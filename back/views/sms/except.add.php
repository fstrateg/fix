<?php
use common\models\ClientsRecord;
use yii\helpers\Html;
/* @var $this yii\web\View
 *  @var $client ClientsRecord
 */
$form=\yii\bootstrap\ActiveForm::begin();
echo $form->field($client,'id')->hiddenInput()->label(false);
echo $form->field($client,'name')->textInput(['readonly'=>true])->label();
echo $form->field($client,'phone')->textInput(['readonly'=>true])->label();
echo $form->field($client,'exception_0')->checkbox()->label();
echo $form->field($client,'exception_1')->checkbox()->label();
echo $form->field($client,'exception_5')->checkbox()->label();
echo $form->field($client,'exception_21')->checkbox()->label();
echo $form->field($client,'exception_42')->checkbox()->label();
echo Html::submitButton('Добавить', ['class' => 'btn btn-success']);
echo Html::a('Отменить',\yii\helpers\Url::to('except'),['class'=>'btn btn-warning','style'=>'margin-left:20px;']);
$form::end();