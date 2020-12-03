<?php
/*

    this is an automated method for updating the Bot
    in case you're using phar.

    Requirements:
    -- using phar
    -- obviously not using composer :|

*/

$Bot->onCommand("updatePhar", function ($message) {
    $message->delete(null, true);  // "null, true" means that you're using json_payload for deleting the message
    unlink("novagram.phar");  // change "novagram.phar" with the name of your .phar file (if you have a different name)
    $message->reply("I've updated the bot to the latest version");
});
