<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "settings".
 *
 * @property integer $id
 * @property string $group
 * @property string $param
 * @property string $val
 * @property string $name
 */
class SettingsRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group', 'param'], 'string', 'max' => 10],
            [['val'], 'string', 'max' => 250],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group' => 'Group',
            'param' => 'Param',
            'val' => 'Значение',
            'name' => 'Параметр',
        ];
    }
}
