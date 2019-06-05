<?php
/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 16.02.2018
 * Time: 16:49
 */

namespace common\components;

use yii;

class Date
{
    public $date;

    function __construct()
    {

        $this->date=self::now();
    }

    function __clone()
    {
        $this->date = clone $this->date;
    }

    public static function now()
    {
        return new \DateTime('now',self::timeZone());
    }

    public static function timeZone()
    {
        return new \DateTimeZone(yii::$app->timeZone);
    }

    public function toMySql()
    {
        return $this->date->format('Y-m-d H:i:s');
    }

    public function toMySqlRound()
    {
        return $this->date->format('Y-m-d');
    }

    public function format($format='d.m.Y')
    {
        return $this->date->format($format);
    }

    /**
     * @param string $date;
     * @param string $format;
     */
    public function set($date,$format = 'Y-m-d H:i:s')
    {
        $this->date=$this->date->createFromFormat($format,$date,self::timeZone());
    }

    /**
     * @param string $days
     * @return $this
     */
    public function subDays($days)
    {
        $di=new \DateInterval('P'.$days.'D');
        $this->date->sub($di);
        return $this;
    }

    /**
     * @param string $days
     * @return $this
     */
    public function addDays($days)
    {
        $di=new \DateInterval('P'.$days.'D');
        $this->date->add($di);
        return $this;
    }


    /**
     * @param $vl string
     * @return Date
     */
    public static function fromMysql($vl)
    {
        $rz=new Date();
        $rz->set($vl);
        return $rz;
    }

    /**
     * @return \DateTime
     */
    public function get()
    {
        return $this->date;
    }
}