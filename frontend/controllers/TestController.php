<?php


namespace frontend\controllers;


use common\components\Messages;
use yii\base\Controller;

class TestController extends Controller
{
    public function actionSavemsg()
    {
        Messages::sendMessage("Тестовое сообщение","Тестовое сообщение","-","test");
    }
}