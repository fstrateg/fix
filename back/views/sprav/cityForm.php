<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
/* @var $this yii\web\View
 * @var $model app\models\CityForm
 */

$this->title=empty($model->id)?"Добавление города":"Редактирование: ".$model->name;
$form=ActiveForm::begin();
echo $form->field($model,'name')->textInput();
echo $form->field($model,'country')->dropDownList($model->getCountrys());
?>
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>
<?
ActiveForm::end();
?>

