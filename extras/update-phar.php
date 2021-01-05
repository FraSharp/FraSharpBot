<?php
/*

    this is an automated method for updating the Bot
    in case you're using phar.

    Requirements:
    -- using phar
    -- obviously not using composer :|

*/

require_once "functions.php";
require_once "check.php";
global $Bot;

use skrtdev\Telegram\Message;

$Bot->onCommand("updatePhar", function (Message $message) {
    $chat = $message->chat;
    $message->delete(null, true);  // "null, true" means that you're using json_payload for deleting the message
    unlink(__DIR__.'/../novagram.phar');  // change "novagram.phar" with the name of your .phar file (if you have a different name) and change "../FraSharpBot" with your directory
    $chat->sendMessage("I've updated the bot to the latest version of <a href='github.com/skrtdev/novagram'>Novagram</a>");
});
