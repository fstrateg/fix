<?php
/**
 * Created by PhpStorm.
 * User: Alekseym
 * Date: 09.04.2019
 * Time: 22:01
 */

namespace common\models;


use common\components\SMS;
use common\components\Telegram;

class SMSLaser
{
    public $transaction_id;
    private $message;
    private $msg_noname;

    private $record;
    public $Dontsend=false;
    public $client_phone;
    public $HasError=false;
    private $lat=false;
    /**
     * @var ClientsRecord
     */
    public $client;
    public $error;

    public static function sendSmsNumber($day)
    {
        $clients=self::getClientsForReminder($day);
        foreach ($clients as $c)
        {
            $needsms = true;
            $rec = RecordsRecord::findOne(['resource_id' => $c['last_record']]);
            /* Клиент уже записался */
            $cnt = RecordsRecord::find()->where([
                    'and',
                    'client_id=' . $c['id'],
                    "appointed>'{$c['last_visit']}'"
                ])->count();
            if ($cnt > 0 || empty($rec['services_id'])) {
                  $needsms = false;
            } else {
                    /* Проверка на напоминание */
               if ($rec->naprav <> 'L') $needsms = false;
            }

            if ($needsms) {
                $sms = new SMS();
                $sms->setNumber($day);
                $sms->setRecord($rec);
                $sms->send();
                //echo $c['name'].'<br>';
                //Telegram::instance()->sendMessage('Alex',$sms->getMessageText(), $sms->client_phone);
                //Telegram::instance()->sendMessageAll($sms->getMessageText(), $sms->client_phone);
            }
            $done = new Sms_doneRecord();
            $done->setAttribute('type', $day);
            $done->setAttribute('client_id', $c['id']);
            $done->setAttribute('record_id', $c['last_record']);
            $done->save();
        }
        echo 'Обработано: ' . count($clients) . ' клиентов';
    }

    private static function getClientsForReminder($day)
    {
        if ($day==30)
        {
            $exc='laser_except_30';
            $col='laser_30';
            $ds=SettingsRecord::findValue('sms','laser30');
        }
        else
        {
            $exc='laser_except_60';
            $col='laser_60';
            $ds=SettingsRecord::findValue('sms','laser60');
        }
        //$exc=$day==21?'wax_except_21':'wax_except_42';
        $sql = "select a.*,IFNULL(e.vl,0) exc,IFNULL(d.vl,{$ds}) days
        from  clients a left join sms_done b on (a.id=b.client_id and a.last_record=b.record_id and b.type={$day})
        left join sms_exception e on (e.client_id=a.id and e.sms_type='{$exc}')
        left join sms_exception d on (d.client_id=a.id and d.sms_type='{$col}')
        where a.deleted=0
        and date_add(a.last_visit,interval IFNULL(d.vl,{$ds}) day) between  date_sub(curdate(),interval 1 day) and curdate()
        and b.type is null
        and IFNULL(e.vl,0)=0
        limit 20";
        $clients = \yii::$app->db->createCommand($sql)->queryAll();
        return $clients;
    }

}