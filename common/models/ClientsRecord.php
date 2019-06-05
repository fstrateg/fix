<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use app\models\CityRecord;
use common\components\Telegram;

class ClientsRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{clients}}';
    }

    public static function getDataProvider()
    {
         $dataProvider = new ActiveDataProvider([
            'query' => ClientsRecord::find()->where(['or',['exception_0'=>1],['exception_1'=>1],['exception_5'=>1],['exception_21'=>1],['exception_42'=>1]]),
            'sort' => [ // сортировка по умолчанию
                'defaultOrder' => ['name' => SORT_ASC],
            ],
            'pagination' => [ // постраничная разбивка
                'pageSize' => 10, // 10 новостей на странице
            ],
        ]);
        return $dataProvider;
    }

    public function attributeLabels()
    {
        return
            [
                'name'=>Yii::t('app','Имя клиента'),
                'phone'=>Yii::t('app','Номер телефона'),
                'exception_0'=>Yii::t('app','СМС при записи'),
                'exception_1'=>Yii::t('app','СМС за N часов до проц.'),
                'exception_5'=>Yii::t('app','СМС через 5 дней'),
                'exception_21'=>Yii::t('app','СМС через 21 день'),
                'exception_42'=>Yii::t('app','СМС через 42 дня'),
            ]
        ;
    }

    public function setExcept($post)
    {
        if (!$post) return false;
        $client=ClientsRecord::findOne(['id'=>$post['id']]);
        $client->exception_0=$post['exception_0'];
        $client->exception_1=$post['exception_1'];
        $client->exception_5=$post['exception_5'];
        $client->exception_21=$post['exception_21'];
        $client->exception_42=$post['exception_42'];
        return $client->update();
    }

    public static function deleteFromExcept($uid)
    {
        $client=ClientsRecord::findOne(['uid'=>$uid]);
        $client->exception_0=0;
        $client->exception_1=0;
        $client->exception_5=0;
        $client->exception_21=0;
        $client->exception_42=0;
        return $client->update();
    }

    public static function getPages()
    {
        $rez=[];
        $id=ClientsRecord::find()->select('city')->distinct()->all();
        foreach($id as $i)
        {
            $rez[]=['id'=>$i->city,'name'=>CityRecord::findOne($i->city)->name];
        }
        return $rez;
    }

    public static function initRec($resource_id,$data)
    {
        $rez=self::findOne(['id'=>$resource_id]);
        $rez=$rez!=null?$rez:new ClientsRecord();
        $rez->id=$resource_id;
        $rw=self::getRec($data);

        // нужна ли синхронизация с googlom
        if ($rw['name']==$rez->getAttribute('name')&&$rw['phone']==$rez->getAttribute('phone'))
            $rez->setAttribute('gr','N');
        else
            $rez->setAttribute('gr','Y');

        foreach($rw as $k=>$v)
            $rez->setAttribute($k,$v);
        return $rez;
    }

    private static function getRec($data)
    {
        $rec=[
            'name'=>$data['name'],
            'phone'=>$data['phone']
        ];
        return $rec;
    }

    public function afterSave($insert, $changedAttributes)
    {
           if ($insert)
            {
                $t=Telegram::instance();
                $t->sendMessage('Alex','Добавлен новый клиент: '.$this->name);
                $t->sendMessage('nikvoit','Добавлен новый клиент: '.$this->name);
            }
            return true;
    }

    public function shortName()
    {
        $name = str_replace('ё', 'е', $this->name);
        preg_match("/([a-z]|[а-я])+/ui", $name, $matches);
        return $matches[0];
    }
}