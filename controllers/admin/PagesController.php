<?php

namespace rcms\contentManager\controllers\admin;

use rcms\contentManager\models\ContentPage;
use rcms\contentManager\models\ContentPageForm;
use rcms\contentManager\models\ContentPageSearch;
use rcms\core\base\BaseAdminController;

/**
 * Class PagesController
 * @package rcms\contentManager\controllers\admin
 * @author Andrii Borodin
 * @since 0.1
 */
class PagesController extends BaseAdminController
{
    public $availableActions = [
        parent::ACTION_INDEX,
        parent::ACTION_CREATE,
        parent::ACTION_UPDATE,
        parent::ACTION_DELETE,
    ];

    public function beforeAction($action)
    {
        if (in_array($this->action->id, [
            parent::ACTION_CREATE, parent::ACTION_UPDATE
        ])) {
            $this->modelObject = new ContentPageForm();
        } else {
            $this->modelObject = new ContentPage();
        }

        $this->primaryKey = $this->modelObject->primaryKey()[0];
        $this->modelSearch = new ContentPageSearch();

        return parent::beforeAction($action);
    }
}