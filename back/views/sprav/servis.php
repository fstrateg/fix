<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $dataForModerate yii\data\ActiveDataProvider */
/* @var $params array */

$this->title = 'Услуги';
$this->params['breadcrumbs'][] = $this->title;
$flt=$params['flt'];
if ($flt=='md')
    $data=$dataForModerate;
else
    $data=$dataProvider;
?>
    <div class="settings-record-index">

        <h1><?= Html::encode($this->title) ?></h1>

        <div id='editform'></div>
        <? //print_r($params['test']) ?>
        <ul class="nav nav-pills">
            <li<?= empty($flt)?' class="active"':''?>><a href="<?= Url::to('servis') ?>">Все (<?= $dataProvider->getTotalCount() ?>)</a></li>
            <li<?= empty($flt)?'':' class="active"'?>><a href="<?= Url::to('servis?flt=md') ?>">На модерацию (<?= $dataForModerate->getTotalCount() ?>)</a></li>
        </ul>
        <?= GridView::widget([
            'dataProvider' => $data,
            'columns' => [
                'title',
                [
                    'attribute'=>'scrubbing',
                    'format' => 'raw',
                    'value' => function ($model, $index, $widget) {
                        return Html::checkbox('scrubbing[]',$model->scrubbing,['onclick'=>'return false;']);
                    }
                ],
                [
                    'attribute'=>'remind',
                    'format' => 'raw',
                    'value' => function ($model, $index, $widget) {
                        return Html::checkbox('remind[]',$model->remind,['onclick'=>'return false;']);
                    }
                ],
                [
                    'attribute'=>'laser',
                    'format' => 'raw',
                    'value' => function ($model, $index, $widget) {
                        return Html::checkbox('laser[]',$model->laser=='Y',['onclick'=>'return false;']);
                    }
                ],
                [
                    'attribute'=>'electro',
                    'format' => 'raw',
                    'value' => function ($model, $index, $widget) {
                        return Html::checkbox('electro[]',$model->electro=='Y',['onclick'=>'return false;']);
                    }
                ],
                ['class' => 'yii\grid\ActionColumn',
                    'template' => '{update}',
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', "#", [
                                'title' => 'Редактировать',
                                'data-pjax' => '0',
                                'idkey'=>$key,
                                'class'=>'edit',
                            ]);
                        },
                    ]
                ],
            ],
        ]); ?>
    </div>

<?
$js2=<<<JS
$('.edit').on('click', function(event){
 event.preventDefault();
    var idkey=$(this).attr('idkey');
    $.ajax({
        url: '/admin/sprav/servis?m=update&id='+idkey,
        type: 'GET',
        success: function(res)
        {
            $('#editform').html(res);
            $('#modal').modal('show');
        }
    });
    });
JS;

$this->registerJs($js2);?>