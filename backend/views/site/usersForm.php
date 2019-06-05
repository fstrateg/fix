<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use common\models\SettingsRecord;
/* @var $this yii\web\View
 * @var $model backend\models\Users
 */

$this->title=empty($model->id)?"Добавление пользователя":"Редактирование: ".$model->username;

$roles=json_decode(SettingsRecord::findValue('system','roles'),true);
$form=ActiveForm::begin();
?>
<?= $form->field($model,'username')->textInput() ?>
<?= $form->field($model,'email')->textInput() ?>
<?= $form->field($model,'role')->dropDownList($roles) ?>
<?= $form->field($model,'npassword')->passwordInput() ?>
<?= $form->field($model,'cpassword')->passwordInput() ?>
<?//echo $form->field($model, 'password')->passwordInput();
//echo $form->field($model,'country')->dropDownList($model->getCountrys());
?>
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>
<?
ActiveForm::end();
//print_r($roles);
?>