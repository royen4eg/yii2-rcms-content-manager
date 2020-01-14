<?php


namespace rcms\contentManager\models;


use rcms\core\base\SearchInterface;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\helpers\ArrayHelper;

class ContentLayoutSearch extends ContentLayout implements SearchInterface
{

    /**
     * @var int the default page size
     */
    public $pageSize = 25;

    /**
     * @var string
     */
    private $_cacheKey;

    /**
     * Method used to make basic search in model
     * @param array $params
     * @return DataProviderInterface
     */
    public function search(array $params = []): DataProviderInterface
    {
        $query = parent::find();

        $cache = Yii::$app->cache->get($this->_cacheKey) ?? [];

        if(!empty($cache)) {
            $params = ArrayHelper::merge($cache, $params);
        }

        if(!empty($params)) {
            Yii::$app->cache->set($this->_cacheKey, $params, 0, new TagDependency(['tags' => 'user-' . Yii::$app->user->id]));
        }

        $this->load($params);

        $query->andFilterWhere([
            'content_layout_id' => $this->content_layout_id,
            'updated_by' => $this->updated_by,
            'created_by' => $this->created_by,
        ]);
        $query->andFilterWhere(['like', 'layout_name', $this->layout_name]);


        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->pageSize,
            ],
            'sort' => ['defaultOrder' => ['updated_at' => SORT_DESC]]
        ]);
    }
}