<?php
use common\models\ClientsRecord;
use yii\grid\GridView;
use yii\jui\AutoComplete;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\helpers\Html;
use yii\widgets\Pjax;
/* @var $this yii\web\View
 *  @var $pages array
 */

$this->title = 'Исключения для СМС рассылок';
//if (count($pages)==0) return;
$clients=new ClientsRecord();

?>
<ul class="nav nav-tabs">
    <?
    $class=' class="active"';
    foreach($pages as $page)
    {?>
        <li<?= $class;?>><a data-toggle="tab" href="#page<?= $page['id']?>"><?= $page['name'] ?></a></li>
        <?
        $class='';
    } ?>
</ul>
    <div class="tab-content tab-bordered">

<?
    $class=' in active';
    foreach($pages as $page)
    {
        ?>
        <div id="page<?= $page['id'] ?>" class="panel-group pt1 tab-pane fade<?= $class ?>">
            <?   Pjax::begin(['enablePushState' => false]);?>
                <p>&nbsp;</p>
            <?= Html::beginForm(['exceptadd'],'post',['data-pjax' => '', 'class' => 'form-inline']); ?>
            <?= Html::label("Номер телефона") ?>&nbsp;
            <?= AutoComplete::widget([
                'clientOptions' => [
                    'source' => Url::to(['clients']),
                    'minLength'=>'2',
                    'select'=>new JsExpression("function( event, ui ) { $('#idClient').val(ui.item.id); }")
                ],
            ]);
            ?>
            <?= Html::input('hidden','idclient','',['id'=>'idClient']) ?>&nbsp;
            <?= Html::submitButton('Добавить'); ?>
            <? Pjax::end(); ?>
            <p>&nbsp;</p>
            <?
            $data=ClientsRecord::getDataProvider();
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
                        'label' => 'Клиент',
                        'attribute' => 'name',
                        //'value' => function($data) { return $data->name; },
                    ],
                    [
                        'label' => 'Телефон',
                        'attribute'=>'phone',
                    ],
                    [
                        'label' => 'Статус',
                        'value' => function ($data) {
                            $r=array();
                            if ($data->exception_0) $r[]='зап';
                            if ($data->exception_1) $r[]='напом';
                            if ($data->exception_5) $r[]='5';
                            if ($data->exception_21) $r[]='21';
                            if ($data->exception_42) $r[]='42';
                            return implode(', ',$r); },
                    ],
                    ['class' => 'yii\grid\ActionColumn',
                        'template' => '{delete}',
                        'buttons' => [
                            'delete' => function ($url, $model, $key) {
                                $url=Url::to(['exceptdelete','id'=>$key]);
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
<?
$class='';
    }
?>