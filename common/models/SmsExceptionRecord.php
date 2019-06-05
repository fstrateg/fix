<?php

namespace common\models;
use yii\db\ActiveRecord;

class SmsExceptionRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{sms_exception}}';
    }
}