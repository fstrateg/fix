<?php

namespace common\models;


use common\components\Date;
use \yii\db\ActiveRecord;

/**
 * Class Sys_logRecord
 * @package common\models
 * @property $id
 * @property $dat
 * @property $ip
 * @property $job
 * @property $details
 */
class Sys_logRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%sys_log}}';
    }

    /**
     * @param $job string
     * @param $details array
     */
    public static function saveJob($job,$details=null)
    {
        $rec=new Sys_logRecord();
        $rec->ip=$_SERVER['REMOTE_ADDR'];
        $rec->dat=Date::now()->format('Y-m-d H:i:s');
        $rec->job=$job;
        if ($details) $rec->details=json_encode($details);
        $rec->save();
    }
}