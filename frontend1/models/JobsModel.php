<?php
/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 16.02.2018
 * Time: 16:44
 */

namespace frontend\models;

use backend\models\SettingsRecord;
use common\components\Telegram;
use common\models\ClientsRecord;
use common\models\RecordsRecord;
use yii;
use common\components\Date;
use common\components\simple_html_dom;
use rapidweb\googlecontacts\factories\ContactFactory;

class JobsModel extends \stdClass
{

    public static function GetLastVisit()
    {
        $dt=new Date();
        $dt->subDays('2');
        $db=yii::$app->getDb();
        $query=$db->createCommand("SELECT r.*,a.resource_id last_record
FROM (
SELECT MAX(a.appointed) appointed,b.id, MAX(b.last_visit) last_visit
FROM records a,clients b
WHERE (a.appointed>:dt) AND (a.attendance=1)
AND (a.deleted=0) AND b.id=a.client_id AND b.deleted=0
AND b.last_visit<a.appointed
GROUP BY b.id) r,records a
WHERE a.client_id=r.id AND a.appointed=r.appointed",['dt'=>$dt->toMySqlRound()]);
        $rs=$query->queryAll();
        foreach($rs as $rw)
        {
            $db->createCommand('update clients set last_visit=:lv,last_record=:lr where id=:id',
                    [   'lv'=>$rw['appointed'],
                        'lr'=>$rw['last_record'],
                        'id'=>$rw['id']
                    ])->execute();
            $db->createCommand('delete from sms_done where client_id=:id',['id'=>$rw['id']])->execute();
        }
        echo count($rs);
        echo '<br>OK';
    }

    public static function getNaprav()
    {
       $recs=RecordsRecord::find()->where(['naprav'=>'N'])->limit(500)->all();
        if ($recs==null)
        {
            echo 'Нет записей для апдейта!';
            return;
        }
        foreach($recs as $rw)
        {
            $sid=$rw->getAttribute('services_id');
            $rw->naprav=self::findNaprav($sid);
            $rw->save();
        }
        echo 'Записей '.count($recs);
    }

    public static function findNaprav($services_id)
    {
        if (empty($services_id)) return 'A';
        $cmd= yii::$app->db->createCommand("
select title,laser,electro,scrubbing from services a
where a.id in ($services_id)");
        $rows=$cmd->queryAll();
        if ($rows==null) return 'A';
        $scrub=false;
        foreach($rows as $rw)
        {
            if ($rw['laser']=='Y') return 'L';
            if ($rw['electro']=='Y') return 'E';
            if ($rw['scrubbing']==1) $scrub=true;
        }
        if ($scrub) return 'W';
        return 'A';
    }

    public static function SynchroGoogle()
    {
        //exit();
        $gcfg=self::getGoogleConfig();
        $curl="https://www.google.com/m8/feeds/contacts/{$gcfg->googleakk}/full/";
        $cl=ClientsRecord::find()->where(['googleid'=>''])->limit(50)->all();
        $i=0;
        foreach($cl as $cli)
        {
            $name=self::getGName($cli->getAttribute('name'),$cli->getAttribute('phone'));
            $cid=$cli->getAttribute('googleid');
            $n=true;
            if (!empty($cid))
            {
                $contact=ContactFactory::getBySelfURL($curl.$cid,$gcfg);
                //exit();
                if (!empty($contact->id)) {
                    $contact->name = $name;
                    $contact->phoneNumber=$cli->getAttribute('phone');
                    $contact->email = '';
                    try {
                        ContactFactory::submitUpdates($contact, $gcfg);
                    }
                    catch(\ErrorException $err)
                    {
                        echo $err->getMessage().'<br>';
                        echo $err->getTraceAsString();
                        echo '<br>';
                        print_r($contact);
                        exit();
                    }
                    $n=false;
                }
            }
            if ($n) {
                $contact = ContactFactory::create($name, $cli->phone, '', '', $gcfg);
                $id = basename($contact->id);
                $cli->setAttribute('googleid',$id);
            }
            $cli->setAttribute('gr','N');
            $cli->save();
            $i++;
        }
        echo 'Обработано клиентов:'.$i;
    }

    /**
     * Дополнительная синхронизация googla, так как не всегда контакты заходят с первого раза
     */
    public static function GoogleSynchroAdd()
    {

        $cmd=yii::$app->getDb()->createCommand("
Select b.name,b.phone,b.googleid
 from yclientslog a,clients b
 where a.resource='client'
 	and a.oper<>'DE'
	and a.dat>date_sub(now(),interval 12 hour)
	and b.id=a.resource_id
");
        $rows=$cmd->queryAll();
        if ($rows==null) return;
        $cont=count($rows);
        $rep=0;
        $gcfg=self::getGoogleConfig();
        foreach($rows as $cli) {
            $name = self::getGName($cli['name'], $cli['phone']);
            $cid = $cli['googleid'];
            if (!empty($cid)) {
                $contact = self::getContact($cid,$gcfg); //ContactFactory::getBySelfURL($curl . $cid, $gcfg);
                if (!$contact)
                    continue;
                if ($contact->name==$name) continue;
                try {
                    ContactFactory::delete($contact,$gcfg);
                    $contact=ContactFactory::create($name,$cli['phone'],'','',$gcfg);
                    $cli->googleid=basename($contact->id);
                    $cli->save();
                    $rep++;
                }
                catch(\ErrorException $err)
                {
                    echo $err->getMessage().'<br>';
                    echo $err->getTraceAsString();
                    echo '<br>';
                    print_r($contact);
                    exit();
                }
            }
        }

        Telegram::instance()->sendMessageAll("На обработку: {$cont} контактов.\nИсправлено: {$rep}.","Дополнительная синхронизация Google");
    }

    public static function test()
    {
        $gcfg=self::getGoogleConfig();
        $contact=self::getContact('609595eb8e005fde',$gcfg);
        $name=self::getGName('Анастасия','+77772032200');
        $contact->name = $name;
        ContactFactory::delete($contact);

        $contact = ContactFactory::create($name, $cli->phone, '', '', $gcfg);
        $id = basename($contact->id);
        $cli->setAttribute('googleid',$id);

        ContactFactory::submitUpdates($contact, $gcfg);
        print_r($contact);
    }

    public static function updateGoogleContact($phone)
    {
        if (empty($phone))
        {
            echo 'Телефон для синхронизации не задан';
            return;
        }
        $cli=ClientsRecord::find()->where("phone like '%{$phone}%'")->one();
        if (empty($cli))
        {
            echo 'Клиент с номером телефона '.$phone.' не найден!';
            return;
        }
        $name=self::getGName($cli->name,$cli->phone);
        $gcfg=self::getGoogleConfig();
        $cont=self::getContact($cli->googleid,$gcfg);
        if (!empty($cont))
        {
            ContactFactory::delete($cont,$gcfg);
        }
        $cont=ContactFactory::create($name,$cli->phone,'','',$gcfg);
        $cli->googleid=basename($cont->id);
        $cli->save();
        echo $name.' Ok!';
    }

    private static function getContact($id,$gcfg)
    {
        $curl="https://www.google.com/m8/feeds/contacts/{$gcfg->googleakk}/full/";
        try {
            $contact = ContactFactory::getBySelfURL($curl . $id, $gcfg);
        }catch(\ErrorException $err){
            return null;
        }
        return $contact;
    }


    public static function FillGoogle()
    {
        $cfg=self::getGoogleConfig();
        $contacts = ContactFactory::getAll($cfg);
        //$c=ContactFactory::getBySelfURL($contacts[0]->selfURL,$cfg);
        $i=0;
        foreach ($contacts as $contact) {
            $i++;
            //if ($i<4000) continue;
            if (!isset($contact->phoneNumber)) continue;
            $phone = $contact->phoneNumber[0]['number'];
            $client = ClientsRecord::findOne(['phone' => $phone, 'gr'=>'Y']);
            if ($client) {
                $id = basename($contact->id);
                $client->googleid = $id;
                $client->gr="N";
                $client->save();
            }
        }
        echo 'Обработано клиентов:'.$i;
    }


    private static function getGoogleConfig()
    {
        $c=\common\models\SettingsRecord::getValuesGroup("google");
        $cfg=new \stdClass();
        $cfg->clientID=$c['clientid'];
        $cfg->clientSecret=$c['clientsecr'];
        $cfg->redirectUri="";
        $cfg->developerKey="";
        $cfg->refreshToken=$c['refresh'];
        $cfg->googleakk=urlencode($c['googleakk']);
        return $cfg;
    }

    public static function getGName($name, $phone) {
        if (!preg_match("/(\+7)|(\+8)/ui", $phone, $matches))
            preg_match("/\+\d\d\d/ui", $phone, $matches);
        $code = $matches[0];
        $phone_name = str_replace($code, "", $phone);
        $code = str_replace("+", "", $code);
        return $name . "  " . $code . "_" . $phone_name;
    }
    
    public static function getKurs()
    {       
        echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
        $dat=new Date();
        echo "<CurrencyRates Name=\"Daily Exchange Rates\" Date=\"{$dat->format()}\">\n";
        $s=0;
        try{
            $html = simple_html_dom::file_get_html('http://demirbank.kg/ru-ru');
            $s=1;
            $ret=$html->find('div.pricing-table div.owl-carousel table',0);
            //echo $ret;
            $ret=$ret->find('tr');
            if (count($ret)>0) unset($ret[0]); 
            foreach ($ret as $rw)
            {
                $str=$rw->find("th",0)->plaintext;
                $str=trim($str);
                echo "<Currency ISOCode=\"{$str}\">\n";
                $str=$rw->find('td',1)->plaintext;
                echo "<Nominal>1</Nominal>\n<Value>{$str}</Value>\n</Currency>\n";
            }
        }
        catch(yii\db\Exception $error)
        {
            $err[0]="Ошибка загрузки страницы";
            $err[1]="Ошибка разбора страницы";
            echo "<Error>{$err[$s]}</Error>\n";
            echo "<ErrMsg>{$error->getMessage()}</ErrMsg>\n";
        }
        echo '</CurrencyRates>';
    }
}