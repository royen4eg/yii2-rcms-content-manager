<?php

namespace rcms\contentManager\models;

use rcms\core\base\BaseSearchModel;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\helpers\ArrayHelper;

class ContentPageSearch extends BaseSearchModel
{

    public $title;
    public $url;
    public $language;
    public $type;
    public $content_layout_id;
    public $is_published;
    public $for_guests;
    public $for_auth;
    public $only_with_post;
    public $is_main_page;
    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;

    const AVAILABLE_COLUMNS = [
        'title', 'url', 'language', 'type', 'content_layout_id', 'is_published', 'for_guests', 'for_auth', 'only_with_post',
        'is_main_page', 'created_at', 'updated_at', 'created_by', 'updated_by',
    ];

    const DEFAULT_COLUMNS = [
        'title', 'url', 'language', 'is_published', 'for_guests',
        'for_auth', 'only_with_post', 'is_main_page', 'updated_at',
    ];

    private $iconTrue = '<span class="fas fa-check text-success"></span>';
    private $iconFalse = '<span class="fas fa-times text-danger"></span>';

    public $gridViewColumns = [];
    private $_gridViewColumns = [];

    public $selectedColumns = [];
    /**
     * @var string
     */
    private $_cacheKey;

    /**
     * @var ContentPage
     */
    private $_contentPage;

    public function init()
    {
        parent::init();
        $this->_cacheKey = 'ContentPageSearch_user_' . Yii::$app->user->id;
        $this->_contentPage = new ContentPage();
        $this->prepareGridColumns();
        if (empty($this->selectedColumns)) {
            $this->selectedColumns = self::DEFAULT_COLUMNS;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [$this->attributes(), 'safe'],
        ];
    }

    public function attributeLabels()
    {
        $labels = ArrayHelper::filter($this->_contentPage->attributeLabels(), self::AVAILABLE_COLUMNS);
        return ArrayHelper::merge($labels, []);
    }

    /**
     * @param array $params
     * @return DataProviderInterface
     * @throws \yii\base\InvalidConfigException
     */
    public function search(array $params = []): DataProviderInterface
    {
        $query = ContentPage::find();

        $cache = Yii::$app->cache->get($this->_cacheKey) ?? [];

        if(!empty($cache)) {
            if(isset($params[$this->formName()]['selectedColumns'])){
                unset($cache[$this->formName()]['selectedColumns']);
            }
            $params = ArrayHelper::merge($cache, $params);
        }

        if(!empty($params)) {
            Yii::$app->cache->set($this->_cacheKey, $params, 0, new TagDependency(['tags' => 'user-' . Yii::$app->user->id]));
        }

        $this->load($params);

        $query->andFilterWhere([
            'content_page_id' => $this->id,
            'language' => $this->language,
            'type' => $this->type,
            'content_layout_id' => $this->content_layout_id,
            'is_published' => $this->is_published,
            'for_guests' => $this->for_guests,
            'for_auth' => $this->for_auth,
            'only_with_post' => $this->only_with_post,
            'is_main_page' => $this->is_main_page,
            'updated_by' => $this->updated_by,
            'created_by' => $this->created_by,
        ]);
        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['like', 'url', $this->url]);

        foreach (array_unique($this->selectedColumns) as $column) {
            $this->gridViewColumns[] = $this->_gridViewColumns[$column];
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->pageSize,
            ],
            'sort' => ['defaultOrder' => ['updated_at' => SORT_DESC]]
        ]);
    }

    private function prepareGridColumns()
    {
        $iT = $this->iconTrue;
        $iF = $this->iconFalse;
        $boolFilter = ['1' => Yii::t('yii', 'Yes'), '0' => Yii::t('yii', 'No')];
        $this->_gridViewColumns = [
            'title' => 'title',
            'language' => [
                'attribute' => 'language',
                'filter' => $this->_contentPage::activeLanguagesDropDown(),
            ],
            'url' => [
                'attribute' => 'url',
                'format' => 'raw',
                'value' => 'htmlLink'
            ],
            'type' => [
                'attribute' => 'type',
                'filter' => $this->_contentPage::getAvailableTypes(),
            ],
            'content_layout_id' => [
                'attribute' => 'content_layout_id',
                'filter' => $this->_contentPage->availableLayouts,
                'value' => 'contentLayout.layout_name',
            ],
            'is_published' => [
                'attribute' => 'is_published',
                'format' => 'raw',
                'filter' => $boolFilter,
                'value' => function (ContentPage $data) use ($iT, $iF) {
                    return $data->is_published ? $iT : $iF;
                },
            ],
            'for_guests' => [
                'attribute' => 'for_guests',
                'format' => 'raw',
                'filter' => $boolFilter,
                'value' => function (ContentPage $data) use ($iT, $iF) {
                    return $data->for_guests ? $iT : $iF;
                },
            ],
            'for_auth' => [
                'attribute' => 'for_auth',
                'format' => 'raw',
                'filter' => $boolFilter,
                'value' => function (ContentPage $data) use ($iT, $iF) {
                    return $data->for_auth ? $iT : $iF;
                },
            ],
            'only_with_post' => [
                'attribute' => 'only_with_post',
                'format' => 'raw',
                'filter' => $boolFilter,
                'value' => function (ContentPage $data) use ($iT, $iF) {
                    return $data->only_with_post ? $iT : $iF;
                },
            ],
            'is_main_page' => [
                'attribute' => 'is_main_page',
                'format' => 'raw',
                'filter' => $boolFilter,
                'value' => function (ContentPage $data) use ($iT, $iF) {
                    return $data->is_main_page ? $iT : $iF;
                },
            ],
            'created_at' => 'created_at:datetime',
            'updated_at' => 'updated_at:relativeTime',
            'created_by' => [
                'attribute' => 'created_by',
                'value' => 'creator.username'
            ],
            'updated_by' => [
                'attribute' => 'updated_by',
                'value' => 'updator.username'
            ]
        ];
    }
}