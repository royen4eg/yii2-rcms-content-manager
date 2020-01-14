<?php

namespace rcms\contentManager\components;

use Yii;
use yii\base\Component;
use yii\base\ViewNotFoundException;
use yii\helpers\ArrayHelper;
use yii\web\View;

/**
 * Class ContentParser
 * @package rcms\contentManager\components
 * @author Andrii Borodin
 *
 * TODO: Change logic to next - grab all between {{ }} and then check what is what
 * Also remove addFormatter()
 */
class ContentParser extends Component
{
    const RESERVED_NAMES = [
        '__construct',
        '__call',
        'init',
        '__class__',
        '__dir__',
        '__file__',
        '__function__',
        '__line__',
        '__method__',
        '__namespace__',
        '__trait__',
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'case',
        'catch',
        'callable',
        'cfunction',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'eval',
        'exception',
        'exit',
        'extends',
        'final',
        'finally',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'namespace',
        'new',
        'old_function',
        'or',
        'parent',
        'php_user_filter',
        'print',
        'private',
        'protected',
        'public',
        'require',
        'require_once',
        'return',
        'static',
        'switch',
        'this',
        'throw',
        'trait',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
    ];

    /** @var string */
    public $varPattern = "/(?<={{)[\w\s.\(\):|'\",?@-]+?(?=}})/";

    /** @var string */
    public $paramPattern = '/^\'?[a-zA-Z][\w-]+(\.[\w-]+)*\'?( \?\? \'?\w+\'?)?$/';

    /** @var string */
    public $methodPattern = '/^[a-zA-Z][\w-]+(\.[\w-]+)*( \| ([\w-]+(\.[\w-]+)*|\'[^\']+\'))*$/';

    /** @var string */
    public $loopPattern = "/{{@for ([a-zA-Z]\w+) as ([a-zA-Z]\w+), ([a-zA-Z]\w+) }}(.+)?{{@endfor}}/";

    public $definePattern = "/{{@define ([a-zA-Z]\w+) = ('[^']*'|[^}]+)}}/";

    public $data = [];

    /**
     * @var array
     */
    private static $_callableFunctions = [];

    public function init()
    {
        self::addFormatter();
        parent::init();
    }


    /**
     * @return array
     */
    public static function getCallableFunctions(): array
    {
        self::addFormatter();
        return self::$_callableFunctions;
    }

    /**
     * @param $id
     * @return array|null
     */
    public static function getCallableFunction($id): array
    {
        self::addFormatter();
        return self::$_callableFunctions[$id] ?? null;
    }

    /**
     * @param string $id
     * @param mixed $callableFunctions
     */
    public static function setCallableFunction(string $id, $callableFunctions): void
    {
        self::addFormatter();
        if(!in_array($id, self::$_callableFunctions)) {
            self::$_callableFunctions[$id] = $callableFunctions;
        }
    }

    private static function addFormatter()
    {
        if (!in_array('formatter', static::$_callableFunctions)){
            /** @var string $csName */
            $csName = get_class(Yii::$app->formatter);
            try {
                $class = new \ReflectionClass($csName);

                foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
                    if ($m->class == $csName && !in_array(strtolower($m->name), self::RESERVED_NAMES) ) {
                        static::$_callableFunctions['formatter.'.$m->name] = [Yii::$app->formatter, $m->name];
                    }
                }
            } catch (\ReflectionException $e) {
            }
            static::$_callableFunctions['formatter'] = 'formatter';
        }
    }

    /**
     * @param View $view
     * @param $output
     */
    public function scanContent(View $view, &$output)
    {
        preg_match_all($this->definePattern, $output, $defineRes);
        if (!empty($defineRes[0])) {
            foreach ($defineRes[0] as $index => $define) {
                $this->defineParam($defineRes[1][$index], trim($defineRes[2][$index]));
                $output = str_replace($define, '', $output);
            }
        }

        preg_match_all($this->loopPattern, $output, $loopRes);
        if (!empty($loopRes[0])) {
            $tempParams = $this->data;
            foreach ($loopRes[0] as $index => $loop) {
                $oldContent = $loop;
                $newContent = '';
                $origin = $this->getParameter($loopRes[1][$index]);
                if (!empty($origin) && is_array($origin)) {
                    foreach ($origin as $key => $array) {
                        $innerContent = $loopRes[4][$index];
                        //set current index as temporary value
                        $tempParams[$loopRes[2][$index]] = $key;
                        $tempParams[$loopRes[3][$index]] = $array;

                        $loopCp = new self();
                        $loopCp->data = $tempParams;
                        $loopCp->scanContent($view, $innerContent);
                        unset($loopCp);

                        $newContent .= $innerContent;
                    }
                }
                $output = str_replace($oldContent, $newContent, $output);
            }
        }

        preg_match_all($this->varPattern, $output, $parameters);
        $parameters = !empty($parameters[0]) ? array_unique($parameters[0]) : [];
        foreach ($parameters as $param) {
            $replaceWith = $this->scanParam(trim($param));

            if (is_array($replaceWith) || is_object($replaceWith)) {
                $replaceWith = '';
            }
            $output = str_replace('{{' . $param . '}}', $replaceWith, $output);
        }
    }

    private function scanParam(string $param)
    {
        $resVal = '';
        // Check for simple parameters (with or without default replacement)
        if (in_array($param, $this->data) || preg_match($this->paramPattern, $param)) {
            $paramArray = explode(' ?? ', $param, 2);
            $resVal = $this->getParameter($paramArray[0], $paramArray[ 1] ?? null);
        } elseif (preg_match($this->methodPattern, $param)) {
            $paramArray = explode(' | ', $param);
            $fnName = array_shift($paramArray);
            if(isset(self::$_callableFunctions[$fnName])){
                foreach ($paramArray as &$pa){
                    $pa = $this->getParameter($pa);
                }
                try {
                    $resVal = call_user_func_array(self::$_callableFunctions[$fnName],$paramArray);
                } catch (\Exception $e){
                    $resVal = '';
                }
            }
        }
        return $resVal;
    }


    /**
     * @param string $search
     * @param string $default
     * @return mixed
     */
    private function getParameter(string $search, $default = null)
    {
        $sPattern = '/^\'.*\'$/';
        if(preg_match($sPattern, $search)){
            return trim($search, "'");
        }
        if(!empty($default) && preg_match($sPattern, $default)){
            $default = trim($search, "'");
        } else {
            $default = ArrayHelper::getValue($this->data, $default);
        }
        return ArrayHelper::getValue($this->data, $search, $default);
    }

    private function getComponent(View $view, string $param)
    {
        $param = str_replace('getComponent | ', '', $param);
        $paramConf = explode(' ', $param);
        if (empty($paramConf)) {
            return '';
        }
        $componentName = $paramConf[0];
        unset($paramConf[0]);
        try {
            return $view->render($componentName, ['args' => $paramConf]);
        } catch (ViewNotFoundException $e) {
            return '';
        }
    }

    private function defineParam($paramName, $strValue)
    {
        $paramValue = $this->scanParam($strValue);
        ArrayHelper::setValue($this->data, $paramName, $paramValue);
    }

}
