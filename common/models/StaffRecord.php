<?php
namespace common\models;
use common\components\Date;
use Yii;
/**
* Class ServicesRecord
* @package common\models
* @property $id
* @property $name
* @property $created
*/
class StaffRecord extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%staff}}';
    }

    public function rules()
    {
        return [
        [['id'],'integer'],
        [['name'],'string'],
        [['created'],'string'],
        ];
    }

    public static function RefreshAll()
    {
        ini_set('max_execution_time', 900);
        $cfg=SettingsRecord::getValuesGroup('yclients');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://api.yclients.com/api/v1/staff/{$cfg['company']}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer {$cfg["token"]}"
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $response=json_decode($response,true);
        foreach($response as $staff)
        {
            $rw=StaffRecord::findOne($staff['id']);
            if ($rw==null) //---- new ----
            {
                $rw=new StaffRecord();
                $rw->id=$staff['id'];
                $rw->created=(new Date())->toMySql();
                $rw->name=$staff['name'];
                $rw->save();
            }
            else
            {
                if ($rw->name!=$staff['name'])
                {
                    $rw->name=$staff['name'];
                    $rw->save();
                }
            }
        }
    }

    public static function initRec($resource_id,$data)
    {
        $rez=self::findOne($resource_id);
        $rez=$rez!=null?$rez:new StaffRecord();
        $rez->id=$resource_id;
        $rez->name=$data['name'];
        return $rez;
    }
}
