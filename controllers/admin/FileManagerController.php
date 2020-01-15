<?php


namespace rcms\contentManager\controllers\admin;

use rcms\contentManager\models\ContentFileStorage;
use rcms\contentManager\models\ContentFileStorageSearch;
use rcms\contentManager\models\ContentFileUploadForm;
use rcms\core\base\BaseAdminController;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Class FileManagerController
 * @package rcms\contentManager\controllers\admin
 * @author Andrii Borodin
 * @since 0.1
 *
 * @property ContentFileStorage $modelObject
 */
class FileManagerController extends BaseAdminController
{
    public $availableActions = [
        parent::ACTION_INDEX,
        parent::ACTION_DELETE,
        'upload-file',
        'upload-files',
        'get-img-list',
        'get-file-list',
    ];

    public function behaviors()
    {
        $beh = parent::behaviors();
        $beh['verbs']['actions'] = [
            self::ACTION_INDEX => ['get'],
            self::ACTION_DELETE => ['post'],
            'upload-file' => ['post'],
            'upload-files' => ['post'],
            'get-img-list' => ['get'],
            'get-file-list' => ['get'],
        ];
        $beh['contentNegotiator'] = [
            'class' => 'yii\filters\ContentNegotiator',
            'only' => ['get-img-list', 'get-file-list'],
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        return $beh;
    }

    public function beforeAction($action)
    {
        $this->modelObject = new ContentFileStorage();
        $this->primaryKey = $this->modelObject->primaryKey()[0];
        $this->modelSearch = new ContentFileStorageSearch();

        if (parent::beforeAction($action)) {
            return true;
        }
        return false;
    }

    public function actionUploadFile()
    {
        $file = UploadedFile::getInstanceByName('file');
        if ($fileName = ContentFileUploadForm::uploadFile($file)) {
            Yii::$app->getSession()->addFlash('success', Yii::t('rcms-contentManager', 'File {filename} uploaded', ['filename' => $file->name]));
            if (\Yii::$app->request->isAjax) {
                return Url::base(true) . '/file-manager/get-file?n=' . $fileName;
            }
        } else {
            Yii::$app->getSession()->addFlash('error', Yii::t('rcms-contentManager', 'Could not upload file.'));
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionUploadFiles()
    {
        $files = UploadedFile::getInstancesByName('file');
        $returnUrls = [];
        foreach ($files as $file) {
            if ($fileName = ContentFileUploadForm::uploadFile($file)) {
                Yii::$app->getSession()->addFlash('success', Yii::t('rcms-contentManager', 'File {filename} uploaded', ['filename' => $file->name]));
                $returnUrls[$file->name] = Url::base(true) . '/file-manager/get-file?n=' . $fileName;
            }
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $returnUrls;
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionGetImgList()
    {
        $pathUrl = Url::base(true) . '/file-manager/get-file?n=';
        $imgList = $this->modelObject->find()
            ->select(['name', 'path' => new Expression("CONCAT('{$pathUrl}', file_hash)")])
            ->where(['like', 'type', 'image/'])
            ->orderBy('name')->asArray()->all();
        $return = ArrayHelper::map($imgList, 'name', 'path');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $return;
    }

    public function actionGetFileList()
    {
        $pathUrl = Url::base(true) . '/file-manager/get-file?d=1&n=';
        $imgList = $this->modelObject->find()
            ->select(['name', 'path' => new Expression("CONCAT('{$pathUrl}', file_hash)")])
            ->orderBy('name')->asArray()->all();
        $return = ArrayHelper::map($imgList, 'name', 'path');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $return;
    }
}