<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
/* @var $this yii\web\View
 * @var $model backend\models\SettingsRecord
 */

yii\bootstrap\Modal::begin([
    'header' => "<b>{$model->getAttribute('title')}</b>",
    'headerOptions' => ['id' => 'modalHeader'],
    'id' => 'modal',
    'size' => 'modal-md',
    'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE]
]);
$form=ActiveForm::begin();
echo $form->field($model,'scrubbing')->checkbox();
echo $form->field($model,'remind')->checkbox();
echo $form->field($model,'laser')->radioList(['Y'=>'Да','N'=>'Нет']);
echo $form->field($model,'electro')->radioList(['Y'=>'Да','N'=>'Нет']);
echo $form->field($model,'moderated')->hiddenInput()->label(false);
?>
<div class="form-group">
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    <?= Html::button('Отменить',['class'=>'btn btn-danger','data-dismiss'=>'modal']) ?>
</div>
<?
ActiveForm::end();
yii\bootstrap\Modal::end();
?>
