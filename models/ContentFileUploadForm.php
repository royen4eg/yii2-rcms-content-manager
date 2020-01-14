<?php


namespace rcms\contentManager\models;


use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ContentFileUploadForm extends Model
{

    public $name;
    public $type;
    public $size;
    /**
     * @var \yii\web\UploadedFile|null
     */
    public $file;

    private $_storagePath;

    public function init()
    {
        parent::init();
        $this->_storagePath = Yii::getAlias(ContentFileStorage::REAL_FILE_STORAGE);
    }

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, /*'extensions' => 'png, jpg'*/],
            [['name', 'type', 'size'], 'required'],
        ];
    }

    public function upload()
    {
        if (!empty($this->file) && $this->file instanceof UploadedFile){
            $this->name = $this->file->name;
            $this->type = $this->file->type;
            $this->size = $this->file->size;
        }
        if($this->validate()){
            $tgtObg = new ContentFileStorage();
            $tgtObg->name = pathinfo($this->name, PATHINFO_FILENAME);
            $tgtObg->ext = pathinfo($this->name, PATHINFO_EXTENSION);
            $tgtObg->type = $this->type;
            $tgtObg->size = $this->size;
            $tgtObg->file_hash = Yii::$app->security->generateRandomString(10) . str_replace(' ', '_', $this->name);
            $tgtObg->path = $this->_storagePath . $tgtObg->file_hash;
            if ($this->file->saveAs($this->_storagePath . $tgtObg->file_hash) && $tgtObg->save()){
                return $tgtObg->file_hash;
            }
        }
        return false;
    }

    public static function uploadFile(UploadedFile $file)
    {
        $newFileObj = new self();
        $newFileObj->file = $file;

        return $newFileObj->upload();
    }

}