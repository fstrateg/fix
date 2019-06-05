<?php
namespace backend\controllers;

use common\models\Access;
use app\models\CityRecord;
use common\models\ServicesRecord;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\data\ActiveDataProvider;
use backend\models\SettingsRecord;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Site controller
 */
class SpravController extends Controller
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
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['city','config','servis','staffrefresh'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!Access::isAdmin())
            throw new ForbiddenHttpException('Доступ к этому разделу запрещен!');
        return parent::beforeAction($action);
    }


    public function actionCity($m='index')
    {
        switch($m)
        {
            case 'index';
                return $this->cityIndex();
            case 'add':
                return $this->cityAdd();
            case 'update':
                $id=yii::$app->request->get('id',1);
                return $this->cityUpdate($id);
            case 'delete':
                return $this->cityDelete();
        }
    }

    private function cityIndex()
    {
        $citys=CityRecord::find()->all();
        $dataProvider = new ActiveDataProvider([
            'query' => CityRecord::find(),
            'sort' => [ // сортировка по умолчанию
                'defaultOrder' => ['name' => SORT_DESC],
            ],
            'pagination' => [ // постраничная разбивка
                'pageSize' => 10, // 10 новостей на странице
            ],
        ]);
        return $this->render('city',['citys'=>$citys,'data'=>$dataProvider]);
    }

    private function cityAdd()
    {
        $form=new CityRecord();
        if ($form->load(Yii::$app->request->post(),'CityRecord'))
        {
            if ($form->save())
                return $this->redirect(Url::to(['city','m'=>'index']));
            else
                Yii::$app->getSession()->setFlash('error', Html::errorSummary($form));
        }
        return $this->render('cityForm',['model'=>$form]);
    }

    private function cityUpdate($id)
    {
        $form=CityRecord::findOne($id);
        if ($form->load(Yii::$app->request->post(),'CityRecord'))
        {
            if ($form->save())
                return $this->redirect(Url::to(['city','m'=>'index']));
            else
                Yii::$app->getSession()->setFlash('error', Html::errorSummary($form));
        }
        return $this->render('cityForm',['model'=>$form]);
    }

    private function cityDelete()
    {
        $id=yii::$app->request->get('id',-1);
        CityRecord::findOne((int)$id)->delete();
        return $this->redirect(Url::to(['city','m'=>'index']));
    }

    public function actionConfig($m='index')
    {
        switch($m)
        {
            case 'update':
                $id=Yii::$app->request->get('id');
                $rez=$this->configUpdate($id);
                break;
            default:
                $rez=$this->configIndex();
        }
        return $rez;
    }

    private function configIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => SettingsRecord::find(),
        ]);

        return $this->render('config', [
            'dataProvider' => $dataProvider,
        ]);
    }

    private function configUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('config');
        } else {
            return $this->renderPartial('configForm', [
                'model' => $model,
            ]);
        }
    }

    protected function findModel($id)
    {
        if (($model = SettingsRecord::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionServis($m='index')
    {
        $rez='';
        switch($m)
        {
            case 'index':
                $rez=$this->servisIndex();
                break;
            case 'update':
                $id=Yii::$app->request->get('id');
                $rez=$this->servisUpdate($id);
                break;
        }
        return $rez;
    }

    private function servisIndex()
    {
        $request=yii::$app->request;
        $flt=$request->get('flt');
        $session=yii::$app->session;
        $session->set('servisurl',$this->buildargs($request->get()));

        $dataProvider = new ActiveDataProvider([
            'query' => ServicesRecord::find()->where('deleted<>1'),
        ]);
        $dataForModerate = new ActiveDataProvider(['query'=> ServicesRecord::find()->where('deleted<>1 and moderated=\'N\'')]);
        return $this->render('servis', [
            'dataProvider' => $dataProvider,
            'dataForModerate'=>$dataForModerate,
            'params'=>['flt'=>$flt],
        ]);
    }

    private function buildargs($arr)
    {
        $rez='';
        foreach($arr as $k=>$v)
        {
            $rez.=empty($rez)?'':'&';
            $rez.=$k.'='.$v;
        }
        return $rez;
    }

    private function servisUpdate($id)
    {
        $model=ServicesRecord::findOne($id);
        $post=Yii::$app->request->post();
        $model->load($post);
        if ($model->load($post))
        {
            $model->setAttribute('moderated','Y');
            $model->save();
            $add=yii::$app->session->get('servisurl','');
            $url=empty($add)?'servis':'servis?'.$add;
            return $this->redirect($url);
        } else {
            return $this->renderPartial('servisForm', [
                'model' => $model,
            ]);
        }
    }

    public function actionStaffrefresh()
    {
        \common\models\StaffRecord::RefreshAll();
    }
}
