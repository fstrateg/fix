<?php
/**
 * Created by PhpStorm.
 * User: Alekseym
 * Date: 17.06.2019
 * Time: 22:44
 */

namespace common\models;


use yii\data\ActiveDataProvider;

class SysMessagesRecord extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%sys_messages}}';
    }

    public function attributeLabels() {
        return [
            /* Другие названия атрибутов */
            'dt' => 'Дата',
            'msg' => 'Сообщение',
            'phone' => 'Номер клиента',
            'grp'=>'Группа',
            'info'=>'Информация'
        ];
    }
}

