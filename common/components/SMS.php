<?php
namespace common\components;

use common\components\smsprovider\SMSKazinfo;
use common\models\ServicesRecord;
use common\models\SettingsRecord;
use common\models\Sms_doneRecord;
use common\models\SmsExceptionRecord;
use common\models\SMSSettings;
use common\models\ClientsRecord;
use common\models\RecordsRecord;
use yii\base\BaseObject;
use common\components\smsprovider\SMSNikita;

/**
 * Class SMS
 * @package common\components
 * @property $transaction_id;
 */
class SMS extends BaseObject
{
    private $transaction_id;
    private $message;
    private $msg_noname;
    /**
     * @var RecordsRecord
     */
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

    public function getMessageText()
    {
        return $this->_prepare();
    }

    public function setNumber($day)
    {
        $s=SMSSettings::findOne(['days'=>$day]);
        if (!$s->sms_on) $this->Dontsend=true;
        if ($s->lat=='Y') $this->lat=true;
        $this->transaction_id='c'.$day.'_';
        $this->message=$s->sms_text;
        $this->msg_noname=$s->sms_text_noname;
    }


    /**
     * @param $record RecordsRecord|integer
     */
    public function setRecord($record)
    {
        if (is_numeric($record))
        {
            $record=RecordsRecord::findOne($record);
        }
        $this->record=$record;
        $this->client_phone=$record->client_phone;
        $this->transaction_id.=$record->resource_id;
        $this->client=ClientsRecord::findOne(['id'=>$this->record->client_id]);
        if (!$this->client)
        {
            sleep(10); // Подождем 10 сек, возможно клиент идет следом.
            $this->client=ClientsRecord::findOne(['id'=>$this->record->client_id]);
        }
        if (!$this->client) // Клиент не найден попробуем импортировать
        {
            $i=new \frontend\models\YclientsImport();
            $i->getKlient($this->record->client_id);
            $this->client=ClientsRecord::findOne(['id'=>$this->record->client_id]);
        }
        if ($this->client!=null) //ищем исключение
        {
            $vl=SmsExceptionRecord::findOne(['client_id'=>$this->record->client_id,'sms_type'=>'wax_except_1']);
            if ($vl) $this->Dontsend = ($vl->vl == 1);
        }
        else
            $this->Dontsend=true;
    }

    private function _prepare()
    {
        $appointed=$this->record->appointed;
        $appointed = \DateTime::createFromFormat('Y-m-d H:i:s',$appointed,new \DateTimeZone(\yii::$app->timeZone));
        $app = $appointed->format('d.m.Y H:i');
        $app = explode(' ', $app);
        $date = $app[0];
        $time = $app[1];
        // MASTER
        $staff=$this->record->staff_name;
        // NAME
        $msg=$this->message;
        if (strpos($msg,'%NAME%')>=0)
        {
            if (!$this->client)
                $msg=$this->msg_noname;
            else {
                $msg = str_replace('%NAME%', $this->client->shortName(), $msg);
            }
        }
        if (strpos($msg,'%HH%'))
        {
            $c=self::getCurDate();
            $n=$appointed->diff($c);
            //$r=self::roundDateTime($r,30);
            //$n=$r->diff($appointed);
            $hh=($n->d*24+$n->h)*60+$n->i;
            $hh=round($hh/60);
           // $hh=$r->format('H:i');
            $hh=$this->record->sms_before;
            $msg=str_replace('%HH%',$hh,$msg);
        }

        $msg=str_replace('%DATE%',$date,
                str_replace('%TIME%',$time,
                    str_replace('%MASTER%',$staff, $msg)
                )
        );
        if ($this->lat) $msg=SMS::translate($msg);

        return $msg;
    }

    public function __get($name)
    {
        if ($name=='HasError') return !empty($this->error);
        return parent::__get($name);
    }

    public static function sendReminder()
    {
        // Выбираем время
        $min=SettingsRecord::findValue('sms','second');
        $time=self::getCurDate();
        $c=self::getCurDate();
        $p="PT{$min}M";
        //$time->add(new \DateInterval($p)); ---
        $time->sub(new \DateInterval($p));
        // Выбираем клиентов кому нужно отправить SMS
        $query=RecordsRecord::find()
            ->where(['and','sms_second=0','deleted=0',
                "date_sub(appointed,INTERVAL sms_before HOUR) between '{$time->format('Y-m-d H:i:s')}' and '{$c->format('Y-m-d H:i:s')}'"
            ]);
            /*->where(['and','sms_second=0','deleted=0',  ---
                ['<=','appointed',$time->format('Y-m-d H:i:s')],
                ['>','appointed',$c->format('Y-m-d H:i:s')]
            ]);*/

        $records=$query->all();
        if (count($records)==0) return 'Нет SMS';
        // Формируем текст сообщения
        foreach($records as $r)
        {
            $sms = new SMS();
            $sms->setNumber(1);
            $sms->setRecord($r);
            // Смотрим когда была создана заявка
            if ($sms->checkForSecond($min)) {
                // Отправляем
                if (!$sms->Dontsend) {
                    $sms->send();
                    $r->sms_second=1;
                    //Telegram::instance()->sendMessageAll($msg, $sms->client_phone);
                }
                else
                    $r->sms_second=3;
            }
            else
                $r->sms_second=3;
            $r->save();
        }

    }

