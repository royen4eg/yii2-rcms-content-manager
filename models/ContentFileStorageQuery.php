<?php

namespace rcms\contentManager\models;

/**
 * This is the ActiveQuery class for [[ContentFileStorage]].
 *
 * @see ContentFileStorage
 */
class ContentFileStorageQuery extends \yii\db\ActiveQuery
{
    public function available()
    {
        return $this->andWhere('[[is_available]]=1');
    }

    /**
     * {@inheritdoc}
     * @return ContentFileStorage[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ContentFileStorage|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
