<?php
/**
 * Created by PhpStorm.
 * User: Alekseym
 * Date: 17.06.2019
 * Time: 22:37
 */

namespace common\components;

use common\models\SysMessagesRecord;

class Messages
{

    public static function sendMessage($msg,$info,$phone,$group)
    {
        $rec=new SysMessagesRecord();
        $rec->setAttributes([
            'phone'=>$phone,
            'msg'=>$msg,
            'info'=>$info,
            'grp'=>$group
        ], false);
        $dat=new Date();
        $rec->setAttribute('dt',$dat->toMySql());
        $rec->save();
    }

    public static function sendMessage2($msg,$info = '',$group = 'info')
    {
        self::sendMessage($msg,$info,'-',$group);
    }
}