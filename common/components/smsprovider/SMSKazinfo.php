<?php
namespace common\components\smsprovider;

use yii\base\BaseObject;
use common\models\SettingsRecord;
/**
 * Class SMSNikita
 * @package common\components
 * @property $login
 * @property $user
 */
class SMSKazinfo extends BaseObject
{
    /**
     * @var $login
     */
    public $login;
    /**
     * @var string $password
     */
    public $password;

    function __construct($config = [])
    {
        $config=SettingsRecord::getValuesGroup('kazinfo');
        parent::__construct($config);
    }

    private function post_content($postdata)
    {
        $url = "http://kazinfoteh.org:9501/api";
        $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER,  0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'));
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
        /*$xml = '<?xml version="1.0" encoding="UTF-8"?>'.
            "<package login=\"{$this->login}\" password=\"{$this->password}\">".
            "<message><default sender=\"INFO_KAZ\"/>".
            "<msg recipient=\"{$phoneNumber}\">{$message}</msg>".
            "</message></package>";*/
        $post=[
            'action'=>'sendmessage',
            'username'=>$this->login,
            'password'=>$this->password,
            'messagetype'=>'SMS:TEXT',
            'originator'=>'INFO_KAZ',
            'recipient'=>$phoneNumber,
            'messagedata'=>$message
        ];
        //print_r($post);
        try
        {
            $result = $this->post_content($post);
            $responseXML = $result['content'];
            $response = new \SimpleXMLElement($responseXML);
            //print_r($response);
            $ret=((int)$response->data->acceptreport->statuscode);
            if ($ret<>0)
            {
                Telegram::instance()->sendMessageAll("SMS не отправлено: {$response->error}\r\n $message",$phoneNumber);
				throw new \Exception('Ошибка от Kazinfo! Статус: '.$response->error);
				
            }
            return +$ret;
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