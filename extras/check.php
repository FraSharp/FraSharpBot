<?php

use skrtdev\Telegram\Message;
use skrtdev\NovaGram\Bot;

$Bot = new Bot("YOUR_BOT_TOKEN", [
    "json_payload" => false,        // json_payload: true for enable, false for disable
    "debug" => YOUR_USER_ID,        // your user_id
    "parse_mode" => 'HTML'          // you can use "Markdown" for Markdown
]);


    // function to check if a user is an admin of the chat
    function isAdmin($userId, $chatId) {
        global $Bot;
        $isAdmin = false;

        $chatMember = $Bot->getChatMember([
            "chat_id" => $chatId,
            "user_id" => $userId
        ]);
        if (in_array($chatMember->status, ["administrator", "creator"])) $isAdmin = true;

        return $isAdmin;
    }


    // function to check if a user is the creator of the chat
    function isCreator($userId, $chatId) {
        global $Bot;
        $isCreator = false;

        $chatMember = $Bot->getChatMember([
            "chat_id" => $chatId,
            "user_id" => $userId
        ]);
        if (in_array($chatMember->status, ["creator"])) $isCreator = true;

        return $isCreator;
    }
