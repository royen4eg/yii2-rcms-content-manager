<?php

use rcms\contentManager\models\ContentLayout;
use rcms\core\widgets\dynamicform\DynamicFormWidget;
use yii\base\DynamicModel;
use yii\bootstrap4\ActiveForm;
use yii\web\View;

/* @var $this View */
/* @var $model ContentLayout */
/* @var $form ActiveForm */

$emptyText = Yii::t('rcms-core', 'Empty Text');

if(empty($model->metadata)) {
    $model->metadata = [[
        'metaAttributes' => [[]]
    ]];
}

?>
<div class="row" id="rcms-cm-metadata-block">
    <div class="col-sm-12">
        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'dynamicform_wrapper',
            'widgetBody' => '.container-items',
            'widgetItem' => '.item',
            'insertButton' => '.add-item',
            'deleteButton' => '.remove-item',
            'moveBackwardButton' => '.moveUp-item',
            'moveForwardButton' => '.moveDown-item',
            'limit' => 10,
            'min' => 1,
            'model' => new DynamicModel(['metaName']),
            'formId' => 'contentManagerForm',
            'formFields' => [
                'metaName',
                'metaAttributes',
            ],
        ]) ?>
        <table class="table table-bordered table-condensed table-striped" id="metadata-fields">
            <thead>
            <tr>
                <th><?= Yii::t('rcms-contentManager', 'Add metadata') ?></th>
                <th class="text-center">
                    <button type="button" class="add-item btn btn-success btn-sm">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                    </button>
                </th>
            </tr>
            </thead>
            <tbody class="container-items">
            <?php foreach ($model->metadata as $i => $metadataModel): ?>
                <tr class="item" role="tab">
                    <td>
                        <a role="button" data-toggle="collapse" data-parent="#metadata-fields" class="link-to-collapse"
                           href="#collapsable-<?= $i ?>-row" aria-expanded="true">
                            <h5><span class="text-secondary item-name">
                                <?= $metadataModel['metaName'] ?? $emptyText ?>
                            </span></h5>
                        </a>
                        <div class="collapse" id="collapsable-<?= $i ?>-row">
                            <div class="row">
                                <div class="col-md-2">
                                    <?= $form->field($model, "metadata[{$i}][metaName]")
                                        ->label(Yii::t('rcms-contentManager', 'Meta Name'))
                                        ->textInput([
                                            'class' => 'form-control cm-metadata-field__name',
                                        ]) ?>
                                </div>
                                <div class="col-md-10">
                                    <?php DynamicFormWidget::begin([
                                        'widgetContainer' => 'inner_dynamicform_wrapper',
                                        'widgetBody' => '.inner_container-items',
                                        'widgetItem' => '.inner_item',
                                        'insertButton' => '.inner_add-item',
                                        'deleteButton' => '.inner_remove-item',
                                        'moveBackwardButton' => '.inner_moveUp-item',
                                        'moveForwardButton' => '.inner_moveDown-item',
                                        'limit' => 10,
                                        'min' => 1,
                                        'model' => new DynamicModel(['attribute', 'value']),
                                        'formId' => 'contentManagerForm',
                                        'formFields' => [
                                            'attribute',
                                            'value',
                                        ],
                                    ]) ?>
                                    <table class="table table-condensed">
                                        <thead>
                                        <tr>
                                            <th><?= Yii::t('rcms-contentManager', 'Attribute Name') ?></th>
                                            <th><?= Yii::t('rcms-contentManager', 'Attribute Value') ?></th>
                                            <th class="text-center">
                                                <button type="button" class="inner_add-item btn btn-success btn-sm">
                                                    <i class="fas fa-plus" aria-hidden="true"></i>
                                                </button>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody class="inner_container-items">
                                        <?php foreach ($metadataModel['metaAttributes'] as $y => $innerModel): ?>
                                            <tr class="inner_item">
                                                <td>
                                                    <?= $form->field($model, "metadata[{$i}][metaAttributes][{$y}][attribute]")->label(false)
                                                        ->textInput([
                                                                'class' => 'form-control cm-metadata-field__attribute',
                                                        ]) ?>
                                                </td>
                                                <td>
                                                    <?= $form->field($model, "metadata[{$i}][metaAttributes][{$y}][value]")->label(false)
                                                        ->textInput([
                                                                'class' => 'form-control cm-metadata-field__value',
                                                        ]) ?>
                                                </td>
                                                <td class="text-center vcenter" style="width: 125px;">
                                                    <button type="button"
                                                            class="inner_remove-item btn btn-danger btn-sm">
                                                        <i class="fas fa-minus" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="inner_moveUp-item btn btn-info btn-sm">
                                                        <i class="fas fa-chevron-up" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="inner_moveDown-item btn btn-info btn-sm">
                                                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php DynamicFormWidget::end() ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center vcenter" style="width: 125px;">
                        <button type="button" class="remove-item btn btn-danger btn-sm">
                            <i class="fas fa-minus" aria-hidden="true"></i>
                        </button>
                        <button type="button"
                                class="moveUp-item btn btn-info btn-sm">
                            <i class="fas fa-chevron-up" aria-hidden="true"></i>
                        </button>
                        <button type="button"
                                class="moveDown-item btn btn-info btn-sm">
                            <i class="fas fa-chevron-down" aria-hidden="true"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php DynamicFormWidget::end() ?>
    </div>
</div>
<?php
$emptyText = json_encode($emptyText);
$js = <<<JS
$(document).on('change', '.cm-metadata-field__name', function() {
    const content = $(this).val() === "" ? {$emptyText} : $(this).val();
    $(this).closest('td').find('.item-name').html(content);
});

$(".dynamicform_wrapper").on("afterInsert", function(e, item) {
    let tgt = $(item);
    tgt.find('.item-name').html({$emptyText});
    tgt.find('.link-to-collapse').attr('href', '#collapsable-' + $(item).index() + '-row');
    tgt.find('.collapse').addClass('in');
});
JS;
$this->registerJS($js, View::POS_END);
?>
