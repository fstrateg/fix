<?php

use frontend\models\WorkStudioDetails;
/**
 * @var $this yii\web\View;
 * @var $model WorkStudioDetails;
 */
$this->title = 'La Letty';
?>
<h2><?= $model->klientName ?></h2>
<div class="panel panel-default">
    <div class="panel-heading">Настройка индивидуальных напоминаний по услугам <b>ВОСК-ШУГАРИНГ</b> клиента:</div>
    <div class="panel-body">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="d21" <?= $model->sms_wax_21?> value="1">
            <label class="form-check-label" for="d21">Напоминание 21 день</label>
        </div>
        <div class="form-group">
            <input id="d21val" type="number" value="<?= $model->sms_wax_21val?>">
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="d42" <?= $model->sms_wax_42?> value="1">
            <label class="form-check-label" for="d42">Напоминание 42 дня</label>
        </div>
        <div class="form-group">
            <input id="d42val" type="number" value="<?= $model->sms_wax_42val?>">
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="d0" disabled="disabled" value="1" checked>
            <label class="form-check-label" style="color: #c9c9c9" for="d0">1-ая SMS (при записи)</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="d1" <?= $model->sms_wax_01?> value="1">
            <label class="form-check-label" for="d1">2-ая SMS (напоминание)</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="d5" <?= $model->sms_wax_05?> value="1">
            <label class="form-check-label" for="d5">3-я SMS (скрабирование)</label>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">Настройка индивидуальных напоминаний по услугам <b>ЛАЗЕРНОЙ Эпиляции</b> клиента:</div>
    <div class="panel-body">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="l30" <?= $model->sms_laser_30?> value="1">
            <label class="form-check-label" for="l30">Напоминание 30 дней</label>
        </div>
        <div class="form-group">
            <input id="l30val" type="number" value="<?= $model->sms_laser_30val?>">
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="l60" <?= $model->sms_laser_60?> value="1">
            <label class="form-check-label" for="l60">Напоминание 60 дней</label>
        </div>
        <div class="form-group">
            <input id="l60val" type="number" value="<?= $model->sms_laser_60val?>">
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">Настройка индивидуальных напоминаний по услугам <b>ЭЛЕКТРОЭПИЛЯЦИИ</b> клиента:</div>
    <div class="panel-body">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="e30" <?= $model->sms_electro_30?> value="1">
            <label class="form-check-label" for="e30">Напоминание 30 дней</label>
        </div>
        <div class="form-group">
            <input id="e30val" type="number" value="<?= $model->sms_electro_30val?>">
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="e60" <?= $model->sms_electro_60?> value="1">
            <label class="form-check-label" for="e60">Напоминание 60 дней</label>
        </div>
        <div class="form-group">
            <input id="e60val" type="number" value="<?= $model->sms_electro_60val?>">
        </div>
    </div>
</div>
<input id="cid" type="hidden" value="<?= $model->id ?>">
<script type="application/javascript">
    function savenote(){
        var data={
            id: -1,
            d21: 0,
            d21val: 21,
            d42: 0,
            d42val: 42,
            d0: 1,
            d1: 0,
            d5: 0,
            l30: 0,
            l30val: 30,
            l60: 0,
            l60val: 60,
            e30: 0,
            e30val: 30,
            e60: 0,
            e60val: 60
        };
        data.id=document.getElementById('cid').value;
        if (document.getElementById('d21').checked) data.d21=1;
        var vl=document.getElementById('d21val').value;
        if (vl>0) data.d21val=vl;
        vl=document.getElementById('d42val').value;
        if (document.getElementById('d42').checked) data.d42=1;
        if (vl>0) data.d42val=vl;
        if (document.getElementById('d1').checked) data.d1=1;
        if (document.getElementById('d5').checked) data.d5=1;
        if (document.getElementById('l30').checked) data.l30=1;
        if (document.getElementById('l60').checked) data.l60=1;
        vl=document.getElementById('l30val').value;
        if (vl>0) data.l30val=vl;
        vl=document.getElementById('l60val').value;

        if (document.getElementById('e30').checked) data.e30=1;
        if (document.getElementById('e60').checked) data.e60=1;
        vl=document.getElementById('e30val').value;
        if (vl>0) data.e30val=vl;
        vl=document.getElementById('e60val').value;
        if (vl>0) data.e60val=vl;
        $.ajax({
            url: 'savenote',
            type: 'POST',
            data: {data: JSON.stringify(data)}
        });
    }
</script>