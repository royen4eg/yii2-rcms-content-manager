<?php

/* @var $this View */
/* @var $model ContentManagerSettings */

use rcms\contentManager\models\ContentManagerSettings;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\web\View;

?>
<div id="rcms-core-settings-index">

    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <?php $form = ActiveForm::begin(['action' => Url::to(['create'])]) ?>

    <?= $form->field($model, 'hostname') ?>

    <?= $form->field($model, 'content_root_link') ?>

    <?php if (Yii::$app->authManager instanceof \yii\rbac\ManagerInterface): ?>

        <?= $form->field($model, 'access_permission')->widget('yii\jui\AutoComplete', [
            'options' => [
                'class' => 'form-control',
            ],
            'clientOptions' => [
                'source' => array_keys(Yii::$app->authManager->getPermissions()),
            ],
        ]);
        ?>

    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('rcms-core', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>