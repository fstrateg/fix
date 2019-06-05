<?php
namespace common\components;

use common\models\User;
use common\models\SettingsRecord;
use yii\base\Component;

class Telegram extends Component
{
    public $class = '';
    public $token = '';
    public $url = '';

    function __construct(array $config=[])
    {
        $config=SettingsRecord::getValuesGroup('telegram');
        parent::__construct($config);
    }

    /**
     * @param $chat_id
     * @param $msg
     */
    public function sendMessage($userName, $msg, $forinfo='')
    {
        $user=User::findOne(['username'=>$userName]);
        if (!$user&&$user->telegram) return;
        $this->sendMessageInChat($user->telegram,$msg,$forinfo);
    }

    public function sendMessageAll($msg,$forinfo='')
    {
        $users=User::find()->where('not telegram is null')->all();
        if (!$users) return;
        foreach($users as $u)
        {
            $this->sendMessageInChat($u->telegram,$msg,$forinfo);
        }
    }

    public function sendMessageInChat1($chat_id,$msg,$forinfo='')
    {
        /// Метод перестал работать после измениния SSL пришлось переделать на CURL ниже.
        $data=[];
        $data['parse_mode']='html';
        $data['chat_id']=$chat_id;
        $data['text'] = $forinfo?"<b>{$forinfo}</b>\r\n".$msg:$msg;


        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        echo "{$this->url}bot{$this->token}/sendMessage";
        $result = file_get_contents("{$this->url}bot{$this->token}/sendMessage", false, $context);
        //print_r($result);
        //$query = "{$this->apiUrl}{$this->token}/{$api_method}";
    }

    public function sendMessageInChat($chat_id,$msg,$forinfo='')
    {
        $data=[];
        $data['parse_mode']='html';
        $data['chat_id']=$chat_id;
        $data['text'] = $forinfo?"<b>{$forinfo}</b>\r\n".$msg:$msg;

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL,"{$this->url}bot{$this->token}/sendMessage"); # URL to post to
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded/r/n") ); # custom headers, see above
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($data) );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false); # Ignore Cert
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false); # Ignore Cert
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' ); # This POST is special, and uses its specified Content-type
        $result = curl_exec( $ch ); # run!
        curl_close($ch);
        //print_r($result);
    }

    /**
     * @return Telegram
     */
    public static function instance()
    {
        return new Telegram();
    }
}