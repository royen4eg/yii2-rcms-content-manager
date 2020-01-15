<?php

namespace rcms\contentManager\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[ContentPage]].
 * @author Andrii Borodin
 * @since 0.1
 *
 * @see ContentPage
 */
class ContentPageQuery extends ActiveQuery
{

    /**
     * {@inheritdoc}
     * @return ContentPage[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ContentPage|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param bool $mode
     * @return ContentPageQuery
     */
    public function guestMode($mode = false)
    {
        if($mode){
            return $this->onlyGuest();
        } else {
            return $this->andWhere('[[for_auth]]=1');
        }
    }

    /**
     * @return ContentPageQuery
     */
    public function onlyAuth()
    {
        return $this->andWhere('[[for_guests]]=0')->andWhere('[[for_auth]]=1');
    }

    /**
     * @return ContentPageQuery
     */
    public function onlyGuest()
    {
        return $this->andWhere('[[for_guests]]=1')->andWhere('[[for_auth]]=0');
    }

    /**
     * @return ContentPageQuery
     */
    public function isPublished()
    {
        return $this->andWhere('[[is_published]]=1');
    }

    /**
     * @return ContentPageQuery
     */
    public function published()
    {
        return $this->isPublished()
            ->andWhere([ 'or',
                ['is', 'start_publish_date', null],
                ['<', 'start_publish_date', time()]
            ])
            ->andWhere([ 'or',
                ['is', 'end_publish_date', null],
                ['>', 'end_publish_date', time()]
            ]);
    }

    /**
     * @param $url string
     * @return ContentPageQuery
     */
    public function whereUrl($url)
    {
        return $this->andWhere(['url' => $url]);
    }

    /**
     * @return ContentPageQuery
     */
    public function withActiveLang()
    {
        return $this->whereLang(\Yii::$app->language);
    }

    /**
     * @param $lang string
     * @return ContentPageQuery
     */
    public function whereLang($lang)
    {
        return $this->andWhere(['language' => $lang]);
    }

    /**
     * @param array $type
     * @return ContentPageQuery
     */
    public function byType(array $type = [])
    {
        return $this->andFilterWhere(['in', 'type', $type]);
    }
}
