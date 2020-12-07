<?php

/*
    usage: doit("text");
*/

use skrtdev\Telegram\Message;
use skrtdev\NovaGram\Bot;

$Bot = new Bot("YOUR_BOT_TOKEN", [
    "json_payload" => false,        // json_payload: true for enable, false for disable
    "debug" => YOUR_USER_ID,        // the chat_id/user_id for sending errors
    "parse_mode" => 'HTML'          // you can use "Markdown" for Markdown
]);

function get_tiny_url($url)
{
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url=' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function doit($text)
{
    $url = 'https://hastebin.com/documents';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('data'=>$text));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 0);

    $normal  = "https://hastebin.com/";
    $r = json_decode (curl_exec($ch), TRUE);
    $normal = $normal.$r["key"];
    $raw = str_replace('com/','com/raw/',$normal);

    return array(get_tiny_url($normal), get_tiny_url($raw));
}

