<?php

namespace rcms\contentManager\controllers\admin;

use rcms\contentManager\models\ContentManagerSettings;
use rcms\core\base\BaseAdminController;
use Yii;

class SettingsController extends BaseAdminController
{
    public $availableActions = [
        parent::ACTION_INDEX,
        parent::ACTION_CREATE
    ];

    /**
     *{@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->view->title = Yii::t('rcms-contentManager', 'Content Manager Settings');

        $this->modelObject = new ContentManagerSettings();
        if (parent::beforeAction($action)) {
            return true;
        }
        return false;
    }

    public function actionIndex()
    {
        return $this->render('index', ['model' => $this->modelObject]);
    }

    public function actionCreate($exit = true)
    {
        if (Yii::$app->request->isPost) {
            $this->modelObject->load(Yii::$app->request->post());
            if ($this->modelObject->save()) {
                Yii::$app->getSession()->addFlash('success', 'Content successfully saved');
            } else
                Yii::$app->getSession()->addFlash('error', json_encode($this->modelObject->errors, JSON_UNESCAPED_UNICODE));
        }
        return $this->render('index', ['model' => $this->modelObject]);
    }

}