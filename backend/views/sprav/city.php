<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
/* @var $this yii\web\View
 * @var $citys backend\models\CityRecord
 */

$this->title = 'La-Litty';
?>
<div class="site-index">
    <?
    echo Html::a('<span class="glyphicon glyphicon-pencil"></span> Добавить',['city','m'=>'add'],['class'=>'btn btn-success']);
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
                'label' => 'Заголовок',
                'attribute' => 'name',
                //'value' => function($data) { return $data->name; },
            ],
            ['class' => 'yii\grid\ActionColumn',
                'template' => '{update}{delete}',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        $url=Url::to(['city','m'=>'update','id'=>$key]);
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                            'title' => 'Редактировать',
                            'data-pjax' => '0',
                        ]).' ';
                    },
                    'delete' => function ($url, $model, $key) {
                        $url=Url::to(['city','m'=>'delete','id'=>$key]);
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