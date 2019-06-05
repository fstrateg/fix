<?php
namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class CityForm extends ActiveRecord
{
    public $name;
    public $country;

    public static function tableName()
    {
        return '{{city}}';
    }

    public function rules()
    {
        return [
            [['name','country'],'required']
        ];
    }

    public function attributeLabels()
    {
        return [
          [
              'name'=>Yii::t('sprav','Название города'),
              'country'=>Yii::t('sprav','Страна')
            ]
        ];
    }
}