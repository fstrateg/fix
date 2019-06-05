<?php
use frontend\models\WorkStudio;
use common\components\Date;
use yii\helpers\Html;
/**
 * @var $this yii\web\View;
 * @var $model WorkStudio;
 */
$this->title = 'La Letty';
echo $model->staffname;
?>
    <div class="btn-toolbar" role="toolbar" aria-label="...">
        <div class="btn-group mr-2" role="group" aria-label="First group">
            <a class="btn btn-default" href="/st/index">Сегодня</a>
        </div>
        <div class="btn-group mr-2" role="group" aria-label="First group">
            <?= $model->getParamPrior() ?>
            <?= $model->getParamNext() ?>
        </div>
        <div class="btn mr-2">
            <?= $model->getCaclDate(); ?>
        </div>
    </div>
    <hr>
<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead>
        <tr>
            <th>#</th>
            <th>Время</th>
            <th>Клиент</th>
            <th>Услуги</th>
        </tr>
        </thead>
        <?
        $i=0;
        foreach($model->getRecordsForStaff() as $items)
        {
            $dat=new Date();
            $dat->set($items['appointed']);
            ?>
            <tr data-id="<?= $items['id']?>">
                <td><?= ++$i ?></td>
                <td><?= $dat->format('d.m.Y H:i') ?></td>
                <td><?= $items['client']?></td>
                <td><ul><?
                        $srv=$model->getServices($items['services_id']);
                        if (!empty($srv))
                            foreach($srv as $s)
                            {
                                echo '<li>'.$s['title'].'</li>';
                            }
                     ?></ul></td>
            </tr>
            <?
        }
?>
        </table>
    <div id="frmedit" class="wrap" style="width:0; background: #CCC">
    <div class="container">
            <div id="panel">

            </div>
            <button id="savenote" class="btn">Сохранить</button> <button id="escape" class="btn">Отменить</button>
    </div>
    </div>
    <?
$js=<<< JS
    $(document).ready(function() {
        $('tr td').on('click',
            function(e){
                var id=$(this).parent().attr('data-id');
                $.ajax({
                url:'/st/note',
                type: 'POST',
                data: {id: id}
                })
                .done(function(data){
                    $('#panel').html(data);
                    $("#frmedit").css('width','100%');
                    $("#frmedit").css('height','100%');
                    $("#frmedit").css('overflow-y','scroll');
                    $("#frmedit").css('background','#fff');
                    //alert(id);
                });
            });
        $('#escape').on('click',
            function(){
                document.getElementById("frmedit").style.width = "0%";
                $("#frmedit").css('background','#ccc');
            });
        $('#savenote').on('click',function(){
             savenote();
             $('#escape').click();
        });
    });
JS;
$css= <<< CSS
#frmedit{
position: fixed;
background: white;
top: 0;
left: 0;
width: 0;
min-height: 100%;
z-index: 1;
overflow-x: hidden;
transition: 0.8s;
box-shadow: 0 0 10px #888;
}
CSS;

    $this->registerJs($js);
    $this->registerCss($css);
?>