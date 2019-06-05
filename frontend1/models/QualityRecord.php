<?php
namespace frontend\models;

use common\components\Date;
use Yii;
use yii\db\ActiveRecord;

/**
 * Class QualityRecord
 * @package common\models
 * @property $record_id
 * @property $status
 * @property $dat
 *
 */
class QualityRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%quality}}';
    }

    public static function SaveVal($typ, $id,$stat)
    {
        $obj=QualityRecord::findOne(['record_id'=>$id,'typ'=>$typ]);
        if ($obj!=null)
        {
            $obj->setAttribute('status',$stat);
        }
        else
        {
            $obj=new QualityRecord();
            $obj->setAttribute('record_id',$id);
            $obj->setAttribute('status',$stat);
            $obj->setAttribute('typ',$typ);
        }
        $obj->setAttribute('dat',(new Date())->toMySql());
        $obj->save();
    }

    public static function SaveVals($typ, $data)
    {
        $t=null;
        try {
            $t = Yii::$app->db->beginTransaction();
            foreach ($data as $item) {
                self::SaveVal($typ, $item->id, $item->vl);
            }
            $t->commit();
        }catch(\Exception $err)
        {
            $t->rollBack();
            return false;
        }
        return true;
    }
}