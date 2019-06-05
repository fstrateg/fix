<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Конфигурация';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="settings-record-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <div id='editform'></div>
    <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'name',
        [
            'attribute'=>'val',
            'format' => 'raw',
            'value' => function ($model, $index, $widget) {
                $text=htmlentities(mb_substr($model->val,0,50));
                if (strlen($model->val)>50) $text.='...';
                return $text;
            }
        ],
        ['class' => 'yii\grid\ActionColumn',
            'template' => '{update}',
            'buttons' => [
                'update' => function ($url, $model, $key) {
                    $url=Url::to(['config','m'=>'update','id'=>$key]);
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
    $js=<<<JS
$('.edit').on('click', function(event){
    event.preventDefault();
    var idkey=$(this).attr('idkey');
    alert(idkey);
    });
JS;
$js2=<<<JS
$('.edit').on('click', function(event){
 event.preventDefault();
    var idkey=$(this).attr('idkey');
    $.ajax({
        url: '/admin/sprav/config?m=update&id='+idkey,
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