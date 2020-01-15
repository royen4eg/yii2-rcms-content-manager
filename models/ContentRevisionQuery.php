<?php

namespace rcms\contentManager\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[ContentRevision]].
 * @author Andrii Borodin
 * @since 0.1
 *
 * @see ContentRevision
 */
class ContentRevisionQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ContentRevision[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ContentRevision|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
