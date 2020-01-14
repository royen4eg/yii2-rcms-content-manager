<?php

use rcms\contentManager\models\ContentPageForm;
use yii\bootstrap4\Html;
use yii\web\View;
use yii\bootstrap4\ActiveForm;

/* @var $this View */
/* @var $model ContentPageForm */
/* @var $form ActiveForm */

?>
<div class="row">
    <div class="col-sm">
        <?= $form->field($model, 'type')->dropDownList($model->availableTypes) ?>
    </div>
    <div class="col-sm">
        <?= $form->field($model, 'createRevision')->checkbox() ?>
    </div>
</div>
<div class="row">
    <div class="col-sm">
        <?= $form->field($model, 'for_guests')->checkbox() ?>
    </div>
    <div class="col-sm">
        <?= $form->field($model, 'for_auth')->checkbox() ?>
    </div>
</div>
<div class="row">
    <div class="col-sm">
        <?= $form->field($model, 'only_with_post')->checkbox() ?>
    </div>
</div>
<div class="row">
    <div class="col-sm">
        <?= $form->field($model, 'is_main_page')->checkbox() ?>
    </div>
</div>
<div class="row">
    <div class="col-sm">
        <?php
        if ($model->content_layout_id > $model::LAYOUT_DEFAULT) {
            $layoutLink = Html::a(Yii::t('rcms-contentManager', 'Open Layout'),
                ['layouts/update', 'id' => $model->content_layout_id],
                ['target' => '_blank', 'class' => 'btn btn-info']
            );
            echo $form->field($model, 'content_layout_id', [
                'template' => '{beginLabel}{labelTitle}{endLabel}<div class="input-group">{input}
                    <div class="input-group-append">' . $layoutLink . '</div>
                    </div>{error}{hint}'
            ])->dropDownList($model->availableLayouts);
        } else {
            echo $form->field($model, 'content_layout_id')->dropDownList($model->availableLayouts);
        } ?>
    </div>
</div>
