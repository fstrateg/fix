<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property sms_text
 * @property sms_text_noname
 */
class SMSSettings extends ActiveRecord
{
    public static function tableName()
    {
        return '{{settings_sms}}';
    }

    public function rules()
    {
        return [
            [['sms_on'],'integer'],
            [['lat'],'string'],
            [['sms_text'],'string'],
            [['sms_text_noname'],'string'],
        ];
    }

    public static function getPages()
    {
        $rez=[];
        $id=SMSSettings::find()->select('city')->distinct()->all();

        foreach($id as $i)
        {
            $rez[]=['id'=>$i->city,'name'=>CityRecord::findOne($i->city)->name];
        }

        return $rez;
    }

    public static function saveData($data)
    {
        $err=false;
        foreach($data as $k=>$row)
        {
            $rw=SMSSettings::findOne($k);
            $data['SMSSettings']=$row;
            $rw->load($data);
            $err=$err||!$rw->save();
        }
        \common\models\SettingsRecord::setValue('sms','second',$data['5']['sms_time']);
        if ($err===true){
            \Yii::$app->getSession()->setFlash('error', 'Проблемма с сохранением, что-то не так!');
        }
        else{
            \Yii::$app->getSession()->setFlash('success', 'Данные успешно внесены в базу!');
        }
    }
}