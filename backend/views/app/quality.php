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
$vdays=$grp['fix'];
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
        if ($prop_id==1)
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
    <div class="panel-heading">Массаж</div>
    <div class="panel-body">
        <div class="form-group">
            <input id="vdays" class="days" type="number" name="vdays" value="<?= $vdays?>" size="5"/> <label for="vdays">Количество дней назад</label>
        </div>
        <div class="form-group">
            <input id="vall" type="checkbox" name="vall" /> <label for="vall">Все клиенты указанных мастеров</label>
        </div>
        <? outListStaff('fix',1) ?>
    </div>
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
        $('#vall').on('click',function(e){
            $('li.fix input').attr('checked',this.checked);
        });
    });
JS;
$this->registerJs($js);

$css = <<< CSS
ul.list{
    list-style-type: none;
}
li.fix{padding-bottom: 10px;}
input.days{
    width: 40px;
}
CSS;
$this->registerCss($css);
