<?php

use skrtdev\Telegram\Message;
use skrtdev\NovaGram\Bot;

$Bot = new Bot("YOUR_BOT_TOKEN", [
    "json_payload" => false,        // json_payload: true for enable, false for disable
    "debug" => YOUR_USER_ID,        // your user_id
    "parse_mode" => 'HTML'          // you can use "Markdown" for Markdown
]);


    // function to demote a member
    function demoteMember($chatId, $userId) {
        global $Bot;
        $result = false;
        $demote = $Bot->promoteChatMember([
            "chat_id" => $chatId,
            "user_id" => $userId,
            "can_change_info" => false,
            "can_delete_messages" => false,
            "can_restrict_members" => false,
            "can_pin_messages" => false,
            "can_promote_members" => false
        ]);

        if ($demote) { $result = true; }
        return $result;
    }


    // function to promote a member
    function promoteMember($chatId, $userId) {
        global $Bot;
        $result = false;

        $promote = $Bot->promoteChatMember([
            "chat_id" => $chatId,
            "user_id" => $userId,
            "can_change_info" => true,
            "can_delete_messages" => true,
            "can_restrict_members" => true,
            "can_pin_messages" => true,
            "can_promote_members" => false
        ]);

        if ($promote) { $result = true; }
        return $result;
    }


    // function to custom promote a member
    function customPromote($chatId, $userId, $text) {
        global $Bot;
        $result = false;
        $ai = explode(" ", $text);
        unset($ai[0]);

        $promote = $Bot->promoteChatMember([
            "chat_id" => $chatId,
            "user_id" => $userId,
            "can_change_info" => boolval($ai[1]),
            "can_delete_messages" => boolval($ai[2]),
            "can_restrict_members" => boolval($ai[3]),
            "can_pin_messages" => boolval($ai[4]),
            "can_promote_members" => boolval($ai[5])
        ]);

        if ($promote) { $result = true; }
        return $result;
    }


    // function to change admin's title
    function changeAdminTitle($chatId, $userId, $text)
    {
        global $Bot;
        $result = false;
        $title = explode(" ", $text);
        unset($title[0]);
        $title = implode(" ", $title);

        $changeAdminTitle = $Bot->setChatAdministratorCustomTitle([
            "chat_id" => $chatId,
            "user_id" => $userId,
            "custom_title" => $title
        ]);

        if ($changeAdminTitle) $result = true;
        return $result;
    }


    // function to get json from a webpage with cURL
    function get_json($url){
        $base = "https://api.github.com/";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $base . $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $content = curl_exec($curl);
        curl_close($curl);
        return $content;
    }


    // function to get tiny url of a text
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


    // function to create an hastebin with a text
    function do_hastebin_file($text)
    {
        $url = 'https://hastebin.com/documents';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('data' => $text));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 0);

        $normal = "https://hastebin.com/";
        $r = json_decode(curl_exec($ch), TRUE);
        $normal = $normal . $r["key"];
        $raw = str_replace('com/', 'com/raw/', $normal);
        return array(get_tiny_url($normal), get_tiny_url($raw));
    }