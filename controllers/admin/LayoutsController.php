<?php


namespace rcms\contentManager\controllers\admin;


use rcms\contentManager\models\ContentLayout;
use rcms\contentManager\models\ContentLayoutSearch;
use rcms\core\base\BaseAdminController;

class LayoutsController extends BaseAdminController
{
    public $availableActions = [
        parent::ACTION_INDEX,
        parent::ACTION_CREATE,
        parent::ACTION_UPDATE,
        parent::ACTION_DELETE,
    ];

    public function beforeAction($action)
    {
        $this->modelObject = new ContentLayout();

        $this->primaryKey = $this->modelObject->primaryKey()[0];
        $this->modelSearch = new ContentLayoutSearch();

        return parent::beforeAction($action);
    }
}