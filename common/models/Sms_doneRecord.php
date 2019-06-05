<?php
namespace common\models;
use \yii\db\ActiveRecord;

class Sms_doneRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%sms_done}}';
    }

}