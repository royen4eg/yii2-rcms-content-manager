<?php

namespace rcms\contentManager\models;

use rcms\core\behaviors\JsonBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use rcms\core\base\ActiveRecord;

/**
 * This is the model class for table "{{%content_layout}}".
 *
 * @property int $content_layout_id
 * @property string $layout_name
 * @property string $content
 * @property string $css_style
 * @property string $js_script
 * @property string $metadata
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class ContentLayout extends ActiveRecord
{
    const CONTENT_REPLACEMENT = '{{content}}';

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
            'metadata' => [
                'class' => JsonBehavior::class,
                'property' => 'metadata',
                'jsonField' => 'metadata',
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%content_layout}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $cr = self::CONTENT_REPLACEMENT;
        return [
            [['layout_name', 'content',], 'required'],
            [['content', 'css_style', 'js_script'], 'string'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['layout_name'], 'string', 'max' => 255],
            [['layout_name'], 'unique', 'targetAttribute' => ['layout_name']],
            ['content', 'match', 'pattern' => "/$cr/",
                'message' => Yii::t('rcms-contentManager', "String $cr should be part of content")],
            [['metadata'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'content_layout_id' => Yii::t('rcms-contentManager', 'Content Layout ID'),
            'layout_name' => Yii::t('rcms-contentManager', 'Layout Name'),
            'content' => Yii::t('rcms-contentManager', 'Content'),
            'css_style' => Yii::t('rcms-contentManager', 'Css Style'),
            'js_script' => Yii::t('rcms-contentManager', 'Js Script'),
            'metadata' => Yii::t('rcms-contentManager', 'Metadata'),
            'created_at' => Yii::t('rcms-contentManager', 'Created At'),
            'updated_at' => Yii::t('rcms-contentManager', 'Updated At'),
            'created_by' => Yii::t('rcms-contentManager', 'Created By'),
            'updated_by' => Yii::t('rcms-contentManager', 'Updated By'),
        ];
    }

    /**
     * @return array
     */
    public function attributeHints()
    {
        return [
            'layout_name' => Yii::t('rcms-contentManager', 'Should be unique'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return ContentLayoutQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ContentLayoutQuery(get_called_class());
    }
}
