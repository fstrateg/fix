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

    var $chmaster;

    var $cfg;

    public $days_fix;
    public $dat_fix;

    public function init($sdat)
    {
        $this->dat=new Date();
        if ($sdat) $this->dat->set($sdat,'Ymd');
        $this->cfg=SettingsRecord::getValuesGroup('quality');

        $this->chmaster=!empty($this->cfg['chmaster']);
        $this->initDays('fix');
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

    public function getFixMsg()
    {
        return $this->cfg['fixmsg'];
    }

    public function getCaclDate()
    {
       return $this->dat->format();
    }

    public function getDateFix()
    {
        return $this->dat_fix->format();
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
        $staff=$type;
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
    public function findFixRecords()
    {

        $p1=$this->dat_fix->toMySqlRound();
        $cmd= yii::$app->db->createCommand('
SELECT a.resource_id,a.staff_name,a.appointed,a.services_id,a.client_phone,b.name,c.title,ifnull(q.status,0) stat,a.client_id,s.allcli
FROM records a
    inner join staff_prop s on a.staff_id=s.staff_id and s.prop_id=1
    left join clients b on b.id=a.client_id
	left join services c on trim(c.id)=a.services_id
    left join quality q on a.resource_id=q.record_id  and q.typ=1
WHERE a.id IN (
SELECT DISTINCT a.id
FROM (
SELECT a.*
FROM records a
WHERE date(appointed)=\''.$p1.'\' AND a.attendance=1 and a.deleted=0
) a,services b
WHERE INSTR(a.services_id,b.id)>0
)
order by a.appointed
    ');
        $list=$cmd->queryAll();
        $list=$this->setShortName($list);
        $list=$this->filter_onlynew($list,$p1);
        return $list;
    }



    private function filter_onlynew($list,$dat)
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