<?php
namespace common\models;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ServicesRecord
 * @package common\models
 * @property $id
 * @property $staff_id
 * @property $user_is
 */
class StaffUserRecord extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%staff_user}}';
    }

    public static function getUsersForStaff()
    {
        $arr=Yii::$app->db->createCommand("select a.*,b.username from staff_user a,user b where b.id=a.user_id")->queryAll();
        return ArrayHelper::index($arr,"staff_id");
    }

    public static function saveForm($staff_id,$user_id)
    {
        if (empty($user_id))
        {
            Yii::$app->session->setFlash("error","Не выбран пользователь!");
            return false;
        }
        $obj=self::findOne(['staff_id'=>$staff_id]);
        if ($obj==null)
        {
            $obj=new StaffUserRecord();
            $obj->setAttribute('staff_id',$staff_id);
        }
        $obj->setAttribute('user_id',$user_id);
        $obj->save();
        return true;
    }
}
