<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
/* @var $this yii\web\View
 * @var $model backend\models\SettingsRecord
 */

yii\bootstrap\Modal::begin([
    'header' => '<b>Редактирование</b>',
    'headerOptions' => ['id' => 'modalHeader'],
    'id' => 'modal',
    'size' => 'modal-md',
    'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE]
]);
$form=ActiveForm::begin();
echo $model->getAttribute('name');
echo $form->field($model,'val')->textarea(['rows'=>'5'])->label(false);
?>
<div class="form-group">
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    <?= Html::button('Отменить',['class'=>'btn btn-danger','data-dismiss'=>'modal']) ?>
</div>
<?
ActiveForm::end();
yii\bootstrap\Modal::end();
?>
