<?php
namespace backend\controllers;

use common\models\Access;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use backend\models\Users;
use yii\helpers\Url;
/**
 * Site controller
 */
class SiteController extends Controller
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
                        'actions' => ['logout', 'index','users'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
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

        if (!in_array($action->id,['login', 'error','logout'])) {
            if (Access::isGuest())
            {
                $this->redirect(Url::to(['login']));
                return false;
            }
            if (!Access::isAdmin())
            {
                //$this->redirect('login');
                throw new ForbiddenHttpException('Доступ к этому разделу запрещен!');
            }
        }
        return parent::beforeAction($action);
    }
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if (Access::isAdmin()) return $this->goBack();
            $model->addError('username', 'У вас нет доступа к этому разделу!');
            Yii::$app->user->logout();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionUsers($m='')
    {
        switch($m)
        {
            case 'add':
                return $this->UsersAdd();
            case 'update':
                return $this->UsersUpdate();
            case 'delete':
                return $this->UsersDelete();

        }

        return $this->UsersIndex();
    }

    private function UsersIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Users::find(),
            'sort' => [ // сортировка по умолчанию
                'defaultOrder' => ['username' => SORT_DESC],
            ],
            'pagination' => [ // постраничная разбивка
                'pageSize' => 10, // 10 новостей на странице
            ],
        ]);
        return $this->render('users',['data'=>$dataProvider]);
    }

    private function UsersAdd()
    {
        $model=new Users();
        if ($model->load(Yii::$app->request->post(),'Users'))
        {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Пользователь сохранен!");
                return $this->redirect(Url::to(['users', 'm' => 'index']));
            }
            else
                Yii::$app->getSession()->setFlash('error', Html::errorSummary($model));
        }
        return $this->render('usersForm',['model'=>$model]);
    }

    private function UsersUpdate()
    {
        $model=$this->getEditUser();
        if (!$model) return $this->redirect(Url::to(['users', 'm' => 'index']));
        if ($model->load(Yii::$app->request->post(),'Users'))
        {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Пользователь сохранен!");
                return $this->redirect(Url::to(['users', 'm' => 'index']));
            }
            else
                Yii::$app->getSession()->setFlash('error', Html::errorSummary($model));
        }
        return $this->render('usersForm',['model'=>$model]);
    }

    private function UsersDelete()
    {
        $user=$this->getEditUser();
        if (!$user) return $this->redirect(Url::to(['users', 'm' => 'index']));
        if ($user->id==yii::$app->user->id)
        {
            Yii::$app->getSession()->setFlash('error', 'Вы не можете удалить свою учетную запись!');
        }
        else{
            $user->delete();
        }
        return $this->redirect(Url::to(['users', 'm' => 'index']));

    }

    private function getEditUser()
    {
        $id=yii::$app->request->get('id',-1);
        $model=Users::findOne($id);
        if (!$model)
            Yii::$app->session->setFlash('error', "Пользователь не найден!");
        return $model;
    }
}
