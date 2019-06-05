<?php
namespace common\components\smsprovider;

use yii\base\BaseObject;
use common\models\SettingsRecord;
use common\components\Telegram;
/**
 * Class SMSNikita
 * @package common\components
 * @property $login
 * @property $user
 * @property $sendername
 */
class SMSNikita extends BaseObject
{
    /**
     * @var $login
     */
    public $login;
    /**
     * @var string $password
     */
    public $password;
    /**
     * @var string $sendername
     */
    public $sendername;

    function __construct($config = [])
    {
        $config=SettingsRecord::getValuesGroup('nikita');
        parent::__construct($config);
    }

    private function post_content($postdata)
    {
        $url = "https://smspro.nikita.kg/api/message";
        $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, "c://coo.txt");
        //curl_setopt($ch, CURLOPT_COOKIEFILE,"c://coo.txt");

        $content = curl_exec($ch);
        $err     = curl_errno($ch);
        $errmsg  = curl_error($ch);
        $header  = curl_getinfo($ch);
        curl_close($ch);

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return $header;
    }

    public function sendSMS($phoneNumber, $message, $transactionID)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".
            "<message>".
            "<login>" . $this->login . "</login>".
            "<pwd>" . $this->password . "</pwd>".
            "<sender>" . $this->sendername . "</sender>".
            "<phones>".
            "<phone>" . $phoneNumber . "</phone>".
            "</phones>".
            "<text>" . $message . "</text>".
            "<id>" . $transactionID . "</id>".
            //"<test>1</test>".
            "</message>";

        try
        {
            $result = $this->post_content($xml);
            $responseXML = false;
            if (isset($result['content']))
                $responseXML=$result['content'];
            if ($responseXML===false)
            {
                $result=$result['errmsg'];
                Telegram::instance()->sendMessageAll("SMS не отправлено: {$result}",$phoneNumber);
                //Telegram::instance()->sendMessageAll("SMS не отправлено:{$message}",$phoneNumber);
                return 0;
            }
            $response = new \SimpleXMLElement($responseXML);
            if ($response->status<>0)
            {
                Telegram::instance()->sendMessageAll("SMS не отправлено: Ошибка от Никиты! Статус: {$response->status}",$phoneNumber);
                $response->status=0;
            }
            return +$response->status;
        }
        catch(\Exception $e)
        {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            var_dump($e->getTrace());
            Telegram::instance()->sendMessageAll("SMS не отправлено: {$e->getMessage()}\r\n $message",$phoneNumber);
        }
        return 12;
    }
}