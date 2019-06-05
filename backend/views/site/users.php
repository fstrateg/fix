<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
/* @var $this yii\web\View */

$this->title = 'Пользователи';
?>
<div class="site-index">
    <?
    echo Html::a('<span class="glyphicon glyphicon-pencil"></span> Добавить',['users','m'=>'add'],['class'=>'btn btn-success']);
    echo GridView::widget([
        // полученные данные
        'dataProvider' => $data,
        // Отображать 5 страниц
        'pager' => ['maxButtonCount' => 5],
        // колонки с данными
        'columns' => [
            [
                'label' =>"ID", // название столбца
                'attribute' => 'id', // атрибут
                //'value'=>function($data){return $data->id;} // объявлена анонимная функция и получен результат
            ],
            [
                'label' => 'Имя пользователя',
                'attribute' => 'username',
                //'value' => function($data) { return $data->name; },
            ],
            [
                'label' => 'Email',
                'attribute' => 'email',
                //'value' => function($data) { return $data->name; },
            ],
            [
                'label' => 'Роль',
                'attribute' => 'role',
                //'value' => function($data) { return $data->name; },
            ],
            [
              'label'=>'Создание',
                'value' => function ($model, $index, $widget) {
                    return Yii::$app->formatter->asDate($model->created_at,'php:d.m.y H:i:s');;
                }
            ],
            ['class' => 'yii\grid\ActionColumn',
                'template' => '{update}{delete}',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        $url=Url::to(['users','m'=>'update','id'=>$key]);
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                            'title' => 'Редактировать',
                            'data-pjax' => '0',
                        ]).' ';
                    },
                    'delete' => function ($url, $model, $key) {
                        $url=Url::to(['users','m'=>'delete','id'=>$key]);
                        return Html::a('<span class="glyphicon glyphicon-remove"></span>', $url, [
                            'title' => 'Удалить',
                            'data-confirm' => 'Вы уверены что хотите удалить?',
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ]);
                    },
                ],
            ],
        ],
    ]);
    ?>
</div>