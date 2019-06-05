<?php
namespace frontend\models;

use common\components\Date;
use common\models\SettingsRecord;
use yii\helpers\ArrayHelper;
use yii;

/**
 * Class LcWatsApp
 * @package frontend\models
 */
class LcWatsApp
{
    /**
     * @var $dat Date
     */
    var $dat;
    /**
     * @var $dat_laser Date
     */
    var $dat_laser;
    /**
     * @var $dat_laser Date
     */
    var $dat_wax;
    /**
     * @var $dat_laser Date
     */
    var $dat_electrod1;
    /**
     * @var $dat_laser Date
     */
    var $dat_electrod2;
    /**
     * @var $dat_laser Date
     */
    var $dat_electrod3;
    /**
     * @var $chmaster bool
     */
    var $chmaster;

    var $cfg;

    public $days_laser;
    public $days_wax;
    public $days_electrod1;
    public $days_electrod2;
    public $days_electrod3;

    public function init($sdat)
    {
        $this->dat=new Date();
        if ($sdat) $this->dat->set($sdat,'Ymd');
        $this->cfg=SettingsRecord::getValuesGroup('quality');
        /*$day=$this->cfg['laser']; //SettingsRecord::findValue('quality',);
        $this->days_laser=$day;
        $dd=clone $this->dat;
        $dd->subDays($day);
        $this->dat_laser=$dd;*/

        $this->chmaster=!empty($this->cfg['chmaster']);

        /*$dd=clone $this->dat;
        $day=$this->cfg['wax'];
        $this->days_wax=$day;
        $dd->subDays($day);
        $this->dat_wax=$dd;*/
        $this->initDays('wax');
        $this->initDays('laser');
        $this->initDays('electrod1');
        $this->initDays('electrod2');
        $this->initDays('electrod3');


        /*$dd=clone $this->dat;
        $day=$this->cfg['electrod1'];
        $this->days_electro1=$day;
        $dd->subDays($day);
        $this->dat_ee1=$dd;*/
    }

    private function initDays($param)
    {
        $dd=clone $this->dat;
        $day=$this->cfg[$param];
        $p='days_'.$param;
        $this->$p=$day;
        $dd->subDays($day);
        $p='dat_'.$param;
        $this->$p=$dd;
    }

    public function getLaserMsg()
    {
        return $this->cfg['lasermsg'];
    }

    public function getWaxMsg()
    {
        return $this->cfg['waxmsg'];
    }
    public function getEEMsg($param)
    {
        return $this->cfg['electro'.$param];
    }

    public function getCaclDate()
    {
       return $this->dat->format();
    }

    public function getDateLaser()
    {
        return $this->dat_laser->format();
    }

    public function getDateWax()
    {
        return $this->dat_wax->format();
    }

    public function getDateEl($type)
    {
        $p=dat_electrod.$type;
        return $this->$p->format();
    }
    public function getParamNext()
    {
        /* @var $dd Date */
        $dd=clone $this->dat;
        $dd->addDays(1);
        return $dd->format('Ymd');
    }

    public function getParamPrior()
    {
        /* @var $dd Date */
        $dd=clone $this->dat;
        $dd->subDays(1);
        return $dd->format('Ymd');
    }

