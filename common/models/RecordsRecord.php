<?php
namespace common\models;
use common\components\Date;
use common\components\Telegram;
use common\components\SMS;
use common\components\SMSNikita;

/**
 * Class RecordsRecord
 * @package common\models
 * @property $attendance
 * @property $created
 * @property $resource_id
 */
class RecordsRecord extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%records}}';
    }

    public static function initRec($resource_id,$data)
    {
        $rez=self::findOne(['resource_id'=>$resource_id]);
        $rez=$rez!=null?$rez:new RecordsRecord();
        $rez->resource_id=$resource_id;
        if ($rez->isNewRecord) {
            $ct = new \DateTime('now', new \DateTimeZone(\yii::$app->timeZone));
            $rez->created = $ct->format('Y-m-d H:i:s');
        }
        $rw=self::getRec($data);
        foreach($rw as $k=>$v) {
            if (!$rez->isNewRecord&&$k=='services_id')
            {
                if ($rez->getAttribute(services_id)!=$v) $rez->setAttribute('naprav','N');
            }
            $rez->setAttribute($k, $v);
        }
        return $rez;
    }

    public static function getRec($data)
    {
        $srv=[];
        foreach($data['services'] as $item) $srv[]=$item['id'];
        $data['sms_before']=empty($data['sms_before'])?self::getSMSBefore():$data['sms_before'];
        $rec=[
            'staff_id'=>$data['staff']['id'],
            'staff_name'=>$data['staff']['name'],
            'appointed'=>$data['date'],
            'sms_before'=>$data['sms_before'],
            'client_phone'=>$data['client']['phone'],
            'services_id'=>implode(',',$srv),
            'attendance'=>$data['attendance'],
        ];
        $rec['client_id']=(isset($data['client']['id']))?$data['client']['id']:'-1';
        return $rec;
    }

    private static function getSMSBefore()
    {
        $min=SettingsRecord::findValue('sms','second');
        return $min/60;
    }

    public function afterSave($insert, $changedAttributes)
    {
            if ($insert&&$this->status!='import')
            {
                if (Date::fromMysql($this->appointed)->get() < Date::now()) return true;
                $phone=$this->getAttribute('client_phone');
                if (empty($phone))
                {
                    Telegram::instance()
                        ->sendMessageAll("Мастер: {$this->getAttribute('staff_name')}\r\nЗапись: {$this->getAttribute('appointed')}",
                            "Нет номера телефона!");
                    return true;
                }
                //$t=Telegram::instance();

                $sms=new SMS();
                $sms->setNumber(0);
                $sms->setRecord($this);

                //$t->sendMessage('Alex',"Попытка отправки СМС",$sms->client_phone);
                if (!$sms->Dontsend) $sms->send();
                //$t->sendMessage('Alex',"Конец попытки",$sms->client_phone);

                /*$t=Telegram::instance();
                $t->sendMessageAll($sms->getMessageText(),$sms->client_phone);*/
                /*$msg=$sms->getMessageText();

                $t->sendMessage('Alex',$sms->getMessageText($msg),$sms->client_phone);
                $t->sendMessage('nikvoit',$sms->getMessageText($msg),$sms->client_phone);*/

                //Telegram::instance()->sendMessage('Alex','Добавилась новая запись на клиента: '.$this->client_id);
            }
            return true;
    }
}