<?php
use common\components\cpanel\CPanelBtn;
/* @var $this yii\web\View */

$this->title = 'Fix-Litty';
?>
<div class="site-index">
    <? $panel=CPanelBtn::begin();
    $panel->addPanelBtn([
        'text'=>'Пользователи',
        'url'=>\yii\helpers\Url::toRoute('/users'),
        'img'=>'user',
    ]);
    $panel->addPanelBtn([
        'text'=>'Мастера',
        'url'=>\yii\helpers\Url::toRoute('/app/masters'),
        'img'=>'female',
    ]);
    $panel->addPanelBtn([
        'text'=>'Сообщения',
        'url'=>\yii\helpers\Url::toRoute('/app/msg'),
        'img'=>'msg',
    ]);
    $panel->end();
    ?>
</div>
