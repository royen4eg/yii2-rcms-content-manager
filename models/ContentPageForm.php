<?php


namespace rcms\contentManager\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class ContentPageForm extends ContentPage
{

    public $createRevision = false;

    public function init()
    {
        parent::init();
        $this->is_published = $this->for_auth = $this->for_guests = true; // by default set new items as true
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['createRevision', 'boolean'];
        return  $rules;
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'createRevision' => Yii::t('rcms-contentManager', 'Create Revision'),
        ]);
    }

    public function attributeHints()
    {
        return [
            'url' => Yii::t('rcms-contentManager', 'Example: {example}', ['example' => '<code>mypage</code>, <code>my-page</code>, <code>my/page</code>']),
            'createRevision' => Yii::t('rcms-contentManager', 'Check if you want to create new revision - you will be able to rollback changes'),
            'language' => Yii::t('rcms-contentManager', 'New languages can be added through dictionary'),
            'for_guests' => Yii::t('rcms-contentManager', 'Guests will see the content'),
            'for_auth' => Yii::t('rcms-contentManager', 'Authorized users will see the content'),
            'content' => Yii::t('rcms-contentManager', 'You can use next tags: {tags}', [
                'tags' => '<code>$img:{img-name}</code>, <code>$file:{file-name}</code>'
            ]),
            'type' => Yii::t('rcms-contentManager', 'Note: Only Page Type support Layout')
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        if (isset($_GET['revision'])) {
            $this->loadRevision($_GET['revision']);
        }
    }

    public function beforeSave($insert)
    {
        if(!empty($this->metadata)){
            $metaCheck = $this->metadata;
            foreach ($metaCheck as $key => $metaRow) {
                if(empty($metaRow['metaName'])){
                    unset($metaCheck[$key]);
                }
            }
            $this->metadata = $metaCheck;
        }

        if(!$insert && $this->createRevision){
            $this->createNewRevision();
        }

        return parent::beforeSave($insert);
    }

    public function toDatetime ($value)
    {
        if (!empty($value)) {
            try {
                $datetime = Yii::$app->formatter->asDatetime($value);
                return $datetime;
            } catch (InvalidConfigException $exception) {}
        }
        return null;
    }

    protected function createNewRevision()
    {
        $revision = new ContentRevision();
        $revision->content_page_id = $this->content_page_id;
        $revision->revision_number = count($this->contentRevisions) + 1;
        $revision->title = $this->getOldAttribute('title');
        $revision->content = $this->getOldAttribute('content');
        $revision->css_style = $this->getOldAttribute('css_style');
        $revision->js_script = $this->getOldAttribute('js_script');
        if($revision->save()){
            $this->refreshInternal($this);
        } else {
            Yii::$app->getSession()->addFlash('error', Yii::t('rcms-contentManager','Could not create new revision'));
            Yii::$app->getSession()->addFlash('error', json_encode($revision->errors, JSON_UNESCAPED_UNICODE));
        }
    }

    protected function loadRevision($revision_number)
    {
        $r = $this->getContentRevision($revision_number)->one();
        if(!empty($r)){
            $this->title = $r->title;
            $this->content = $r->content;
            $this->css_style = $r->css_style;
            $this->js_script = $r->js_script;
        }
    }

}