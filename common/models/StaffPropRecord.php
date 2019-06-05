<?php
namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ServicesRecord
 * @package common\models
 * @property $id
 * @property $name
 * @property $created
 */
class StaffPropRecord extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%staff_prop}}';
    }

    public static function getPropForStaff($prop_id)
    {
        $rws=self::findAll(['prop_id'=>$prop_id]);
        $rez=array();
        foreach($rws as $rw) $rez[$rw['staff_id']]=['id'=>$rw['staff_id'],'allcli'=>$rw['allcli']];

        return $rez;
    }

    public function UpdateAllCli($id,$cli)
    {
        $db=$this->getDb();
        $cmd=$db->createCommand()->update('staff_prop',['allcli'=>$cli],['staff_id'=>$id]);
        $cmd->execute();
        //$db->createCommand("update ".$this->tableName()." set allcli=$cli where id=$id")->execute();
    }
}