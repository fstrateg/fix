<?php
use yii\grid\GridView;
use yii\helpers\Html;
use \backend\models\Users;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 */
$this->title="Мастер подвязка пользователя";
$users=Users::getUsersList();
$susers=\common\models\StaffUserRecord::getUsersForStaff();
$dd=isset($susers[$model->id])?$susers[$model->id]['user_id']:0;
?>
<form class="form-vertical" method="post">
    <div class="form-group">
        <b>Мастер: </b></br>
        <?= $model->name ?>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="col-sm-3">Подвязать пользователя:</label>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <?= Html::dropDownList('userid',$dd,$users,['class'=>'form-control']) ?>
            </div>
        </div>

    </div>
    <input type="hidden" name="m" value="save">
    <input type="hidden" name="id" value="<?= $model->id; ?>">
    <input type="hidden" name="_csrf-backend" value="<?=Yii::$app->request->getCsrfToken()?>" />
    <button type="submit" class="btn btn-success" style="width:100px">Сохранить</button>
    <button type="button" class="btn btn-default" style="width:100px;margin-left: 10px" onclick="window.history.back()">Отмена</button>
</form>
