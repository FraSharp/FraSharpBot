<?php
if (file_exists('vendor')) {
    require_once 'vendor/autoload.php';
}
else{ //phar
    if (!file_exists('novagram.phar')) {
        copy('http://gaetano.cf/novagram/phar.php', 'novagram.phar');
    }
    require_once 'novagram.phar';
}

use skrtdev\Telegram\Message;
use skrtdev\NovaGram\Bot;

$Bot = new Bot("YOUR_BOT_TOKEN", [
    "json_payload" => false,        // json_payload: true for enable, false for disable
    "debug" => YOUR_ID,             // your user_id
    "parse_mode" => 'HTML'          // you can use "Markdown" for Markdown
]);

// add extra files here
require_once "extras/update-phar.php";
require_once "extras/functions.php";
require_once "extras/check.php";
// end extra files


// commands
$Bot->onTextMessage(function (Message $message) {
    try {
        global $Bot;

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


        // simple command for sending a message
        if ($text === "/testBot") $chat->sendMessage("testBot ok", true);


        // command to delete the message in reply with json_payload enabled
        // "$replyToMessage !== null and $usernameReply !== null"  checks if is a reply
        if ($text === "/deleteMessage" and $replyToMessage !== null and $usernameReply !== null) {
            $message->delete();
            $replyToMessage->delete(null, true);
        }


        // command to change chat's title (if is a group/supergroup)
        // example of usage: "/changeTitle i choose this title" the title of the chat will be "i choose this title"
        if (strpos($text, "/changeTitle") === 0) {
            $ai = explode(" ", $text);
            if (!$ai[1]) { $message->reply("Insert a valid chat title"); die; }
            unset($ai[0]);
            $ei = implode(" ", $ai);

            $chat->setTitle($ei);
        }


        // command to get Datacenter of user in reply
        if ($text === "/getUserDc") {
            if ($replyToMessage === null and $usernameReply === null) { $message->reply("Reply to a message first"); die; }

            $profilePhotos = $Bot->getUserProfilePhotos([
                "user_id" => $useridReply
            ]);
            if ($profilePhotos->total_count > 0 and $message->reply_to_message->from->username != "") {
                $chat->sendMessage("@" . $usernameReply . " 's datacenter: " . $userReply->getDC());
            } else {
                if ($profilePhotos->total_count < 1) {
                    $chat->sendMessage("Can't get @$usernameReply 's datacenter, set a profile picture");
                } else {
                    if ($usernameReply == null) {
                        $chat->sendMessage("Can't get @$usernameReply 's datacenter, set an username");
                    }
                }
            }
        }


        // command to pin message in reply
        // "$replyToMessage !== null and $usernameReply !== null"  checks if is a reply
        if (($text === "/pinMessage") and ($replyToMessage !== null and $usernameReply !== null) and isAdmin($userid, $chatid)) $message->reply_to_message->pin();


        // command to unpin the pinned message
        if ($text === "/unpinMessage") {
            $unpinChatMessage = $chat->unpinMessage();
            // checks if there is a pinned message in the chat
            if ($unpinChatMessage != true) $chat->sendMessage("there isn't any pinned message");
        }


        // command to unpin all the pinned messages in a group/supergroup
        if ($text === "/unpinAllMessages") {
            if ($chat->pinned_message == false) $chat->sendMessage("no pinned messages");
            else $chat->unpinAllMessages();
        }


        // command to mute the user in reply
        if ($text == "/muteUser") {
            $Bot->restrictChatMember([
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


        // command to change custom admin's title
        // usage: /changeAdminTitle customTitle (can use the spaces too)
        if (strpos($text, "/changeAdminTitle") === 0) {
            if ($replyToMessage === null and $usernameReply === null) { $message->reply("Reply to a message first"); die; }
            if (!isAdmin($userid, $chatid)) { $message->reply("You aren't an admin of this chat"); die; }
            $title = explode(" ", $text);
            unset($title[0]);
            $totalChars = "";
            foreach ($title as $strings) {
                $totalChars .= $strings;
                if (strlen($totalChars) > 16) {
                    $message->reply("The admin's custom title can't be more than 16 characters");
                    die;
                }
            }

            $changeAdminTitle = changeAdminTitle($chatid, $useridReply, $text);
            if (!$changeAdminTitle) $message->reply("I haven't been able to change admin title");
        }


        // command to make admin a user
        if ($text === "/promoteMember") {
            if ($replyToMessage === null and $usernameReply === null) { $message->reply("Reply to a message first"); die; }
            if (!isAdmin($userid, $chatid)) { $message->reply("You aren't an admin of this chat"); die; }
            if (isAdmin($userid, $chatid) and $userid === $useridReply) { $message->reply("You are already an admin"); die; }
            if (isAdmin($useridReply, $chatid)) { $message->reply("@$usernameReply is already an admin"); die; }

            $promote = promoteMember($chatid, $useridReply);

            if ($promote) $message->reply("@$usernameReply is now admin");
        }


        // command to make admin a user
        // example: "/customPromote 1 1 1 1 1"
        if ((strpos($text, "/customPromote") === 0)) {
            if ($replyToMessage === null and $usernameReply === null) { $message->reply("Reply to a message first"); die; }
            if (!isAdmin($userid, $chatid)) { $message->reply("You aren't an admin of this chat"); die; }
            if (isAdmin($userid, $chatid) and $userid === $useridReply) { $message->reply("You can't promote yourself"); die; }
            if (isAdmin($useridReply, $chatid)) { $message->reply("@$usernameReply is already an admin"); die; }

            $promote = customPromote($chatid, $useridReply, $text);

            if ($promote) $message->reply("@$usernameReply is now admin");
        }


        // command to demote an admin to user
        if ((strpos($text, "/demoteMember") === 0)) {
            if ($replyToMessage === null and $usernameReply === null) { $message->reply("Reply to a message first"); die; }
            if (!isAdmin($userid, $chatid)) { $message->reply("You aren't an admin of this chat"); die; }
            if (isAdmin($userid, $chatid) and $userid === $useridReply) { $message->reply("You can't demote yourself"); die; }
            if (!isAdmin($useridReply, $chatid)) { $message->reply("@$usernameReply is not an admin"); die; }

            $demote = demoteMember($chatid, $useridReply);

            if ($demote) $message->reply("@$usernameReply isn't an admin anymore");
        }


        // command to list admins of the chat
        if ($text === "/listAdmins" or $text === "/list_admins" or $text === "/list_admins@FraSharp_Bot") {
            $chatAdmins = $chat->getAdministrators($chatid);

            $adminsar = [];
            foreach ($chatAdmins as $chatAdmin) {
                if ($chatAdmin === '[ChatMember]') continue;
                $usernameAdmin = $chatAdmin->user->username;
                if ($usernameAdmin == "") $usernameAdmin = "no username"; else
                if ($usernameAdmin != "") $usernameAdmin = "@" . $chatAdmin->user->username;
                $adminsar[] = "\n<a href='tg://user?id={$chatAdmin->user->id}'>" . ($chatAdmin->user->first_name) . ($chatAdmin->user->last_name) . "</a> | " . ($usernameAdmin) . "\n";
            }

            $chat->sendMessage("group admins:\n" . implode("", $adminsar));
        }


        // command to check if a user is an admin
        if ($text == "/isAdmin?") {
            $chatMember = $chat->getMember($useridReply);

            $isAdmin = in_array($chatMember->status, ["administrator", "creator"]);

            if ($isAdmin) $message->reply("@" . $usernameReply . " is an admin");
            else {
                if (!in_array($chatMember->status, ["administrator", "creator"])) $message->reply("@" . $usernameReply . " isn't an admin");
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
            $chat->sendMessage("@$usernameReply 's id: <code>$useridReply</code>");
        }


        // command to get chat_id of the chat
        if ($text === "/chatId") {
            $chat->sendMessage("<code>$chat->title</code> 's id: <code>$chat->id</code>");
        }


        // command to send commands to your VM
        // use this if your VM is based on Linux
        // use if hosting provider allows to do the "shell_exec()" command
        // usage: /command command, example: "/command ls"
        if (stripos($text, "/command") === 0) {
            $explode = explode(" ", $text);
            unset($explode[0]);
            $command = implode(" ", $explode);
            $response = shell_exec($command);
            if (isset($response)) $chat->sendMessage($response);
        }


    } catch (Throwable $e) {
        $chat->sendMessage("<b>error code: " . $e->getCode() . "</b>\n\n<b>description: </b>$e");
    }

});
