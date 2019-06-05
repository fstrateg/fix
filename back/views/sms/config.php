<?php
use app\models\SMSSettings;
use common\models\SettingsRecord;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View
 *  @var $pages array
 */

$this->title = 'Настройка СМС рассылок';
\yii\jui\Dialog::begin([
    'clientOptions' => [
        'modal' => true,
        'autoOpen' => false,
        'width'=>'50%',
    ],
    'options'=>[
        'id'=>'transl',
        'style'=>'hide',
    ]
]);
echo '<div id="DialogText"></div>';
\yii\jui\Dialog::end();
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
<span id="helpBlock" class="help-block small">Используйте вырожения %NAME%, %DATE%, %TIME%, %MASTER%, %HH% для подстановки значений в SMS</span>
    <?
    $class=' in active';
    foreach($pages as $page)
    {
        $form=ActiveForm::begin();
        ?>
        <div id="page<?= $page['id'] ?>" class="panel-group pt1 tab-pane fade<?= $class ?>">
            <?
            $set=SMSSettings::find(['city'=>$page['id']])->orderBy('ord')->all();
            foreach($set as $s) {
                $id=$s['id'];?>
                <div class="panel panel-default">
                    <div class="panel-heading"><?= $s->name; ?></div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12 col-sm-3">
                                <div class="checkbox">
                                    <label class="form-check-label">
                                        <input type="hidden" name="r[<?= $id ?>][sms_on]" value="0">
                                        <input type="checkbox" name="r[<?= $id ?>][sms_on]" class="form-check-input" value="1"<?= $s['sms_on']?' checked':'';?>>Включено</label>
                                        <? //$form->field($s,'sms_on')->checkbox(['class'=>'form-check-input','label'=>null,'name'=>"r[$id][sms_on]"]); ?>
                                </div>
                            </div>
<?
if(((int)$id)==5)
{ $val=SettingsRecord::findValue('sms','second');
?>
    <div class="checkbox col-xs-12 col-sm-5">
        Время до визита: <input type="number" style="width:60px" name="r[<?=$id?>][sms_time]" value="<?= $val ?>"/>мин
    </div>
<?
}
?>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-4 checkbox">
                                <label class="form-check-label">
                                    <input type="hidden" name="r[<?= $id ?>][lat]" value="N">
                                    <input type="checkbox" name="r[<?= $id ?>][lat]" class="form-check-input" value="Y"<?= $s['lat']=='Y'?' checked':'';?>>Отправлять латиницей
                                </label>
                                <button class="transl" type="button" title="На латинице" data-id="<?= $id?>"><i class="glyphicon glyphicon-list-alt" title="Посмотреть перевод на латинице"></i></button>
                            </div>
                        </div>
                        <?= $form->field($s,'sms_text')->textarea(['class'=>'form-control','rows'=>'2','name'=>"r[$id][sms_text]"])->label(false); ?>
                            <label class="small">Если имя клиента не известно, используем текст:</label>
                        <?= $form->field($s,'sms_text_noname')->textarea(['class'=>'form-control','rows'=>'2','name'=>"r[$id][sms_text_noname]"])->label(false); ?>
                            <!-- textarea class="form-control" rows="3" name="r[?= $id ?][sms_text]">?= $s['sms_text']; ?</textarea-->
                    </div>
                </div>
            <? }
            ?>
            <button type="submit" class="mt2 btn btn-success">Сохранить изменения</button>
        </div>
    <?
        ActiveForm::end();
    $class='';
    } ?>
</div>
<?php
$js=<<< JS
    $(document).ready(
        function(){
            $('button.transl').on('click',function(){
                var id=$(this).attr('data-id');
                var text=$('textarea[name="r['+id+'][sms_text]"').text();
                $.ajax('/admin/sms/translate',{
                data: {text: text}
                })
                    .done(function(data){
                        $('#DialogText').text(data);
                        $( "#transl" ).dialog( "open" );
                    })
                    .fail(function(xhr, ajaxOptions, thrownError){
                        alert(xhr.responseText);
                        });;

            });
        }
    );
JS;
$this->registerJs($js);