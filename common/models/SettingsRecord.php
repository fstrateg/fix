<?php
namespace common\models;


use yii\db\ActiveRecord;

/**
 * @property val
 * @property group
 * @property param
 */
class SettingsRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{settings}}';
    }

    /**
     * @param $group string
     * @param $param string
     * @return string
     */
    public static function findValue($group,$param)
    {
        return self::findOne(['group'=>$group,'param'=>$param])->val;
    }

    public static function setValue($group,$param,$value)
    {
        $r = self::findOne(['group' => $group, 'param' => $param]);
        $r->val = $value;
        $r->save();
    }

    public static function getValuesGroup($group)
    {
        $rws=self::findAll(['group'=>$group]);
        $rz=array();
        foreach($rws as $rw)
            $rz[$rw->param]=$rw->val;
        return $rz;
    }
}