    public static function getServices($services_id)
    {
        if (!$services_id) return null;
        $cmd= yii::$app->db->createCommand("
select title from services a
where a.id in ($services_id)");
        return $cmd->queryAll();
    }

    public function findLaserRecords()
    {
        $p1=$this->dat_laser->toMySqlRound();
        $query=$this->createQuery($p1,1,"laser='Y'");
        $cmd= yii::$app->db->createCommand($query);
        $list=$cmd->queryAll();
        $list=$this->setShortName($list);
        $list=$this->getChMasterRecords($list, $p1, 'L');
        return $list;
    }

    public function findElectroRecords($param)
    {
        $t='dat_electrod'.$param;
        $p1=$this->$t->toMySqlRound();
        $query=$this->createQuery($p1,$param+2,"electro='Y'");
        $cmd= yii::$app->db->createCommand($query);
        $list=$cmd->queryAll();
        $list=$this->setShortName($list);
        return $list;
    }

    private function createQuery($dat,$type,$servis)
    {
        /**
         * types
         * 1 - laser
         * 2 - wax
         * 3 - ee1
         * 4 - ee2
         * 5 - ee3
         */
        $staff=in_array($type,[3,4,5])?3:$type;
        $query="SELECT a.resource_id,a.staff_name,a.appointed,a.services_id,a.client_phone,b.name,c.title,ifnull(q.status,0) stat
FROM records a
    inner join staff_prop s on a.staff_id=s.staff_id and s.prop_id=$staff
    left join clients b on b.id=a.client_id
	left join services c on trim(c.id)=a.services_id
    left join quality q on a.resource_id=q.record_id and q.typ=$type
WHERE a.id IN (
SELECT DISTINCT a.id
FROM (
SELECT a.*
FROM records a
WHERE date(appointed)='$dat' AND a.attendance=1 and a.deleted=0
) a,services b
WHERE INSTR(a.services_id,b.id)>0 AND b.$servis
)
order by a.appointed";
        return $query;
    }
    /**
     * @param $dat string
     * @return array
     */
    public function findWaxRecords()
    {
       /* $day=SettingsRecord::findValue('quality','wax');
        $dat=new Date();
        $dat->set($pdat);
        $dat->subDays($day);
        //$dat->subDays($day+60);*/
        $p1=$this->dat_wax->toMySqlRound();
        $cmd= yii::$app->db->createCommand('
SELECT a.resource_id,a.staff_name,a.appointed,a.services_id,a.client_phone,b.name,c.title,ifnull(q.status,0) stat,a.client_id,s.allcli
FROM records a
    inner join staff_prop s on a.staff_id=s.staff_id and s.prop_id=2
    left join clients b on b.id=a.client_id
	left join services c on trim(c.id)=a.services_id
    left join quality q on a.resource_id=q.record_id  and q.typ=2
WHERE a.id IN (
SELECT DISTINCT a.id
FROM (
SELECT a.*
FROM records a
WHERE date(appointed)=\''.$p1.'\' AND a.attendance=1 and a.deleted=0
) a,services b
WHERE INSTR(a.services_id,b.id)>0 AND b.scrubbing=1
)
order by a.appointed
    ');
        //echo $cmd->sql;
        $list=$cmd->queryAll();
        //print_r($list);
        //exit();
        $list=$this->setShortName($list);
        $list=$this->filter_vax_onlynew($list,$p1);
        $list=$this->getChMasterRecords($list, $p1, 'W');
        return $list;
    }

    private function getChMasterRecords($list, $dat,$typ)
    {
        if (!$this->chmaster) return $list;
        $qtyp=($typ=='L')?1:2;
        $sql="
        Select rez.resource_id,c.name,ifnull(q.status,0) stat,r.client_phone,r.services_id,s.title,s2.name staff_name,concat(s1.name,'<br> v <br>',s2.name) staff_change,
concat(n1.name,'<br> v <br>',n2.name) naprav_ch
from
(
    Select recs.resource_id,recs.client_id,recs.staff_id,b2.staff_id staff_id_last,recs.naprav,b2.naprav naprav_last

	from (
        SELECT a.resource_id,a.client_id,a.staff_id,a.appointed,a.naprav,max(b.appointed) dt
		FROM records a left join records b on (a.client_id=b.client_id and a.appointed>b.appointed and b.deleted=0 and b.attendance=1)
		WHERE a.id IN (
        SELECT DISTINCT a.id
				FROM (
                    SELECT a.*
					FROM records a
					WHERE DATE(appointed)='%s' AND a.attendance=1 AND a.deleted=0 AND naprav='%s'
					) a
		)
		group BY a.resource_id,a.client_id,a.appointed
	) recs,records b2
	where recs.client_id=b2.client_id and recs.dt=b2.appointed and b2.staff_id>0 and recs.staff_id<>b2.staff_id
) rez
 inner join records r on (r.resource_id=rez.resource_id)
 inner join clients c on (rez.client_id=c.id)
 left join quality q on (q.record_id=rez.resource_id and typ=%s)
 left join services s on (r.services_id=s.id)
 left join staff s1 on (s1.id=rez.staff_id_last)
 left join staff s2 on (s2.id=rez.staff_id)
 left join naprav n1 on (n1.code=rez.naprav_last)
 left join naprav n2 on (n2.code=rez.naprav)
 ";
        $cmd= yii::$app->db->createCommand(sprintf($sql,$dat,$typ,$qtyp));
        $l2= $cmd->queryAll();
        if ($l2)
        {
            $l2=$this->setShortName($l2);
            $l2=ArrayHelper::index($l2,'resource_id');
            $k=[];
            foreach($l2 as $key=>$item)
            {
                $item['ch']=1;
                $item['imgtext']='<b>Изменение по мастеру:</b><br>'.$item['staff_change'].'<br><br><b>Изменения по направлению:</b><br>'.$item['naprav_ch'];
                $l2[$key]=$item;
                $k[]=$key;
            }
            foreach($list as $key=>$l)
            {
                if (in_array($l['resource_id'],$k))
                {
                    $list[$key]['ch']=1;
                    $list[$key]['imgtext']=$l2[$l['resource_id']]['imgtext'];
                    unset($l2[$l['resource_id']]);
                }
            }
            foreach($l2 as $item) $list[]=$item;

        }
        return $list;
    }

    private function filter_vax_onlynew($list,$dat)
    {
        $ids=[];
        foreach ($list as $item) $ids[]=$item['client_id'];
        $usl=implode(',',$ids);
        if ($usl) $usl=" where client_id in ($usl) and date(appointed)<='".$dat."' AND attendance=1 and deleted=0";
        $cmd= yii::$app->db->createCommand('
Select client_id,count(1) cnt
from records'
            .$usl.' group by client_id');
        $table=$cmd->queryAll();
        $table=yii\helpers\ArrayHelper::index($table,'client_id');
        foreach ($list as $k=>$item)
        {
            if ($item['allcli']) continue;
            if (((int)$table[$item['client_id']]['cnt'])>1) unset($list[$k]);
        }
        return $list;
    }

    private function setShortName($list)
    {
        foreach($list as $k=>$rw)
        {
            $name = str_replace('ё', 'е', $rw['name']);
            preg_match("/([a-z]|[а-я])+/ui", $name, $matches);
            if (preg_match("/([a-z]|[а-я])+/ui", $name, $matches))
                $list[$k]['name']=$matches[0];
            else
            {
                unset($list[$k]);
            }
        }
        return $list;
    }
}