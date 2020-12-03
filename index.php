<?php
use skrtdev\Telegram\Message;

header('Content-Type: application/json');

if (file_exists('vendor')) {
    require 'vendor/autoload.php';
}

//phar

else{
    if (!file_exists('novagram.phar')) {
        copy('http://gaetano.cf/novagram/phar.php', 'novagram.phar');
    }
    require_once 'novagram.phar';
}

$Bot = new \skrtdev\NovaGram\Bot("YOUR_BOT_TOKEN", [
    "json_payload" => true,
    "debug" => YOUR_USER_ID,    // your user_id
    "parse_mode" => 'HTML',     // you can use "Markdown" for Markdown
]);


$update = $Bot->update;                                                     // update
$message = $update->message;                                                // message
$text = $message->text;                                                     // text the message
$chat = $message->chat;                                                     // chat                         
$user = $message->from;                                                     // user
$useridReply = $message->reply_to_message->from->id;                        // user_id of user in reply
$usernameReply = $message->reply_to_message->from->username;                // username of user in reply
$username = $message->from->username;                                       // username
$replyToMessage = $message->reply_to_message;                               // reply to message
$messageid = $message->reply_to_message->message_id;                        // message_id of message in reply
$userid = $user->id;                                                        // user_id
$chatid = $chat->id;                                                        // chat_id
$firstNameReply = $message->reply_to_message->from->first_name;             // first_name of user in reply
$lastNameReply = $message->reply_to_message->from->last_name;               // last_name of user in reply
$userReply = $message->reply_to_message->from;                              // user reply


//commands


try {

    if(isset($message->text)){


        // simple command for sending a message
        if ($text === "/testBot") {
            $chat->sendMessage("testBot ok", true);
        }


        // command to delete the message in reply with json_payload enabled
        // "$replyToMessage !== null and $usernameReply !== null"  checks if is a reply
        if ($text === "/deleteMessage" and $replyToMessage !== null and $usernameReply !== null) {
            $replyToMessage->delete(null, true);
        }


        // command to change chat's title (if is a group/supergroup)
        // example of usage: "/changeTitle i choose this title" the title of the chat will be "i choose this title"
        if (strpos($text, "/changeTitle") === 0) {
            $ai = explode(" ", $text);
            unset($ai[0]);
            $ei = implode(" ", $ai);

            $Bot->setChatTitle([
                "chat_id" => $chatid,
                "title" => $ei
            ]);
        }


        // command to get Datacenter of user in reply
        if ($text === "/getUserDc") {
            $chat->sendMessage("@$usernameReply datacenter: " . $userReply->getDC());
        }


        // command to pin message in reply
        // "$replyToMessage !== null and $usernameReply !== null"  checks if is a reply
        if (($text === "/pinMessage") and ($replyToMessage !== null and $usernameReply !== null)) {
            $message->reply_to_message->pin();
        }


        // command to unpin the pinned message
        if ($text === "/unpinMessage") {
            $unpinChatMessage = $Bot->unpinChatMessage([
                "chat_id" => $chatid    // chat_id of the chat
            ]);

            // checks if there is a pinned message in the chat
            if ($unpinChatMessage != true) {
                $chat->sendMessage("there isn't any pinned message");
            }
        }


        // command to unpin all the pinned messages in a group/supergroup
        if ($text === "/unpinAllMessages") {
            if ($chat->pinned_message == false) {
                $chat->sendMessage("no pinned messages");
            } else {
                $unpinAllChatMessage = $Bot->unpinAllChatMessages([
                    "chat_id" => $chatid
                ]);
            }
        }


        // command to mute the user in reply
        if ($text == "/muteUser") {
            $restrict = $Bot->restrictChatMember([
                "chat_id" => $chatid,
                "user_id" => $useridReply,
                "can_send_messages" => 0
            ]);
            $chat->sendMessage("i muted the user in reply");
        }


        // command to unmute the user in reply
        if ($text == "/unmuteUser") {
            $Bot->restrictChatMember([
                "chat_id" => $chatid,
                "user_id" => $useridReply,
                "can_send_messages" => 1
            ]);
        }


        // command to make admin a user
        // example: "/promoteMember 1 1 1 1 1" 
        if ((strpos($text, "/promoteMember") === 0) and ($replyToMessage !== null and $usernameReply !== null)) {
            $ai = str_replace("admin ", "", $text);
            $ai = explode(" ", $text);

            $Bot->promoteChatMember([
                "chat_id" => $chatid,
                "user_id" => $useridReply,
                "can_change_info" => boolval($ai[0]),
                "can_delete_messages" => boolval($ai[1]),
                "can_restrict_members" => boolval($ai[2]),
                "can_pin_messages" => boolval($ai[3]),
                "can_promote_members" => boolval($ai[4])
            ]);

        }


        // command to demote an admin to user
        if ((strpos($text, "/demoteMember") === 0) and ($replyToMessage !== null and $usernameReply !== null)) {
            $Bot->promoteChatMember([
                "chat_id" => $chatid,
                "user_id" => $useridReply,
                "can_change_info" => false,
                "can_delete_messages" => false,
                "can_restrict_members" => false,
                "can_pin_messages" => false,
                "can_promote_members" => false
            ]);
        }


        // command to list admins of the chat
        if ($text == "/listAdmins") {
            $chatAdmins = $Bot->getChatAdministrators([
                "chat_id" => $chatid
            ]);

            $adminsar = [];
            foreach ($chatAdmins as $chatAdmin) {
                if ($chatAdmin === '[ChatMember]') continue;
                
                $adminsar[] = "<a href='t.me/{$chatAdmin->user->username}'>@" . ($chatAdmin->user->username) . "</a>\n";
            }

            $chat->sendMessage("group admins:\n" . implode("", $adminsar));
        }


        // command to check if a user is an admin
        if ($text == "/isAdmins?") {
            $chatMember = $Bot->getChatMember([
                "chat_id" => $chatid,
                "user_id" => $useridReply
            ]);

            $isAdmin = in_array($chatMember->status, ["administrator", "creator"]);

            if ($isAdmin) {
                $chat->sendMessage("@" . $usernameReply . " is an admin");
            } else {
                if (!in_array($chatMember->status, ["administrator", "creator"])) {

                    $chat->sendMessage("@" . $usernameReply . " isn't an admin");
                }
            }
        }


        // check lag (?)
        if ($text === "/checkLag") {
            $start = hrtime(true);
            $lagMex = $chat->sendMessage("lag...");
            $time_elapsed_secs = hrtime(true) - $start;
            $lagMex->editText("method sendMessage: " . $time_elapsed_secs / 1000000000, true);
        }


        // command to get user_id of user in reply
        if ($text === "/userId") {
            $chat->sendMessage("@$usernameReply id: <code>$useridReply</code>");
        }


        // command to send commands to your VM
        // use this if your VM is based on Linux
        // usage: /command command, example: "/command ls" 
        if (stripos($text, "/command") === 0) {
            $explode = explode(" ", $text);
            unset($explode[0]);
            $command = implode(" ", $explode);
            $output = shell_exec($command);
            if (isset($output)) $chat->sendMessage($output);
        }

    }


} catch (Throwable $e) {
    file_put_contents("logs.txt", $e . "\n", FILE_APPEND);
    $chat->sendMessage("<b>error code: " . $e->getCode() . "</b>\n\n<b>description: </b>$e");
}
