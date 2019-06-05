<?php
namespace backend\controllers;

use common\components\SMS;
use common\models\Access;
use common\models\ClientsRecord;
use app\models\CityRecord;
use app\models\SMSSettings;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;


/**
 * Site controller
 */
class SmsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['config','except','clients','exceptadd','exceptdelete','translate'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!Access::isAdmin())
            throw new ForbiddenHttpException('Доступ к этому разделу запрещен!');
        return parent::beforeAction($action);
    }

    public function actionTranslate($text)
    {
        return SMS::translate($text);
    }

    public function actionConfig()
    {
        $data=Yii::$app->request->post();
        if (isset($data['r'])) SMSSettings::saveData($data['r']);
        $pages=SMSSettings::getPages();
        return $this->render('config',['pages'=>$pages,'test'=>$data]);
    }

    public function actionExcept()
    {
        $pages=ClientsRecord::getPages();
        return $this->render('except',['pages'=>$pages]);
    }

    public function actionClients($term)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $rs=ClientsRecord::find()->where(['like','phone',$term])->select(['name','phone','id'])->orderBy('name')->limit(15)->all();
        if($rs !=null){
            $row_set = [];
            foreach ($rs as $row)
            {
                $row_set[] = ['label'=>$row->phone.' '.$row->name,'value'=>$row->phone,'id'=>$row->id]; //build an array
            }
            return $row_set;
        }else{
            false;
        }
    }
    public function actionExceptadd()
    {
        $clientid=yii::$app->request->post("idclient");
        if ($clientid==null)
        {
            $data=yii::$app->request->post("ClientsRecord");
            $clientid=$data['id'];
        }
        $client=ClientsRecord::findOne(['id'=>$clientid]);
        if ($client==null){
            $this->redirect('except');
            return null;
        }
        if ($client->setExcept(yii::$app->request->post("ClientsRecord")))
        {
            yii::$app->session->setFlash('success','Клиент успешно добавлен в список исключений');
            $this->redirect('except');
        }
        return $this->renderPartial('except.add.php',['client'=>$client]);
    }

    public function actionExceptdelete($id)
    {
        if (ClientsRecord::deleteFromExcept($id))
            yii::$app->session->setFlash('success','Клиент удален из списка исключений');
        $this->redirect('except');
    }
}
