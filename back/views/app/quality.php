<?php
use common\models\StaffRecord;
use common\models\SettingsRecord;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
/**
 * @var $this yii\web\View
 */
$this->title="Контроль качества";

$grp=SettingsRecord::getValuesGroup('quality');

/*$ldays=SettingsRecord::findValue('quality','laser');
$vdays=SettingsRecord::findValue('quality','wax');*/
$ldays=$grp['laser'];
$vdays=$grp['wax'];
$onnew=($grp['onnew']=='1')?'checked':'';
$chmaster=($grp['chmaster']=='1')?'checked':'';


function outListStaff($name,$prop_id)
{
    $items=['1'=>'Все клиенты','0'=>'Новые'];
    $rs=StaffRecord::find()->orderBy('name')->where(['deleted'=>0])->all();
    $rr=\common\models\StaffPropRecord::getPropForStaff($prop_id);
    $r0=[];
    foreach($rr as $r) $r0[]=$r['id'];
    //print_r($rr);
    echo '<ul class="list">';
    foreach($rs as $s) {
        $check=in_array($s['id'],$r0)?'checked':'';
        $ck="<input type='checkbox' name='{$name}[{$s['id']}]' value='1' $check> {$s['name']}";
        $hd="<input type='hidden' name='{$name}[{$s['id']}]' value='0'>";
        if ($prop_id==2)
        {
            $vl=isset($rr[$s['id']]['allcli'])?$rr[$s['id']]['allcli']:0;
            //if($s['id']==273552) {echo $rr[$s['id']]['onnew']; exit();}
            $onn=Html::dropDownList("allcli[{$s['id']}]",$vl,$items);
        }
        else
        {
            $onn='';
        }
        echo "<li class='$name'>$hd $ck $onn</li>";
    }
    echo '</ul>';
}
$url=\yii\helpers\Url::to('/admin/app/qualitysave');
$form = ActiveForm::begin(['action'=>$url]);

?>
<div class="form-group">
    <a id="staffrefresh" class="btn btn-primary" href='#'>Обновить штат</a>
    <a class="btn btn-default pull-right" href='<?= \yii\helpers\Url::to('qualitymsg')?>'>Сообщения</a>
</div>

    <div class="panel panel-default">
        <div class="panel-heading">Электро эпиляция</div>
        <div class="panel-body">
            <div class="form-group">
                <input id="eall" type="checkbox" name="eall"> <label for="eall">Все клиенты указанных мастеров</label>
            </div>
            <? outListStaff('electro',3) ?>
        </div>
    </div>

<div class="panel panel-default">
    <div class="panel-heading">Лазерная эпиляция</div>
    <div class="panel-body">
        <div class="form-group">
        <input id="ldays" class="days" type="number" name="ldays" value="<?= $ldays ?>" size="5"/> <label for="ldays">Количество дней назад</label>
        </div>
        <div class="form-group">
            <input id="lall" type="checkbox" name="lall"> <label for="lall">Все клиенты указанных мастеров</label>
        </div>
        <? outListStaff('laser',1) ?>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">Шугаринг/Воск эпиляция</div>
    <div class="panel-body">
        <div class="form-group">
            <input id="vdays" class="days" type="number" name="vdays" value="<?= $vdays?>" size="5"/> <label for="vdays">Количество дней назад</label>
        </div>
        <div class="form-group">
            <input id="vall" type="checkbox" name="vall" /> <label for="vall">Все клиенты указанных мастеров</label>
        </div>
        <? outListStaff('vosk',2) ?>
    </div>
</div>
<div class="form-control">
    <input type="hidden" name="chmaster" value="0"/>
    <input id="chmaster" type="checkbox" name="chmaster" <?= $chmaster?> value="1"/> <label for="chmaster">Отображать клиентов сменивших мастера</label>
</div>
<div style="padding-top: 20px">
    <input type="submit" class="btn btn-success" value="Сохранить">
    <a class="btn btn-warning" href='#' onclick="window.location.reload()">Отмена</a>
</div>
<?php
ActiveForm::end();

$url=\yii\helpers\Url::to('/admin/sprav/staffrefresh');
$js = <<< JS
    $(document).ready(function(){
        $('#staffrefresh').on('click',function(){
            $.ajax("$url")
            .done(function(){
                window.location.reload();
            });
        });
        $('#lall').on('click',function(e){
            $('li.laser input').attr('checked',this.checked);
        });
        $('#vall').on('click',function(e){
            $('li.vosk input').attr('checked',this.checked);
        });
        $('#eall').on('click',function(e){
            $('li.electro input').attr('checked',this.checked);
        });
    });
JS;
$this->registerJs($js);

$css = <<< CSS
ul.list{
    list-style-type: none;
}
li.vosk,li.laser,li.electro{padding-bottom: 10px;}
input.days{
    width: 40px;
}
CSS;
$this->registerCss($css);
