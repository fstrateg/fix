<?php
namespace frontend\models;

use common\components\Date;
use common\models\ClientsRecord;
use common\models\RecordsRecord;
use common\models\SettingsRecord;
use yii;

class YclientsImport
{
    var $token;
    var $user;
    var $company;
    var $count;

    function __construct()
    {
        ini_set('max_execution_time', 900);
        $cfg=SettingsRecord::getValuesGroup('yclients');
        //$cfg=yii::$app->components['yclients'];
        $this->token=$cfg['token'];
        $this->user=$cfg['user'];
        $this->company=$cfg['company'];
    }

    public function import()
    {
        yii::$app->db->createCommand('truncate table clients')->execute();
        $page=1;
        while ($this->getpage($page))
        {
            $page++;

        }

    }

    public function getpage($page)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://api.yclients.com/api/v1/clients/{$this->company}?page={$page}&count=50");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer {$this->token}, User {$this->user}"
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $response=json_decode($response,true);
        $this->loadClients($response);
        return count($response['data'])>0;
    }

    private function loadClients($response)
    {
        foreach ($response['data'] as $item)
        {
            $cl=new ClientsRecord();
            $cl->id=$item['id'];
            $cl->name=$item['name'];
            $cl->phone=$item['phone'];
            $cl->status='import';
            $cl->save();
        }
    }

    public function getKlient($id)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://api.yclients.com/api/v1/client/{$this->company}/{$id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer {$this->token}, User {$this->user}"
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $item=json_decode($response,true);
        if (is_array($item))
        {
            $cl=ClientsRecord::findOne('id='.$id);
            if ($cl) return true;
            $cl=new ClientsRecord();
            $cl->id=$item['id'];
            $cl->name=$item['name'];
            $cl->phone=$item['phone'];
            $cl->status='import';
            $cl->save();
            return true;
        }
        return false;
    }

    public function getRecords()
    {
        ini_set('max_execution_time', 900);
        $data=$this->loadRecords();
        foreach($data['data'] as $row)
        {
            if (!isset($row['client']['id'])) continue;
            echo 'resource_id='.$row['id'];
            $record=RecordsRecord::findOne(['resource_id'=>$row['id']]);
            if (!$record) $record=new RecordsRecord();
            $record->resource_id=$row['id'];
            if ($record->isNewRecord) {
                $ct = new \DateTime('now', new \DateTimeZone(\yii::$app->timeZone));
                $record->status='import';
                $record->created = $ct->format('Y-m-d H:i:s');
            }
            $rw=RecordsRecord::getRec($row);
            $client=ClientsRecord::findOne(['id'=>$row['client']['id']]);
            if ($client)
                $rw['client_phone']=$client->getAttribute('phone');
            else {
                $i=new \frontend\models\YclientsImport();
                $i->getKlient($row['client']['id']);
                $client=ClientsRecord::findOne(['id'=>$row['client']['id']]);
                if ($client) {
                    $rw['client_phone'] = $client->getAttribute('phone');
                    echo ' Новый клиент '.$client->getAttribute('name');
                }
                else
                {
                    unset($rw['client_phone']);
                    echo ' Клиент не найден ';
                }
            }
            foreach($rw as $k=>$v)
                $record->setAttribute($k,$v);
            $record->save();
            echo '<br>';
        }
        $dat=new Date();
        $dat->date->sub(new \DateInterval('PT3H'));
        $time=str_replace(' ','T',$dat->format('Y-m-d H:i:00'));
        SettingsRecord::setValue('import','time',$time);
        echo $time;
    }

    private function loadRecords()
    {
        $dat=SettingsRecord::findValue('import','time');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://api.yclients.com/api/v1/records/31224?changed_after={$dat}&count=500");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer {$this->token}, User {$this->user}"
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $response=json_decode($response,true);
        return $response;
    }
}