    private function checkForSecond($min)
    {
        $ta=\DateTime::createFromFormat('Y-m-d H:i:s',$this->record->getAttribute('appointed'));
        $tc=\DateTime::createFromFormat('Y-m-d H:i:s',$this->record->getAttribute('created'));
        $ti=(($tc->getTimestamp()-$ta->getTimestamp())/60)+$min;
        return $ti<0;  //true SMS напоминание нужно
    }

    public static function sendSmsNumber($day)
    {
        $clients=$day==5?self::getClientsForScrub():self::getClientsForReminder($day);
        foreach ($clients as $c) {
            $needsms = true;
            $rec = RecordsRecord::findOne(['resource_id' => $c['last_record']]);
            if ($day == 5) {
                /* проверка на скраббинг */
                $cm = ServicesRecord::find()->where(['and', "deleted<>1", "id in ({$rec['services_id']})"])->sum('scrubbing');
                if ($cm == 0) $needsms = false;
            }
            if ($day == 21 || $day == 42) {
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
                    $cm = ServicesRecord::find()->where(['and', "deleted<>1", "id in ({$rec['services_id']})"])->sum('remind');
                    if ($cm == 0) $needsms = false;
                }
            }
            if ($needsms) {
                $sms = new SMS();
                $sms->setNumber($day);
                $sms->setRecord($rec);
                $sms->send();
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
        if ($day==21)
        {
            $exc='wax_except_21';
            $col='wax_21';
            $ds=SettingsRecord::findValue('sms','wax21');
        }
        else
        {
            $exc='wax_except_42';
            $col='wax_42';
            $ds=SettingsRecord::findValue('sms','wax42');
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

    private static function getClientsForScrub()
    {
        $day=5;
        $dat = new Date();
        $dat->subDays($day + 1);
        $p1 = $dat->toMySql();
        $dat = new Date();
        $dat->subDays($day);
        $p2 = $dat->toMySql();

        $sql = "select a.*,IFNULL(e.vl,0) exc
        from  clients a left join sms_done b on (a.id=b.client_id and a.last_record=b.record_id and b.type={$day})
              left join sms_exception e on (e.client_id=a.id and e.sms_type='wax_except_5')
        where a.deleted=0 and a.last_visit between '$p1' and '$p2' and b.type is null
          and IFNULL(e.vl,0)=0
        limit 20";
        $clients = \yii::$app->db->createCommand($sql)->queryAll();
        return $clients;
    }

    public static function getCurDate()
    {
        return new \DateTime('now',new \DateTimeZone('Asia/Bishkek'));
    }

    /*
     * @param $time  \DateInterval
     * @param $min int
     */
    public static function roundDateTime($time, $min=5)
    {
        $s=$min*60;
        $t=\DateTime::createFromFormat('H:i',$time->format('%H:%I'));
        $t->setTimestamp($s * round($t->getTimestamp() / $s));
        return $t;
    }

    public function send()
    {
        $t=Telegram::instance();
        $msg=$this->getMessageText();
        $sms=$this->getProvider(); //SMSNikita();
        $sms->sendSMS($this->client_phone,$msg,$this->transaction_id);
        //if ($this->lat) $msg=SMS::translate($msg);
        $t->sendMessageAll($msg,$this->client_phone." ({$this->transaction_id})");
        Messages::sendMessage($msg,$this->client_phone." ({$this->transaction_id})",$this->client_phone,"sms");
    }

    private function getProvider()
    {
        $provider=SettingsRecord::findValue('sms','provider');
        switch ($provider)
        {
            case "SMSNikita":
                return new SMSNikita();
            case "SMSKazinfo":
                return new SMSKazinfo();
        }
        return null;
    }

    public function sendtest()
    {
        $msg='test';
        $sms=$this->getProvider(); //SMSNikita();
        $sms->sendSMS($this->client_phone,$msg,'id504');
    }

    public static function translate($text)
    {
        $rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'J', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', "'", 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'j', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', "'", 'e', 'yu', 'ya');
        return str_replace($rus, $lat, $text);
    }
}