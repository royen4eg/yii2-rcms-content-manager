<?php


namespace rcms\contentManager\models;


use rcms\core\base\BaseSearchModel;
use yii\bootstrap4\Html;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\helpers\ArrayHelper;

class ContentFileStorageSearch extends BaseSearchModel
{
    const FA_ICON_CLASS = 'fas fa-';

    const ICON_TYPE_DEFAULT = self::FA_ICON_CLASS . 'file';
    const ICON_TYPE_ARCHIVE = self::FA_ICON_CLASS . 'file-archive';
    const ICON_TYPE_AUDIO = self::FA_ICON_CLASS . 'file-audio';
    const ICON_TYPE_CSV = self::FA_ICON_CLASS . 'file-csv';
    const ICON_TYPE_EXCEL = self::FA_ICON_CLASS . 'file-excel';
    const ICON_TYPE_IMAGE = self::FA_ICON_CLASS . 'file-image';
    const ICON_TYPE_PDF = self::FA_ICON_CLASS . 'file-pdf';
    const ICON_TYPE_POWERPOINT = self::FA_ICON_CLASS . 'file-powerpoint';
    const ICON_TYPE_VIDEO = self::FA_ICON_CLASS . 'file-video';
    const ICON_TYPE_WORD = self::FA_ICON_CLASS . 'file-word';

    const MIME_ICON_ARRAY = [
        'audio/*' => self::ICON_TYPE_AUDIO,
        'application/msword' => self::ICON_TYPE_WORD,
        'application/vnd.ms-powerpoint' => self::ICON_TYPE_POWERPOINT,
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => self::ICON_TYPE_POWERPOINT,
        'application/vnd.ms-excel' => self::ICON_TYPE_EXCEL,
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => self::ICON_TYPE_EXCEL,
        'application/pdf' => self::ICON_TYPE_PDF,
        'application/gzip' => self::ICON_TYPE_ARCHIVE,
        'application/zip' => self::ICON_TYPE_ARCHIVE,
        'application/x-7z-compressed' => self::ICON_TYPE_ARCHIVE,
        'application/x-rar-compressed' => self::ICON_TYPE_ARCHIVE,
        'image/*' => self::ICON_TYPE_IMAGE,
        'video/*' => self::ICON_TYPE_VIDEO,
        'text/csv' => self::ICON_TYPE_CSV,
    ];

    public $name;
    public $ext;
    public $type;
    public $size;
    public $is_available;
    public $created_by;
    public $created_at;

    public function attributeLabels()
    {
        return ArrayHelper::filter((new ContentFileStorage())->attributeLabels(), $this->attributes());
    }

    /**
     * @param array $params
     * @return DataProviderInterface
     */
    public function search(array $params = []): DataProviderInterface
    {
        $query = ContentFileStorage::find();

        $this->load($params);

        $query->andFilterWhere([
            'is_available' => $this->is_available,
            'created_by' => $this->created_by,
        ]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'type', $this->type]);
        $query->andFilterWhere(['like', 'ext', $this->ext]);
        $query->andFilterWhere(['<=', 'ext', $this->size]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->pageSize,
            ],
            'sort' => ['defaultOrder' => ['name' => SORT_ASC]]
        ]);
    }

    public static function getTypeIcon($type, $path = null)
    {
        if (isset(self::MIME_ICON_ARRAY[$type])){
            $icon = self::MIME_ICON_ARRAY[$type];
        } else {
            $mask = substr($type, 0, strpos($type, '/') + 1) . '*';
            if (isset(self::MIME_ICON_ARRAY[$mask])){
                $icon = self::MIME_ICON_ARRAY[$mask];
            } else {
                $icon = self::ICON_TYPE_DEFAULT;
            }
        }

        if ($icon === self::ICON_TYPE_IMAGE && !empty($path)) {
            return Html::img($path, ['class' => 'preview-icon', 'style' => 'height: 2rem;width: 2rem;']) .
                Html::img($path, ['class' => 'zoomicon']);
        }

        return Html::tag('i', '', [
            'class' => 'text-secondary ' . $icon,
            'style' => 'border: 1px solid;text-align: center;vertical-align: middle;padding: 0.5rem;'
        ]);
    }

}