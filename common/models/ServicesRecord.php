<?php
namespace common\models;
use common\components\Telegram;
use Yii;
/**
 * Class ServicesRecord
 * @package common\models
 * @property $status
 * @property $title
 * @property $scrubbing
 * @property $remind
 * @property $deleted
 * @property $laser
 * @property $electro
 * @property $moderated
 */
class ServicesRecord extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%services}}';
    }

    public function rules()
    {
        return [
            [['scrubbing'],'integer'],
            [['remind'],'integer'],
            [['laser'],'string'],
            [['electro'],'string'],
        ];
    }

    public function attributeLabels()
    {
        return [

            'title'=>Yii::t('app','Название Услуги'),
            'scrubbing'=>Yii::t('app','СМС скрабироваться'),
            'remind'=>Yii::t('app','Напомнить о себе (21,42)'),
            'laser'=>Yii::t('app','Лазерная эпиляция'),
            'electro'=>Yii::t('app','Электро эпиляция'),

        ];
    }

    public static function initRec($resource_id,$data)
    {
        $rez=self::findOne($resource_id);
        $rez=$rez!=null?$rez:new ServicesRecord();
        $rez->id=$resource_id;
        $rez->title=$data['title'];
        return $rez;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert)
        {
            Telegram::instance()->sendMessage("Alex",$this->title,"Добавлен новый сервис");
        }
        return true;
    }
}