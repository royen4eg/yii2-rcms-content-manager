<?php

namespace rcms\contentManager\controllers\front;

use rcms\contentManager\models\ContentFileStorage;
use rcms\contentManager\models\ContentPage;
use rcms\contentManager\Module as CMModule;
use rcms\core\base\BaseFrontController;
use kartik\mpdf\Pdf;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use Yii;
use yii\web\Response;
use yii\web\View;

class ContentController extends BaseFrontController
{
    /* @var string */
    public $requestedUrl;
    /* @var ContentPage */
    public $pageObj;
    /* @var string */
    private $_layoutPath = '@rcms/contentManager/views/front/layouts/empty';

    public function beforeAction($action)
    {
        if($this->module instanceof CMModule){
            Yii::$app->view->on(View::EVENT_AFTER_RENDER,[$this->module, 'transformByEvent']);
        }
        return parent::beforeAction($action);
    }

    /**
     * @param $pageUrl
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionIndex($pageUrl)
    {
        $this->requestedUrl = $pageUrl;

        $this->pageObj = ContentPage::find()
            ->whereUrl($this->requestedUrl)
            ->withActiveLang()
            ->published()
            ->guestMode(Yii::$app->user->isGuest)
            ->one();

        if (empty($this->pageObj) || $this->pageObj->type === ContentPage::TYPE_MENU_ITEM) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'), 404);
        }

        if ($this->pageObj->type === ContentPage::TYPE_LINK) {
            return $this->redirect($this->pageObj->url);
        }

        $outParams = Yii::$app->request->get();
        unset($outParams['pageUrl']);
        $outParams['__get'] = http_build_query($outParams);

        if(Yii::$app->request->post()){
            $outParams['__post'] = Yii::$app->request->post();
        }

        $renderParams = ['page' => $this->pageObj, 'outParams' => $outParams];
        $this->view->title = $this->pageObj->title;
        if ($this->pageObj->type === ContentPage::TYPE_PDF) {
            return $this->renderPdf($this->pageObj, $renderParams);
        } elseif (Yii::$app->request->isAjax) {
            return $this->renderAjax($this->pageObj->type, $renderParams);
        } else {
            $this->layout = $this->_layoutPath;
            return $this->render($this->pageObj->type, $renderParams);
        }

    }

    /**
     * @param $n
     * @param bool $d
     * @return void|\yii\console\Response|Response
     * @throws HttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionGetFile($n, $d = false)
    {
        $file = ContentFileStorage::find()->available()->andWhere(['file_hash' => $n])->one();
        if(!empty($file) && file_exists($file->path)){
            $response = Yii::$app->response;
            if($d){
                return $response->sendFile($file->path, $file->fullName);
            }
            $response->headers->set('Content-Type', $file->type);
            $response->headers->set('Content-Disposition', 'inline; filename=' . $file->fullName);
            $response->format = Response::FORMAT_RAW;
            if ( !is_resource($response->stream = fopen($file->path, 'r')) ) {
                throw new \yii\web\ServerErrorHttpException('File access failed: Permission deny');
            }
            return $response->send();
        }
        throw new HttpException(404, 'File Not Found.');
    }

    /**
     * @param ContentPage|null $pageObj
     * @param array $renderParams
     * @return mixed
     * @throws NotFoundHttpException
     */
    protected function renderPdf(?ContentPage $pageObj, array $renderParams)
    {
        $this->layout = false;
        $content = $pageObj->content;
        $this->view->afterRender(null, $renderParams, $content);

        $metadata = !empty($pageObj->metadata) ? ArrayHelper::map($pageObj->metadata, 'metaName', 'metaAttributes') : [];
        $metaOptions = isset($metadata['pdf']) ? ArrayHelper::map($metadata['pdf'], 'attribute', 'value') : [];

        $pdfOptions = [
            'mode' => ArrayHelper::remove($metaOptions, 'mode', Pdf::MODE_UTF8),
            'format' => ArrayHelper::remove($metaOptions, 'format', Pdf::FORMAT_A4),
            'orientation' => ArrayHelper::remove($metaOptions, 'orientation', Pdf::ORIENT_PORTRAIT),
            'destination' => ArrayHelper::remove($metaOptions, 'destination', Pdf::DEST_BROWSER),
            'content' => $content,
            'options' => [
                'title' => $this->view->title,
                'subject' => ArrayHelper::remove($metaOptions, 'subject', $this->view->title),
                'keywords' => ArrayHelper::remove($metaOptions, 'keywords', ''),
            ],
            'filename' => $this->view->title . '.pdf',
            'cssInline' => $pageObj->css_style,
            'methods' => [
                'SetHeader' => [ArrayHelper::remove($metaOptions, 'header', null)],
                'SetFooter' => [ArrayHelper::remove($metaOptions, 'footer', null)],
                'SetAuthor' => [ArrayHelper::remove($metaOptions, 'author', $pageObj->creator->username)],
                'SetCreator' => Yii::$app->user->isGuest ? $pageObj->creator->username : Yii::$app->user->identity->username,
            ],
            'defaultFontSize' => ArrayHelper::remove($metaOptions, 'defaultFontSize', 0),
            'defaultFont' => ArrayHelper::remove($metaOptions, 'defaultFont', ''),
            'marginLeft' => ArrayHelper::remove($metaOptions, 'marginLeft', 15),
            'marginRight' => ArrayHelper::remove($metaOptions, 'marginRight', 15),
            'marginTop' => ArrayHelper::remove($metaOptions, 'marginTop', 16),
            'marginBottom' => ArrayHelper::remove($metaOptions, 'marginBottom', 16),
            'marginHeader' => ArrayHelper::remove($metaOptions, 'marginHeader', 9),
            'marginFooter' => ArrayHelper::remove($metaOptions, 'marginFooter', 9),
        ];
        $pdf = new Pdf($pdfOptions);

        try {
            Yii::$app->response->format = Response::FORMAT_RAW;
            return $pdf->render();
        } catch (InvalidConfigException $e) {
            throw new NotFoundHttpException();
        }
    }
}