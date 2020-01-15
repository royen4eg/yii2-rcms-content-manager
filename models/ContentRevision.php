<?php

namespace rcms\contentManager\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use rcms\core\base\ActiveRecord;

/**
 * This is the model class for table "{{%content_revision}}".
 * @author Andrii Borodin
 * @since 0.1
 *
 * @property int $content_revision_id
 * @property int $content_page_id
 * @property int $revision_number
 * @property string $title
 * @property string $content
 * @property string $css_style
 * @property string $js_script
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property ContentPage $contentPage
 */
class ContentRevision extends ActiveRecord
{

    public function behaviors ()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%content_revision}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_page_id', 'revision_number', 'title'], 'required'],
            [['content_page_id', 'revision_number', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['content', 'css_style', 'js_script'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['content_page_id', 'revision_number'], 'unique', 'targetAttribute' => ['content_page_id', 'revision_number']],
            [['content_page_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContentPage::class, 'targetAttribute' => ['content_page_id' => 'content_page_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'content_revision_id' => Yii::t('rcms-contentManager', 'Content Revision ID'),
            'content_page_id' => Yii::t('rcms-contentManager', 'Content Page ID'),
            'revision_number' => Yii::t('rcms-contentManager', 'Revision Number'),
            'title' => Yii::t('rcms-contentManager', 'Title'),
            'content' => Yii::t('rcms-contentManager', 'Content'),
            'css_style' => Yii::t('rcms-contentManager', 'Css Style'),
            'js_script' => Yii::t('rcms-contentManager', 'Js Script'),
            'created_at' => Yii::t('rcms-contentManager', 'Created At'),
            'updated_at' => Yii::t('rcms-contentManager', 'Updated At'),
            'created_by' => Yii::t('rcms-contentManager', 'Created By'),
            'updated_by' => Yii::t('rcms-contentManager', 'Updated By'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getContentPage()
    {
        return $this->hasOne(ContentPage::class, ['content_page_id' => 'content_page_id']);
    }

    /**
     * {@inheritdoc}
     * @return ContentRevisionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ContentRevisionQuery(get_called_class());
    }
}
