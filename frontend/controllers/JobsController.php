<?php
namespace frontend\controllers;

use backend\models\SettingsRecord;
use common\components\Common;
use common\components\Date;
use common\components\SMS;
use common\components\Telegram;
use common\models\SMSLaser;
use common\models\SMSElectro;
use common\models\SMSSettings;
use frontend\models\YclientsImport;
use frontend\models\YclientsLogRecord;
use frontend\models\JobsModel;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;


/**
 * Site controller
 */
class JobsController extends Controller
{
    public function beforeAction($action)
    {
        if (in_array($action->id, ['yclients'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Прием данных с хука
     */
    public function actionYclients()
    {
        ini_set('max_execution_time', 600);
        $log=new YclientsLogRecord();
        $curdate=new \DateTime('now',new \DateTimeZone('Asia/Bishkek'));
        $log->dat=$curdate->format('Y-m-d H:i:s');
        $log->ip= $_SERVER["REMOTE_ADDR"];
        $jsonPostData = file_get_contents("php://input");
        $log->data=$jsonPostData;
        $log->insert();
        YclientsLogRecord::doParse($log);

        // Запускаем google синхронизацию //
        //return $this->actionSgoogle(); // синронизация не успевает отработать
        //$this->actionDoyclients();
        //return 'OK';
    }

    /**
     * Парсинг данных
     */
    public function actionDoyclients()
    {
        $rws=YclientsLogRecord::find()->where(['done'=>0])->all();
        foreach($rws as $rw)
        {
            YclientsLogRecord::doParse($rw);
        }
        //YclientsLogRecord::doParse();
    }

    /**
    * Отправка тестовой СМС в телеграм
    */
    public function actionTestsms()
    {
        \common\components\Telegram::instance()->sendMessage('Alex','Тест прошел успешно!','test');
    }

    /**
     * СМС напоминание о необходимости прийти на сеанс
     */
    public function actionSendreminder()
    {
        // запускаем каждые 5 мин, по идее не должно тормазить
        SMS::sendReminder();
    }

    /**
    * Импорт справочника клиентов
    */
    public function actionLoadclients()
    {

        /*$m=new YclientsImport();
        $m->import();*/
        return 'Закрыто чтобы случайно не потереть';
    }

    /**
    * Тестовый метод
    */
    public function actionTest()
    {
        //echo phpinfo();
        //exit();
        //$t=new Telegram();
        //$t->sendMessageAll("test","test1");
        //echo \yii\helpers\Url::base(true);
        //echo \yii\helpers\Url::to('sgoogle',true);
        /*$id=4533;
        $sms=new SMS();
        //$sms->client_phone="555904504";
        $sms->client_phone="77766196331";
        //echo 'test';
        $sms->sendtest();
        //echo $sms->getMessageText();*/
        //imp=new YclientsImport();
        //$imp->getKlient(22772330);
        //echo JobsModel::getGName('Индира','+77019291009');
        /*$curdate=new \DateTime('now');
        echo $curdate->format('Y-m-d H:i:s');*/
        //JobsModel::test();
        echo "<a href='https://accounts.google.com/o/oauth2/auth?"
            . "access_type=offline&client_id=XXXX&"
            . "scope=".urlencode("https://www.google.com/m8/feeds")."&response_type=code&"
            . "redirect_uri=http://fix.laletty.ru/jobs/test2'>Google</a>";


    }

    public function actionTest2()
    {
        global $client_id;
        global $client_secret;
        global $redirect_uri;

        $redirect_uri="XXX";
        $client_secret="XXX";
        $client_id="XXX";

        $oauth2token_url = "https://accounts.google.com/o/oauth2/token";
        $clienttoken_post = array(
            "code" => $_REQUEST['code'],
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "redirect_uri" => $redirect_uri,
            "grant_type" => "authorization_code"
        );

        $curl = curl_init($oauth2token_url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $clienttoken_post);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $json_response = curl_exec($curl);
        curl_close($curl);

        $authObj = json_decode($json_response);
        print_r($authObj);
        if (isset($authObj->refresh_token)){
            //refresh token only granted on first authorization for offline access
            //save to db for future use (db saving not included in example)
            global $refreshToken;
            $refreshToken = $authObj->refresh_token;
        }

        $accessToken = $authObj->access_token;
        return $accessToken;

    }
    /**
    * Напоминание через N дней
    */
    public function actionSendday($day)
    {
        if (empty($day)||!in_array($day,['5','21','42'])) return;
        ini_set('max_execution_time', 600);
        SMS::sendSmsNumber($day);
    }

    public function actionLaserday($day)
    {
        if (empty($day)||!in_array($day,['30','60'])) return;
        ini_set('max_execution_time', 600);
        SMSLaser::sendSmsNumber($day);
    }

    public function actionElectroday($day)
    {
        if (empty($day)||!in_array($day,['30','60'])) return;
        ini_set('max_execution_time', 600);
        SMSElectro::sendSmsNumber($day);
    }

    public function actionGetlastvisit()
    {
        JobsModel::GetLastVisit();
    }

    public function actionGetvisits()
    {
       $model=new YclientsImport();
        $model->getRecords();
    }

    public function actionGetnaprav()
    {
        JobsModel::getNaprav();
    }

    public function actionSgoogle()
    {
        JobsModel::SynchroGoogle();
    }

    public function actionFillgoogle()
    {
        ini_set('max_execution_time', 1200);
        JobsModel::FillGoogle();
    }
    
    public function actionGetkurs() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/xml');
        return JobsModel::getKurs();
    }

    public function actionGoogleadd()
    {
        JobsModel::GoogleSynchroAdd();
    }

    public function actionUpdgoogle($phone)
    {
        JobsModel::updateGoogleContact($phone);
    }

    public function actionPhpinfo()
    {
        phpinfo();
    }
}
