<?php

namespace rcms\contentManager\models;

use rcms\core\behaviors\JsonBehavior;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\bootstrap4\Html;
use yii\db\ActiveQuery;
use rcms\core\base\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%content_page}}".
 *
 * @property int $content_page_id
 * @property string $language
 * @property string $type
 * @property string $title
 * @property string $content
 * @property string $plain_content
 * @property string $url
 * @property string $css_style
 * @property string $js_script
 * @property array $metadata
 * @property int $content_layout_id
 * @property int $is_main_page
 * @property int $is_published
 * @property int $start_publish_date
 * @property int $end_publish_date
 * @property boolean $for_guests
 * @property boolean $for_auth
 * @property int $only_with_post
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property ContentRevision[] $contentRevisions
 * @property ContentRevision $contentRevision
 * @property ContentLayout $contentLayout
 * @property IdentityInterface $creator
 * @property IdentityInterface $updator
 *
 * @property string $htmlLink
 * @property array $availableTypes
 * @property array $availableLayouts
 */
class ContentPage extends ActiveRecord
{
    const TYPE_DEFAULT = 'page';

    const TYPE_PAGE = 'page';
    const TYPE_LINK = 'link';
    const TYPE_MENU_ITEM = 'menu_item';
    const TYPE_PDF = 'pdf';

    const LAYOUT_DEFAULT = -1;

    private $_availLayouts = [];

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'start_publish_date' => function ($value) {
                        return (empty($value)) ? null : Yii::$app->formatter->asTimestamp($value);
                    },
                    'end_publish_date' => function ($value) {
                        return (empty($value)) ? null : Yii::$app->formatter->asTimestamp($value);
                    },
                ]
            ],
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
        return '{{%content_page}}';
    }

    /**
     * @return array
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_PAGE => Yii::t('rcms-contentManager', 'Page'),
            self::TYPE_LINK => Yii::t('rcms-contentManager', 'External Link'),
            self::TYPE_MENU_ITEM => Yii::t('rcms-contentManager', 'Menu Item'),
            self::TYPE_PDF => Yii::t('rcms-contentManager', 'PDF'),
        ];
    }

    /**
     * @return array
     */
    public function getAvailableLayouts(): array
    {
        if (empty($this->_availLayouts)) {
            $this->_availLayouts = [
                self::LAYOUT_DEFAULT => Yii::t('rcms-contentManager', 'Empty')
            ];
            $otherLayouts = ContentLayout::find()->select('layout_name')->indexBy('content_layout_id')->column();
            $this->_availLayouts = ArrayHelper::merge($this->_availLayouts, $otherLayouts);
        }
        return $this->_availLayouts;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['language', 'type', 'title', 'url', 'type'], 'required'],
            [['content_page_id', 'content_layout_id'], 'integer'],
            [['content'], 'default', 'value' => ''],
            [['type'], 'default', 'value' => self::TYPE_DEFAULT],
            [['content_layout_id'], 'default', 'value' => self::LAYOUT_DEFAULT],
            [['content', 'plain_content', 'css_style', 'js_script'], 'string'],
            [['start_publish_date', 'end_publish_date'], 'datetime', 'format' => Yii::$app->formatter->datetimeFormat],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['is_main_page', 'is_published', 'for_guests', 'for_auth', 'only_with_post'], 'boolean'],
            [['language'], 'string', 'max' => 10],
            [['type'], 'string', 'max' => 16],
            [['title', 'url'], 'string', 'max' => 255],
            [['url', 'language'], 'unique', 'targetAttribute' => ['url', 'language']],
            [['metadata'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'content_page_id' => Yii::t('rcms-contentManager', 'Content Page ID'),
            'language' => Yii::t('rcms-contentManager', 'Language'),
            'type' => Yii::t('rcms-contentManager', 'Type'),
            'title' => Yii::t('rcms-contentManager', 'Title'),
            'content' => Yii::t('rcms-contentManager', 'Content'),
            'plain_content' => Yii::t('rcms-contentManager', 'Plain Content'),
            'url' => Yii::t('rcms-contentManager', 'Url'),
            'css_style' => Yii::t('rcms-contentManager', 'Css Style'),
            'js_script' => Yii::t('rcms-contentManager', 'Js Script'),
            'metadata' => Yii::t('rcms-contentManager', 'Metadata'),
            'content_layout_id' => Yii::t('rcms-contentManager', 'Layout'),
            'is_main_page' => Yii::t('rcms-contentManager', 'Is Main Page'),
            'is_published' => Yii::t('rcms-contentManager', 'Is Published'),
            'start_publish_date' => Yii::t('rcms-contentManager', 'Start Publish Date'),
            'end_publish_date' => Yii::t('rcms-contentManager', 'End Publish Date'),
            'for_guests' => Yii::t('rcms-contentManager', 'For Guests'),
            'for_auth' => Yii::t('rcms-contentManager', 'For Auth'),
            'only_with_post' => Yii::t('rcms-contentManager', 'Only With Post'),
            'created_at' => Yii::t('rcms-contentManager', 'Created At'),
            'updated_at' => Yii::t('rcms-contentManager', 'Updated At'),
            'created_by' => Yii::t('rcms-contentManager', 'Created By'),
            'updated_by' => Yii::t('rcms-contentManager', 'Updated By'),
        ];
    }

    private $_creator;

    /**
     * @return IdentityInterface
     */
    public function getCreator()
    {
        if (empty($this->_creator)) {
            $this->_creator = Yii::$app->user->identity::findIdentity($this->created_by);
        }
        return $this->_creator;
    }

    private $_updator;

    /**
     * @return IdentityInterface
     */
    public function getUpdator()
    {
        if (empty($this->_creator)) {
            $this->_creator = Yii::$app->user->identity::findIdentity($this->updated_by);
        }
        return $this->_creator;
    }

    /**
     * @return ActiveQuery
     */
    public function getContentRevisions()
    {
        return $this->hasMany(ContentRevision::class, ['content_page_id' => 'content_page_id'])
            ->orderBy('revision_number desc');
    }

    /**
     * {@inheritdoc}
     * @return ContentRevision|ActiveQuery
     */
    public function getContentRevision($revision_number)
    {
        return $this->hasOne(ContentRevision::class, ['content_page_id' => 'content_page_id'])
            ->andWhere(['revision_number' => $revision_number]);
    }

    public function getContentLayout()
    {
        return $this->hasOne(ContentLayout::class, ['content_layout_id' => 'content_layout_id']);
    }

    /**
     * {@inheritdoc}
     * @return ContentPageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ContentPageQuery(get_called_class());
    }

    /**
     * @return string
     */
    public function getHtmlLink(): string
    {
        switch ($this->type):
            case self::TYPE_LINK:
                return Yii::$app->formatter->asUrl($this->url, ['target' => '_blank', 'data-pjax' => '0']);
            case self::TYPE_PDF:
            case self::TYPE_PAGE:
                $settings = new ContentManagerSettings();
                $baseUrl = $settings->hostname . '/';
                if (!empty($settings->content_root_link)) {
                    $baseUrl .= $settings->content_root_link . '/';
                }
                return Html::a(Html::encode($this->url), $baseUrl . $this->url, ['target' => '_blank', 'data-pjax' => '0']);
            default:
                return '';
        endswitch;
    }

    public static function activeLanguagesDropDown()
    {
        $r = self::find()->select('language')->distinct()->asArray()->orderBy('language')->all();
        return ArrayHelper::map($r, 'language', 'language');
    }
}
