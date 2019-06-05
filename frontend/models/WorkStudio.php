<?php
namespace frontend\models;

use common\components\Date;
use common\models\StaffRecord;
use common\models\StaffUserRecord;
use common\models\RecordsRecord;
use yii;

class WorkStudio
{
    /**
     * @var string $staffname
     */
    public $staffname;
    /**
     * @var int $staffid;
     */
    public $staffid=-1;

    public $dat;

    public function __construct()
    {
        $this->staffname="";
        $rw=StaffUserRecord::findOne(['user_id'=>yii::$app->user->id]);
        if ($rw) {
            $st = StaffRecord::findOne($rw->staff_id);
            if ($st) {
                $this->staffname = $st->name;
                $this->staffid = $st->id;
            }
        }
    }

    public function init($dat)
    {
        $d1=new Date();
        if (!$dat) {
            $dat=$d1->toMySqlRound();
        }
        else
        {
            $d=new Date();
            $d->set($dat,'Ymd');
            $i=$d->date->diff($d1->date);
            $dat=($i->days<=7)?$d->toMySqlRound():$d1->toMySqlRound();
        }
        $this->dat=$dat;
    }

    public function getRecordsForStaff()
    {
        $db=yii::$app->db;
        return $db->createCommand("Select a.resource_id id,a.appointed,a.client_id,b.name client,a.services_id
        from records a,clients b where a.staff_id={$this->staffid}
        and a.appointed like '{$this->dat}%'
        and a.deleted=0
        and b.id=a.client_id
        and b.deleted=0
        order by a.appointed")
            ->queryAll();
    }

    public static function getServices($services_id)
    {
        if (empty($services_id)) return null;
        $cmd = yii::$app->db->createCommand("
select title from services a
where a.id in ($services_id)");
        return $cmd->queryAll();
    }

    public function getCaclDate()
    {
        $d=new Date();
        $d->set($this->dat,'Y-m-d');
        return $d->format();
    }

    public function getParamPrior()
    {
        $d1=new Date();
        $d=new Date();
        $d->set($this->dat,'Y-m-d');
        $d->subDays(1);
        $i=$d1->date->diff($d->date);
        if ($i->days>7&&$i->invert==1)
            $html='<button class="btn btn-default" href="/st/index?dat='.$d->format('Ymd').'" disabled="disabled">&lt;</button>';
        else
            $html='<a class="btn btn-default" href="/st/index?dat='.$d->format('Ymd').'">&lt;</a>';
        return $html;
    }

    public function getParamNext()
    {
        $d1=new Date();
        $d=new Date();
        $d->set($this->dat,'Y-m-d');
        $d->addDays(1);
        $i=$d1->date->diff($d->date);
        if ($i->days>7&&$i->invert==0)
            $html='<button class="btn btn-default" href="/st/index?dat='.$d->format('Ymd').'" disabled="disabled">&gt;</button>';
        else
            $html='<a class="btn btn-default" href="/st/index?dat='.$d->format('Ymd').'">&gt;</a>';
        return $html;
    }
}