<?php

namespace rcms\contentManager\models;

use rcms\core\models\CoreSettings;
use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\helpers\Url;

class ContentManagerSettings extends Model
{
    const CONFIG_PATH = '@rcms/contentManager/config';

    const FILENAME_DEFAULT = 'contentManagerSettings.data';

    /**
     * @event Event an event that is triggered after the record is created and populated with query result.
     */
    const EVENT_AFTER_FIND = 'afterFind';

    /** @var string */
    private $_filePath;

    /** @var string */
    public $hostname;

    /** @var string */
    public $content_root_link;

    /** @var string */
    public $access_permission;

    /** @var array */
    public $active_components = [];

    public function __construct($filePath = null, $config = [])
    {
        if (empty($filePath)) {
            $this->_filePath = Yii::getAlias(self::CONFIG_PATH) . '/' . self::FILENAME_DEFAULT;
        } else {
            $this->_filePath = $filePath;
        }
        parent::__construct($config);

        if (empty($this->dateFormat)) {
            $this->hostname = Url::base(true);
        }

        $this->find();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [$this->attributes(), 'safe'],
            ['active_components', 'each', 'rule' => ['string']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'hostname' => Yii::t('rcms-contentManager', 'Hostname'),
            'content_root_link' => Yii::t('rcms-contentManager', 'Content Root Link'),
            'access_permission' => Yii::t('rcms-contentManager', 'Access Permission'),
            'active_components' => Yii::t('rcms-contentManager', 'Active Components'),
        ];
    }

    public function attributeHints()
    {
        return [
            'hostname' => Yii::t('rcms-contentManager', 'This hostname will be used to redirect from content manager admin tool to actual pages'),
            'content_root_link' => Yii::t('rcms-contentManager', 'This prefix will be added before page URL and will be available only with this prefix'),
            'access_permission' => Yii::t('rcms-contentManager', 'User should have access to this permission to use Content Manager module'),
            'active_components' => Yii::t('rcms-contentManager', 'Selected components will be available during page preparation'),
        ];
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function save()
    {
        if ($this->validate()) {
            $path = $this->_filePath;
            FileHelper::createDirectory(dirname($path));
            $data = $this->getAttributes();

            $serializedDAta = \Opis\Closure\serialize($data);
            file_put_contents($path, $serializedDAta);

            return true;

        }
        return false;
    }

    public function find()
    {
        $path = $this->_filePath;

        $content = '';
        $fp = @fopen($path, 'r');
        if ($fp !== false) {
            @flock($fp, LOCK_SH);
            $content = fread($fp, filesize($path));
            @flock($fp, LOCK_UN);
            fclose($fp);
        }

        if ($content !== '') {
            $data = array_reverse(\Opis\Closure\unserialize($content), true);
            $this->load($data, '');
        }
        $this->afterFind();
        return $this;
    }

    public function afterFind()
    {
        $this->trigger(self::EVENT_AFTER_FIND);
        if (empty($this->access_permission)) {
            $this->access_permission = (new CoreSettings())->defaultAccessRole;
        }
    }
}
