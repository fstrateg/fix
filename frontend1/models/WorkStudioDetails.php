<?php
namespace frontend\models;

use common\models\ClientsRecord;
use common\models\RecordsRecord;
use common\models\SettingsRecord;
use common\models\SmsExceptionRecord;

class WorkStudioDetails
{
    const CH="checked";
    public $klientName;
    public $id;
    public $sms_00;
    public $sms_wax_01;
    public $sms_wax_05;
    public $sms_wax_21;
    public $sms_wax_42;
    public $sms_wax_21val;
    public $sms_wax_42val;
    public $sms_laser_30;
    public $sms_laser_60;
    public $sms_laser_30val;
    public $sms_laser_60val;

    public $sms_electro_30;
    public $sms_electro_60;
    public $sms_electro_30val;
    public $sms_electro_60val;

    public function __construct()
    {
        $ch=WorkStudioDetails::CH;
        $this->sms_wax_01=$ch;
        $this->sms_wax_05=$ch;
        $this->sms_wax_21=$ch;
        $this->sms_wax_42=$ch;
        $s=SettingsRecord::getValuesGroup("sms");
        $this->sms_wax_21val=$s['wax21'];
        $this->sms_wax_42val=$s['wax42'];
        $this->sms_laser_30=$ch;
        $this->sms_laser_60=$ch;
        $this->sms_laser_30val=$s['laser30'];
        $this->sms_laser_60val=$s['laser60'];
        $this->sms_electro_30=$ch;
        $this->sms_electro_60=$ch;
        $this->sms_electro_30val=$s['electro30'];
        $this->sms_electro_60val=$s['electro60'];
    }

    public function initRecord($id)
    {
        $this->id=$id;
        $record=RecordsRecord::findOne(['resource_id'=>$id]);
        $client=ClientsRecord::findOne(['id'=>$record->client_id]);
        $this->klientName=$client->name;
        $this->getExceptions($record->client_id);      // есть ли клиент в списке исключений

        /*$this->sms_wax_01=$client->exception_1==1?"":"checked";
        $this->sms_wax_05=$client->exception_5==1?"":"checked";
        $this->sms_wax_21=$client->exception_21==1?"":"checked";
        $this->sms_wax_42=$client->exception_42==1?"":"checked";
        $this->sms_wax_21val=$client->sms_21_sent;
        $this->sms_wax_42val=$client->sms_42_sent;*/
    }

    private function getExceptions($client_id)
    {
        $e=SmsExceptionRecord::findAll(['client_id'=>$client_id]);
        $ch=WorkStudioDetails::CH;
        if (!$e) return false;
        foreach($e as $item)
        {
            switch($item->sms_type)
            {
                case "wax_except_1":
                    $this->sms_wax_01=$item->vl==1?"":$ch;
                    break;
                case "wax_except_5":
                    $this->sms_wax_05=$item->vl==1?"":$ch;
                    break;
                case "wax_except_21":
                    $this->sms_wax_21=$item->vl==1?"":$ch;
                    break;
                case "wax_except_42":
                    $this->sms_wax_42=$item->vl==1?"":$ch;
                    break;
                case "wax_21":
                    $this->sms_wax_21val=$item->vl;
                    break;
                case "wax_42":
                    $this->sms_wax_42val=$item->vl;
                    break;
                case "laser_except_30":
                    $this->sms_laser_30=$item->vl==1?"":$ch;
                    break;
                case "laser_except_60":
                    $this->sms_laser_60=$item->vl==1?"":$ch;
                    break;
                case "laser_30":
                    $this->sms_laser_30val=$item->vl;
                    break;
                case "laser_60":
                    $this->sms_laser_60val=$item->vl;
                    break;

                case "electro_except_30":
                    $this->sms_electro_30=$item->vl==1?"":$ch;
                    break;
                case "electro_except_60":
                    $this->sms_electro_60=$item->vl==1?"":$ch;
                    break;
                case "electro_30":
                    $this->sms_electro_30val=$item->vl;
                    break;
                case "electro_60":
                    $this->sms_electro_60val=$item->vl;
                    break;

            }
        }
        return true;
    }

    /**
     * @param $data array
     * @return bool
     */
    public function saveData($data)
    {
        /**
         * @var $client ClientsRecord
         */
        $this->id=$data->id;
        $record=RecordsRecord::findOne(['resource_id'=>$data->id]);
        $e=SmsExceptionRecord::findAll(['client_id'=>$record->client_id]);
        $fields=explode(',','wax_except_1,wax_except_5,wax_except_21,wax_except_42,wax_21,wax_42,laser_except_30,laser_except_60,laser_30,laser_60,electro_except_30,electro_except_60,electro_30,electro_60');
        $i=0;
        foreach($fields as $item)
        {
            if (isset($e[$i]))
            {
                $r=$e[$i++];
            }
            else
            {
                $r=new SmsExceptionRecord();
                $r->client_id=$record->client_id;
            }
            $r->sms_type=$item;
            $r->vl=$this->getValue($data,$item);
            $r->save();
        }
        while(isset($e[$i]))
        {
            $e[$i++]->delete();
        }
        return true;
    }

    private function getValue($data,$item)
    {
        $rez='-';
        switch($item)
        {
            case 'wax_except_1':
                $rez=$data->d1==0?1:0;
                break;
            case 'wax_except_5':
                $rez=$data->d5==0?1:0;
                break;
            case 'wax_except_21':
                $rez=$data->d21==0?1:0;
                break;
            case 'wax_except_42':
                $rez=$data->d42==0?1:0;
                break;
            case 'wax_21':
                $rez=$data->d21val;
                break;
            case 'wax_42':
                $rez=$data->d42val;
                break;
            case 'laser_except_30':
                $rez=$data->l30==0?1:0;
                break;
            case 'laser_except_60':
                $rez=$data->l60==0?1:0;
                break;
            case 'laser_30':
                $rez=$data->l30val;
                break;
            case 'laser_60':
                $rez=$data->l60val;
                break;
            case 'electro_except_30':
                $rez=$data->e30==0?1:0;
                break;
            case 'electro_except_60':
                $rez=$data->e60==0?1:0;
                break;
            case 'electro_30':
                $rez=$data->e30val;
                break;
            case 'electro_60':
                $rez=$data->e60val;
                break;
        }
        return $rez;
    }
}