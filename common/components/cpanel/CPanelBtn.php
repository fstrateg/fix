<?php
namespace common\components\cpanel;
use \yii\base\Widget;
use yii\helpers\Html;
class CPanelBtn extends Widget
{
    var $msg;
    public function init()
    {
        parent::init();
        $this->registerAssets();
        ob_start();
        ?>
        <style>


        </style>
<?
        echo '<div class="cpanel">';
    }
    public function addPanelBtn($btn)
    {
        $html='<div style="float:left">';
        $html.='<div class="panelbtn large">';
        $html.='<a href="'.$btn['url'].'">';
        $img="../images/icon-48-".$btn['img'].".png";
        $html.='<span class="cbtn '.$btn['img'].'"></span>';
        $html.='<span>'.$btn["text"].'</span></a>';
        $html.='</div></div>';
        echo $html;
    }
    public function addButton($btn)
    {
        $html='<div style="float:left">';
        $html.='<div class="panelbtn">';
        $html.='<a href="'.$btn['url'].'">';
        $html.='<span class="icon '.$btn["image"].'"></span>';
        $html.='<span>'.$btn["text"].'</span></a>';
        $html.='</div></div>';
        echo $html;
    }
    public function addSave($btn)
    {
        $btn["image"]="save";
        $this->addButton($btn);
    }
    public function run()
    {
        echo '</div>';
        $content = ob_get_clean();
        return $content;
    }
    public function registerAssets()
    {
        $view=$this->getView();
        CPanelAssets::register($view);
    }

}
