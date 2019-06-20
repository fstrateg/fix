<?php
use \yii\grid\GridView;
$this->title="Просмотр сообщений";
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'=>$searchModel,
    'columns' => [
        'phone',
        'info',
        [
            'attribute'=>'msg',
            'contentOptions' => ['style' => 'width:50%; white-space: normal;'],
        ],
        ['attribute' => 'dt','value' => function($sModel) {
            $date = new DateTime($sModel->dt);
            return $date->format('d.m.y H:i:s');
        }],
        ['attribute'=>'grp','filter'=>array("sms"=>"SMS","err"=>"Ошибка")],
    ],
    'formatter' => [
        'class' => 'yii\i18n\Formatter',
        'timeZone' => 'Asia/Bishkek'
    ],
]) ?>