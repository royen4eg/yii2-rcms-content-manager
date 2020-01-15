<?php

namespace rcms\contentManager\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use rcms\core\base\ActiveRecord;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "{{%content_file_storage}}".
 * @author Andrii Borodin
 * @since 0.1
 *
 * @property int $file_id
 * @property string $file_hash
 * @property string $name
 * @property string $ext
 * @property string $type
 * @property int $size
 * @property string $path
 * @property boolean $is_available
 * @property int $created_at
 * @property int $created_by
 *
 * @property string $fullName
 */
class ContentFileStorage extends ActiveRecord
{
    const IS_AVAILABLE = 1;

    const NOT_AVAILABLE = 0;

    const REAL_FILE_STORAGE = '@rcms/contentManager/storage/';

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'blameable' => [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%content_file_storage}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_hash', 'name', 'type', 'size', 'path'], 'required'],
            [['path'], 'string'],
            [['is_available'], 'boolean'],
            [['is_available'], 'default', 'value' => self::IS_AVAILABLE],
            [['created_at', 'created_by', 'size'], 'integer'],
            [['ext',], 'string', 'max' => 10],
            [['file_hash', 'name', 'type'], 'string', 'max' => 256],
            [['file_hash'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'file_id' => Yii::t('rcms-contentManager', 'File ID'),
            'file_hash' => Yii::t('rcms-contentManager', 'File Hash'),
            'name' => Yii::t('rcms-contentManager', 'Name'),
            'ext' => Yii::t('rcms-contentManager', 'Extension'),
            'type' => Yii::t('rcms-contentManager', 'Type'),
            'size' => Yii::t('rcms-contentManager', 'Size'),
            'path' => Yii::t('rcms-contentManager', 'Path'),
            'is_available' => Yii::t('rcms-contentManager', 'Is Available'),
            'created_at' => Yii::t('rcms-contentManager', 'Created At'),
            'created_by' => Yii::t('rcms-contentManager', 'Created By'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return ContentFileStorageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ContentFileStorageQuery(get_called_class());
    }

    public function afterDelete()
    {
        parent::afterDelete();
        if (file_exists($this->path)) {
            FileHelper::unlink($this->path);
        }
    }

    public function getFullName()
    {
        return $this->name . '.' . $this->ext;
    }
}
