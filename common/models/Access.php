<?php
namespace common\models;

use yii;
use yii\base\BaseObject;

class Access extends BaseObject
{
    public static $isOper;

    public static function isOper()
    {
        return self::testRole('oper');
    }

    public static function isGuest()
    {
        return yii::$app->user->isGuest;
    }

    public static function isAdmin()
    {
        return self::testRole('admin');
    }

    public static function isMaster()
    {
        return self::testRole('master');
    }

    private static function testRole($role)
    {
        if (self::isGuest()) return false;
        return self::getRole()==$role?true:false;
    }

    private static function getRole()
    {
        return yii::$app->user->identity->role;
    }

}