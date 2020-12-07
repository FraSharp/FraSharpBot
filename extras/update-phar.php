<?php
/*

    this is an automated method for updating the Bot
    in case you're using phar.

    Requirements:
    -- using phar
    -- obviously not using composer :|

*/

use skrtdev\Telegram\Message;
use skrtdev\NovaGram\Bot;

$Bot = new Bot("YOUR_BOT_TOKEN", [
    "json_payload" => false,        // json_payload: true for enable, false for disable
    "debug" => YOUR_USER_ID,        // the chat_id/user_id for sending errors
    "parse_mode" => 'HTML'          // you can use "Markdown" for Markdown
]);

$Bot->onTextMessage(function (Message $message) {
    $text = $message->text;
    $chat = $message->chat;

    if ($text === "/updatePhar") {
        $message->delete(null, true);  // "null, true" means that you're using json_payload for deleting the message
        unlink("/httpdocs/FraSharpBot/novagram.phar");  // change "novagram.phar" with the name of your .phar file (if you have a different name)
        $chat->sendMessage("I've updated the bot to the latest version of <a href='github.com/skrtdev/novagram'>Novagram</a>");
    }
});