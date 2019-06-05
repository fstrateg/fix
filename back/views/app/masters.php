<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use \yii\bootstrap\Alert;

/**
 * @var $this yii\web\View
 */
$this->title="Справочник мастеров";
echo '<h3>'.$this->title.'</h3>';
global $susers;
$susers=\common\models\StaffUserRecord::getUsersForStaff();
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
    [
        'label'=>'Пользователь',
        'value'=>function($data){
            global $susers;
            if (!isset($susers[$data->id])) return null;
            return $susers[$data->id]['username'];
}
    ],
['class' => 'yii\grid\ActionColumn',
'template' => '{update}',
'buttons' => [
        'update' => function ($url, $model, $key) {
        $url=Url::to(['masters','m'=>'update','id'=>$key]);
        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
        'title' => 'Редактировать',
        'data-pjax' => '0',
        ]);
        }
        ],
],
],
]);
?>
