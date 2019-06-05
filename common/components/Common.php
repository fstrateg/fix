<?php

namespace common\components;

class Common
{
    public static function AsyncQuery($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 900);
        curl_exec($ch);
        curl_close($ch);
    }
}