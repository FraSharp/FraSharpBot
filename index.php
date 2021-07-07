<?php

require("vendor/autoload.php");

use skrtdev\Telegram\Message;
use skrtdev\NovaGram\Bot;

require_once("config.php");

foreach ($modules as $module => $active) {
    if ($active) require_once($module);
}

$Bot = new Bot($TOKEN, [
    "json_payload" => true,
    "username" => $BOT_USERNAME,
    "debug" => $DEBUG_ID,
    "parse_mode" => 'HTML', // you can use "Markdown" for Markdown.
    "disable_web_page_preview" => true,
    "export_commands" => false,
    "async" => false,
    "threshold" => 50,
    "skip_old_updates" => true
]);

$Bot->onTextMessage(function (Message $message) {
    try {
        global $Bot;

        $text = $message?->text;
        $messageid = $message?->message_id;
        $chat = $message?->chat;
        $user = $message?->from;
        $useridReply = $message?->reply_to_message?->from?->id;
        $usernameReply = $message?->reply_to_message?->from?->username;
        $username = $message?->from?->username;
        $replyToMessage = $message?->reply_to_message;
        $replymessageid = $message?->reply_to_message?->message_id;
        $userid = $user?->id;
        $chatid = $chat?->id;
        $firstNameReply = $message?->reply_to_message?->from?->first_name;
        $lastNameReply = $message?->reply_to_message?->from?->last_name;
        $userReply = $message?->reply_to_message?->from;
        $firstName = $message?->from?->first_name;
        $lastName = $message?->from?->last_name;

        $mentionUserReply = "<a href='tg://user?id=$useridReply'>$firstNameReply $lastNameReply</a>";

        $conn = new mysqli("host", "user", "password", "frasharpbot") or $chat->sendMessage("not done");

        importDatabase("extras/db.sql") or $chat->sendMessage($conn->error);

        $gbanQuery = "SELECT * FROM `frasharpbot`.`banned` WHERE `id` = '$userid'";
        $gbanResult = $conn->query($gbanQuery);
        $gbanRow = $gbanResult->fetch_assoc();
        (isset($gbanRow['id']) == $userid) ? $userIsGbanned = true : $userIsGbanned = false;

        if ($text === "/start" or $text === "/start@FraSharpBot_Bot" ) {
            $message->reply("FraSharpBot v0.5-prealpha | alive");
        }


        if (str_contains($text, ":gban") and (isAllowed($userid) or isBotOwner($userid))) {
            if ($replyToMessage !== null and $usernameReply !== null) {
                if (isAllowed($useridReply) or isBotOwner($useridReply)) {
                    $gbanResponse = "can't ban $mentionUserReply, is immune";
                } else {
                    $actualGbanQuery = "INSERT INTO `frasharpbot`.`banned` (`id`) VALUES ('$useridReply')";
                    $actualGban = $conn->query($actualGbanQuery);

                    (str_contains($conn->error, "Duplicate")) ? $gbanErrorReason = "$mentionUserReply is already globally banned" : $gbanErrorReason = "unknown";

                    ($actualGban) ? $gbanResponse = "$mentionUserReply has been globally banned" : $gbanResponse = "failed, $gbanErrorReason";
                }

                $message->reply($gbanResponse);
            } else {
                if ($replyToMessage === null and $usernameReply === null) {
                    $explodeGbanText = explode(" ", $text);
                    $idToGban = $explodeGbanText[1];

                    if (isAllowed($idToGban) or isBotOwner($idToGban)) {
                    $gbanResponse = "can't ban the user, is immune";
                        } else {
                            $actualGbanQuery = "INSERT INTO `frasharpbot`.`banned` (`id`) VALUES ('$idToGban')";
                            $actualGban = $conn->query($actualGbanQuery);

                            (str_contains($conn->error, "Duplicate")) ? $gbanErrorReason = "the user is already globally banned" : $gbanErrorReason = "unknown";

                            $unbanUngban = $Bot->unbanChatMember([
                                "chat_id" => $chatid,
                                "user_id" => $useridReply
                            ]);
                            ($actualGban and $unbanUngban) ? $gbanResponse = "the user has been globally banned" : $gbanResponse = "failed, $gbanErrorReason";
                    }
                    $message->reply($gbanResponse);
                }
            }
        }


        if (str_contains($text, ":ungban") and (isBotSudo($userid))) {
            if ($replyToMessage !== null and $usernameReply !== null) {
                if (isAllowed($useridReply) or isBotOwner($useridReply)) {
                    $unGbanResponse = "can't ungban $mentionUserReply, is immune";
                } else {
                    $actualUnGbanQuery = "DELETE FROM `frasharpbot`.`banned` WHERE `id` = '$useridReply'";
                    $actualUnGban = $conn->query($actualUnGbanQuery);

                    (str_contains($conn->error, "Duplicate")) ? $unGbanErrorReason = "$mentionUserReply is already globally banned" : $unGbanResponse = "unknown";

                    $unbanUngban = $Bot->unbanChatMember([
                        "chat_id" => $chatid,
                        "user_id" => $useridReply
                    ]);
                    ($actualUnGban and $unbanUngban) ? $unGbanResponse = "$mentionUserReply has been globally unbanned" : $unGbanResponse = "";
                }

                $message->reply($unGbanResponse);
            } else {
                if ($replyToMessage === null and $usernameReply === null) {
                    $explodeUnGbanText = explode(" ", $text);
                    $idToUnGban = $explodeUnGbanText[1];

                    if (isAllowed($idToUnGban) or isBotOwner($idToUnGban)) {
                    $unGbanResponse = "can't ban the user, is immune";
                        } else {
                            $actualUnGbanQuery = "DELETE FROM `frasharpbot`.`banned` WHERE `id` = '$idToUnGban'";
                            $actualUnGban = $conn->query($actualUnGbanQuery);

                            (str_contains($conn->error, "Duplicate")) ? $unGbanErrorReason = "the user is already globally banned" : $unGbanErrorReason = "unknown";

                            ($actualUnGban) ? $unGbanResponse = "the user has been globally unbanned" : $unGbanResponse = "";
                    }
                    $message->reply($unGbanResponse);
                }
            }
        }


        if ($text and isGbanned($userid)) {
            if (isAdmin($userid, $chatid) === false) {
                $Bot->kickChatMember([
                    "chat_id" => $chatid,
                    "user_id" => $userid
                ]);
            }
        }



        if ($text) {
            $query = "SELECT * FROM frasharpbot.user WHERE user.id = '$userid'";
            $result = $conn->query($query);
            $row = $result->fetch_assoc();
            $firstName = $message->from->first_name;
            $lastName = $message->from->last_name;
            if ($row['username'] == $username and $row['name'] == $firstName and $row['surname'] == $lastName) { }
            else {
                if ($row['id'] != $userid) {
                    $insert = "INSERT INTO frasharpbot.user (`id`, `username`, `name`, `surname`) VALUES ('$userid', '$username', '$firstName', '$lastName')";
                    $inserdb = $conn->query($insert);
                }
            }
            if ($row['username'] != $username or $row['name'] != $firstName or $row['surname'] != $lastName) {
                $deleteRecord = "DELETE FROM user WHERE id = '$userid'";
                $delete = $conn->query($deleteRecord);
                $insert = "INSERT INTO `user` (`id`, `username`, `name`, `surname`) VALUES ('$userid', '$username', '$firstName', '$lastName')";
                $inserdb = $conn->query($insert);
            }
        }


        if ($text === "userlist" and $userid == $OWNER_ID) {
            $usernameare = [];
            $select = "SELECT `id`, `username`, `name`, `surname` FROM `frasharpbot`.`user`";
            $result = $conn->query($select);
            if ($result->num_rows < 1) {
                $chat->sendMessage("can't run this command <code>$text</code>, 0 users in this table");
            }
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if ($row['name'] == '' && $row['surname'] == '') {
                        $usernameare[] = "username: @" . $row['username'] . "\nuser_id: " . $row['id'] . "\n\n";
                    } else {
                        if ($row['name'] == '') {
                            $usernameare[] = "username: @" . $row['username'] . "\nuser_id: " . $row['id'] . "\nsurname: " . $row['surname'] . "\n\n";
                        } else {
                            if ($row['surname'] == '') {
                                $usernameare[] = "username: @" . $row['username'] . "\nuser_id: " . $row['id'] . "\nname: " . $row['name'] . "\n\n";
                            } else {
                                $usernameare[] = "username: @" . $row['username'] . "\nuser_id: " . $row['id'] . "\nname: " . $row['name'] . "\nsurname: " . $row['surname'] . "\n\n";
                            }
                        }
                    }
                }
                $implodedUsernameAre = implode("", $usernameare);
                $start = hrtime(true);
                $userlist = hastebin($implodedUsernameAre);
                $mex = $chat->sendMessage("users in <code><b>user</b></code> table: " . print_r($userlist, true));
                $time_elapsed_secs = hrtime(true) - $start;
                $mex->editText("users in <code><b>user</b></code> table: " . print_r($userlist, true) . "\ntook: " . round($time_elapsed_secs / 1000000, 2) . " ms");

            }
        }
        
        // simple command for sending a message
        if ($text === "/testBot") $chat->sendMessage("testBot ok", true);


        // command to delete the message in reply with json_payload enabled
        // "$replyToMessage !== null and $usernameReply !== null"  checks if is a reply
        if ($text === ":del" and $replyToMessage !== null and $usernameReply !== null) {
            if ($userid == $useridReply) {
                $message->delete();
                $replyToMessage->delete(null, true);
            } else {
                if (hasRight($userid, $chatid, "can_delete_messages")) {
                    $message->delete();
                    $replyToMessage->delete(null, true);
                } else {
                    $message->reply("can't delete that message");
                }
            }
        }

        // if (stripos($text, ".") === 0) $message->delete(true, true);


        // command to change chat's title (if is a group/supergroup)
        // example of usage: "/changeTitle i choose this title" the title of the chat will be "i choose this title"
        if (strpos($text, "/changeTitle") === 0) {
            $ai = explode(" ", $text);
            if (!$ai[1]) {
                $message->reply("Insert a valid chat title");
                die;
            }
            unset($ai[0]);
            $ei = implode(" ", $ai);

            $chat->setTitle($ei);
        }


        // command to get Datacenter of user in reply
        if ($text === ":getdc") {
            if ($replyToMessage === null and $usernameReply === null) {
                $message->reply("Reply to a message first");
            }

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
        if (($text === ":pin") and ($replyToMessage !== null and $usernameReply !== null) and isAdmin($userid, $chatid)) $message->reply_to_message->pin();


        // command to unpin the pinned message
        if ($text === ":unpin") {
            if (!isAdmin($userid, $chatid)) {
                $message->reply("You aren't an admin of this chat");
                die;
            }

            $unpinChatMessage = $chat->unpinMessage();
            // checks if there is a pinned message in the chat
            if ($unpinChatMessage != true) $chat->sendMessage("there isn't any pinned message");
        }


        // command to unpin all the pinned messages in a group/supergroup
        if ($text === ":unpinall") {
            if (!isAdmin($userid, $chatid)) {
                $message->reply("You aren't an admin of this chat");
                die;
            }

            if ($chat->pinned_message == false) $chat->sendMessage("no pinned messages");
            else $chat->unpinAllMessages();

            $chat->sendMessage(print_r($chat->pinned_message, true));
        }


        // command to mute the user in reply
        if ($text == ":mute") {
            if ($replyToMessage === null and $usernameReply === null) {
                $message->reply("Reply to a message first");
            } else {
                if (!isAdmin($userid, $chatid)) {
                    $message->reply("You aren't an admin of this chat");
                } else {

                    $muteMember = muteMember($chatid, $useridReply);
                    if ($muteMember) $message->reply("@$usernameReply is now muted forever");
                }
            }
        }


        // command to unmute the user in reply
        if ($text == ":unmute") {
            if ($replyToMessage === null and $usernameReply === null) {
                $message->reply("Reply to a message first");
            } else {
                if (!isAdmin($userid, $chatid)) {
                    $message->reply("You aren't an admin of this chat");
                } else {

                    $unmuteMember = unmuteMember($chatid, $useridReply);
                    if ($unmuteMember) $message->reply("@$usernameReply is now unmuted");
                }
            }
        }


        if (strpos($text, ":title") === 0 and (isAllowed($userid) or isBotOwner($userid))) {
            $titleExplode = explode(" ", $text);
            unset($titleExplode[0]);
            $title = implode(" ", $titleExplode);
            try {
                if ($Bot->setChatAdministratorCustomTitle($chatid, $useridReply, $title)) $message->reply("$usernameReply now has $title and admin title");
            } catch (Throwable $e) {
                $notEnoughRights = "not enough rights";
                if (strpos($e, $notEnoughRights))
                    $message->reply("i can't change $usernameReply title: someone else promoted them");
            }
        }


        // command to make admin a user
        if ($text === ":promote") {
            if ($replyToMessage === null and $usernameReply === null) {
                $message->reply("Reply to a message first");
            } else {
                if (!isAdmin($userid, $chatid)) {
                    $message->reply("You aren't an admin of this chat");
                } else {
                    if (isAdmin($userid, $chatid) and $userid === $useridReply) {
                        $message->reply("You can't promote yourself");
                    } else {
                        if (isAdmin($userid, $chatid) and hasRight($userid, $chatid, "can_promote_members") == false) $message->reply("you can't promote the user, you're missing the required right: <pre><i>can_promote_members</i></pre>");
                            else {
                                if (isCreator($userid, $chatid)) {
                                    $promote = promoteMember($chatid, $useridReply);
                                    if ($promote) $message->reply("@$usernameReply is now admin");
                                } else {
                                    $promote = promoteMember($chatid, $useridReply);
                                    if ($promote) $message->reply("@$usernameReply is now admin");
                                }
                            }
                        }
                    }
                }
            }


        // command to make admin a user
        // example: "/customPromote 1 1 1 1 1"
        if ((strpos($text, "/customPromote") === 0)) {
            if ($replyToMessage === null and $usernameReply === null) {
                $message->reply("Reply to a message first");
            } else {
                if (!isAdmin($userid, $chatid)) {
                    $message->reply("You aren't an admin of this chat");
                } else {
                    if (isAdmin($userid, $chatid) and $userid === $useridReply) {
                        $message->reply("You can't promote yourself");
                    } else {
                        if (!isAdmin($useridReply, $chatid)) {
                            $message->reply("@$usernameReply is now an admin");
                        }

                        $promote = customPromote($chatid, $useridReply, $text);
                        if ($promote) $message->reply("@$usernameReply is now admin");
                    }
                }
            }
        }


        // command to demote an admin to user
        if ((strpos($text, ":demote") === 0)) {
            if ($replyToMessage === null and $usernameReply === null) {
                $message->reply("Reply to a message first");
            } else {
                if (!isAdmin($userid, $chatid)) {
                    $message->reply("You aren't an admin of this chat");
                } else {
                    if (isAdmin($userid, $chatid) and $userid === $useridReply) {
                        $message->reply("You can't demote yourself");
                    } else {
                        if (!isAdmin($useridReply, $chatid)) {
                            $message->reply("@$usernameReply is not an admin");
                        }

                        $demote = demoteMember($chatid, $useridReply);
                        if ($demote) $message->reply("@$usernameReply isn't an admin anymore");
                    }
                }
            }
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
            if ($replyToMessage !== null and $usernameReply !== null) {
                if (isAdmin($useridReply, $chatid)) {
                    $message->reply("@" . $usernameReply . " is an admin");
                }
            } else {
                if (!isAdmin($useridReply, $chatid)) {
                    $message->reply("@" . $usernameReply . " isn't an admin");
                }
            }
        }


        // check lag (?)
        if ($text === "/checkLag" and (isAllowed($userid) or isBotOwner($userid))) {
            $start = hrtime(true);
            $lagMex = $chat->sendMessage("lag...");
            $time_elapsed_secs = hrtime(true) - $start;
            $lagMex->editText("method sendMessage: " . round($time_elapsed_secs / 1000000, 2) . " ms", true);
        }


        // command to get user_id of user in reply
        if ($text === "/userId") {
            //$chat->sendMessage(print_r($user, true) . " $user->id");
            if ($replyToMessage == null and $usernameReply == null) $chat->sendMessage("@" . $user->username . "'s id: <code>" . $userid . "</code>");
            else {
                ($usernameReply == "" or $usernameReply == null) ? $usernameReply = "$firstNameReply $lastNameReply" : $usernameReply = "@$usernameReply";
                $chat->sendMessage("$usernameReply 's id: <code>$useridReply</code>");
            }
        }

        if ($text == ":sendsticker") {
            $chat->sendSticker($message->reply_to_message->sticker->file_id);
        }

        // command to get chat_id of the chat
            if ($text === "/chatId") {
            $chat->sendMessage("<code>$chat->title</code> 's id: <code>$chat->id</code>");
        }


        // command to send commands to your VM
        // use this if your VM is based on Linux
        // use if hosting provider allows to do the "shell_exec()" command
        // usage: /command command, example: "/command ls"
        if (stripos($text, ":sh") === 0) {
            if (isBotOwner($userid)) {
                $explode = explode(" ", $text);
                unset($explode[0]);
                $command = implode(" ", $explode);

                if (str_contains($command, "rm ")) {
                    $message->reply("can't");
                } else {

                    if (PHP_OS_FAMILY == "Windows")                             // use powershell?
                    $response = shell_exec("powershell; " . $command);
                    else $response = shell_exec($command . " 2<&1");

                    if (str_contains($response, "sh:")) {
                        $message->reply("$command, not found");
                    } else
                    if (isset($response)) $message->reply("<pre><b>francesco@MBP-di-Francesco</b>:<b>~ </b></pre>" . $response);
                }
            }
        }

        
        if (stripos($text, "random") === 0 and (isAllowed($userid) or isBotOwner($userid))) {

            $choices = str_replace("random ", "", $text);
            $choices_array = explode(" ", $choices);

            if (count($choices_array) < 2) { $message->reply("can't choose a random value, only given " . count($choices_array) . " value" ); } else {

                for ($i = 0; $i < count($choices_array) + 10000; $i++) {
                    if ($choices_array[$i] == "" or $choices_array[$i] == " ") {
                        unset($choices_array[$i]);
                    }
                }

                $i = random_int(0, count($choices_array) - 1);
                $final_choice = $choices_array[$i];
                $message->reply("random choice: $final_choice");
            }
        }

        
        if ($text === ":speedtest" and (isAllowed($userid) or isBotOwner($userid))) {
            $response = shell_exec("curl -s https://raw.githubusercontent.com/sivel/speedtest-cli/master/speedtest.py | python3 -");
            if (isset($response)) $message->reply($response);
        }


        if ($text === ":purget" and (isAllowed($userid) or isBotOwner($userid))) {
            $messages = [];
            for ($i = $message->message_id - 1; $i < $message->reply_to_message->message_id; $i--) {
                array_push($messages, $i);
                if (count($messages) > 50) {
                    foreach ($messages as $messageToDelete) $chat->deleteMessage($messageToDelete);
                    $messages = [];
                }
            }
            try {
                foreach ($messages as $messageToDelete) if (!$chat->deleteMessage($messageToDelete)) continue;
            } catch(Throwable $e) {}
        }


        if ($text === ":ban") {
            if (!isAdmin($userid, $chatid)) $message->reply("you're not an admin");
            else {
                if (isAdmin($userid, $chatid) == true and hasRight($userid, $chatid, "can_restrict_members") == false)
                    $message->reply("you can't ban the user, you're missing the required right: <pre><i>can_restrict_members</i></pre>");
                else {
                    if (((isAdmin($userid, $chatid) and hasRight($userid, $chatid, "can_restrict_members") and $useridReply == $userid) or (isAdmin($userid, $chatid) and hasRight($userid, $chatid, "can_restrict_members") and isAdmin($useridReply, $chatid)))) {
                        $message->reply("i can't ban an admin");
                    } else {
                        if (banMember($chatid, $useridReply, true))
                            $message->reply("<a href='tg://user?id=$useridReply'>$firstNameReply $lastNameReply</a> has been banned");
                    }
                }
            }
        }


        if ($text === ":info") {
            if ($replyToMessage === null and $usernameReply === null) {
                getUserInfo($chatid, $userid, $username, $firstName, $lastName);
            } else  {
                getUserInfo($chatid, $useridReply, $usernameReply, $firstNameReply, $lastNameReply);
            }
        }


        if ($text === ":leave" and $userid == 1186205613) {
            $Bot->leaveChat($chatid);
        }


        if ($text === ":refresh" and (isAllowed($userid) or isBotOwner($userid))) {
            $refresh = $message->reply("refreshing...");

            $mysqliLogs = $conn->refresh(MYSQLI_REFRESH_LOG);
            $mysqliTables = $conn->refresh(MYSQLI_REFRESH_TABLES);


            $refreshLog = $mysqliLogs ? "✅" : "❌";
            $refreshTable = $mysqliTables ? "✅" : "❌";

            ($mysqliLogs) ? $checkLog = true : $checkLog = false;
            ($mysqliTables) ? $checkTable = true : $checkTable = false;

            $opcache_status = opcache_get_status();

            if (isset($opcache_status["opcache_enabled"]) && !$opcache_status["opcache_enabled"]) {
                $opcacheStatus = "❓ opcache empty";
            } else {
                $refreshCache = $checkCache = opcache_reset() ? "✅" : "❌";
            }

            $refreshStatus = (
                ($checkLog === true and $checkTable === true) ? "passed" :
                (((!$checkLog and $checkTable) or ($checkLog and !$checkTable)) ? "partially passed" : "failed")
            );
            sleep(1);
             
            $refreshText = "refresh status: $refreshStatus";
            $refreshText .= "\n\t$refreshLog database logs";
            $refreshText .= "\n\t$refreshTable database tables";
            (!isset($opcacheStatus)) ? $refreshText .= "\n\t$refreshCache caches" : $refreshText .= "\n\t$opcacheStatus";
            $refresh->editText($refreshText);
        }

        if (str_contains($text, "cowsay") and isBotOwner($userid)) {
            $cowsayText = str_ireplace("cowsay ", "", $text);
            cowsay($chatid, $cowsayText);
        }

        if (str_contains($text, ":save") and isBotOwner($userid)) {
            $explodeSaveNote = explode(" ", $text);
            unset($explodeSaveNote[0]);
            $noteName = $explodeSaveNote[1];
            unset($explodeSaveNote[1]);
            $noteText = implode(" ", $explodeSaveNote);

            if ($saveNote = saveNote($chatid, $noteName, $noteText)) {
                $message->reply($saveNote);
            }
        }

        if (str_starts_with($text, ":get")) {
            $noteToGet = str_ireplace(":get ", "", $text);

            if ($getNote = getNote($chatid, $noteToGet)) {
                $message->reply($getNote);
            }
        }

        if (str_starts_with($text, ":remove")) {
            $noteToGet = str_ireplace(":remove ", "", $text);

            if ($removeNote = removeNote($chatid, $noteToGet)) {
                $message->reply($removeNote);
            }
        }

        if (str_starts_with($text, ":kick")) {
            $useridToKick = str_ireplace(":kick ", "", $text);
            if ($chat->type == "group") {
                $message->reply("this can't be used in normal groups");
            } else {
                if ((isBotSudo($userid) or hasRight($userid, $chatid, "can_restrict_members")) and $replyToMessage == null and $usernameReply == null) {
                    kickMember($chatid, $useridToKick);
                    $message->reply($useridToKick . " was kicked");
                } else {
                    if ((isBotSudo($userid) or hasRight($userid, $chatid, "can_restrict_members"))) {
                        kickMember($chatid, $useridReply);
                        $message->reply($mentionUserReply . " was kicked");
                    }
                }
            }
        }

        if ($text === "warn") {
            warnMember($chatid, $useridReply);
        }

    } catch (Throwable $e) {
        $Bot->sendMessage($DEBUG_ID, "<b>error code: " . $e->getCode() . "</b>\n\n<b>description: </b>$e");
    }
});

try {
    $Bot->start();
} catch (\skrtdev\NovaGram\Exception $e) {
}
