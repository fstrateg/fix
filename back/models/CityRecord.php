<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class CityRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{city}}';
    }

    public function rules()
    {
        return [
            [['name'],'string'],
            [['country'],'integer'],
            [['name','country'],'required','message'=>'Поле {attribute} не может быть пустым!']
        ];
    }

    public function attributeLabels()
    {
        return [

                'name'=>Yii::t('app','Название города'),
                'country'=>Yii::t('app','Страна')

        ];
    }

    public function getCountrys()
    {
        return ['1'=>'Кыргызстан','2'=>'Казахстан','3'=>'Россия'];
    }
}