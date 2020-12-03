/* 

    this is an automated method for updating the Bot
    in case you're using phar.

    Requirements:
    -- Linux VM (for deleting the old "novagram.phar" file)
    -- Hosting allows to do the "shell_exec()" command
    -- using phar
    -- obviously not using composer :|

*/

if ($text === "/updatePhar") 
{
    $message->delete(null, true);  // "null, true" means that you're using json_payload for deleting the message
    $deletePhar = shell_exec('rm novagram.phar');  // change "novagram.phar" with the name of your .phar file (if you have a different name)
    $chat->sendMessage("I've updated the bot to the latest version");
}
