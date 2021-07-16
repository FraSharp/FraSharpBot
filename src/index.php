<?php
require("vendor/autoload.php");
use skrtdev\NovaGram\Bot;
use skrtdev\Telegram\Chat;
use skrtdev\Telegram\Message;
use skrtdev\Telegram\User;

require_once("config.php");
require_once("functions.php");
require_once("info/info.php");

$Bot = new Bot(getenv("token"), [
    "command_prefixes" => [':'],
    "skip_old_updates" => true,
    "threshold" => 50,
    "parse_mode" => "HTML",
    "database" => [
        "dbname" => $dbname,
        "dbuser" => $dbuser,
        "dbpass" => $dbpass
    ]
]);

$Bot->onCommand('start', function (Message $message) {
    $message->reply("FraSharpBot v0.1-beta | alive");
});

$Bot->onCommand('lag', function (Message $message) {
        $start = hrtime(true);
        $lagMex = $message->chat->sendMessage("lag...");
        $time_elapsed_secs = hrtime(true) - $start;
        $lagMex->editText("elapsed time: " . round($time_elapsed_secs / 1000000, 2) . " ms", true);
});

$Bot->onCommand('info', function (Message $message) {
    $replyToMessage = $message?->reply_to_message;
    $usernameReply = $message?->reply_to_message?->from?->username;
    $firstNameReply = $message?->reply_to_message?->from?->first_name;
    $lastNameReply = $message?->reply_to_message?->from?->last_name;
    $firstName = $message?->from?->first_name;
    $lastName = $message?->from?->last_name;
    $username = $message?->from?->username;
    $chatid = $message?->chat?->id;
    $userid = $message?->from?->id;
    $useridReply = $message?->reply_to_message?->from?->id;

    $conn = new PDO("mysql:host=localhost;dbname=" . getenv("dbname"), getenv("dbuser"), getenv("dbpass"));

    if (str_starts_with($message->text, ":info ")) {
        $uname = str_ireplace(":info @", "", $message->text);
        $idk = $conn->query("select * from frasharpbot.user where user.username = '$uname'");
        $user = $idk->fetch(PDO::FETCH_ASSOC);

        try {
            getUserInfo($chatid, $user["user_id"], $user["username"], $user["firstname"], $user["lastname"]);
        } catch (\skrtdev\Telegram\BadRequestException $e) {
            if (str_contains($e, "invalid user_id specified")) $message->reply("user not present in database");
        }

    } else {
        if (is_null($replyToMessage)) {
            getUserInfo($chatid, $userid, $username, $firstName, $lastName);
        } else {
            getUserInfo($chatid, $useridReply, $usernameReply, $firstNameReply, $lastNameReply);
        }
    }

});

$Bot->onMessage(function (Message $message) {
    if (str_starts_with($message->text, ":sh")) {
        if (isBotOwner($message?->from?->id)) {
            $explode = explode(" ", $message->text);
            unset($explode[0]);
            $command = implode(" ", $explode);

            if (str_contains($command, "rm ")) {
                $message->reply("you can only delete files manually");
            } else {

                if (PHP_OS_FAMILY == "Windows")
                    $response = shell_exec("powershell; " . $command);
                else $response = shell_exec($command . " 2<&1");

                if (str_contains($response, "sh:")) {
                    $message->reply("$command, not found");
                } else
                    if (isset($response)) $message->reply("<pre><b>francesco@MBP-di-Francesco</b>:<b>~ </b></pre>" . $response);
            }
        }
    }
});

$Bot->onMessage(function (Message $message) {
    $firstName = $message?->from?->first_name;
    $lastName = $message?->from?->last_name;
    $username = $message?->from?->username;
    $userid = $message?->from?->id;

    $conn = new PDO("mysql:host=localhost;dbname=" . getenv("dbname"), getenv("dbuser"), getenv("dbpass"));
    $result = $conn->query("SELECT * FROM frasharpbot.user WHERE user.user_id = '$userid'");
    $row = $result->fetch(PDO::FETCH_ASSOC);

    try {
        if (isset($row['username']) === isset($username) and isset($row['firstname']) == isset($firstName) and isset($row['lastname']) === isset($lastName)) {
        } else {
            if (isset($row['id']) != isset($userid)) {
                $insert = "INSERT INTO frasharpbot.user (`user_id`, `username`, `firstname`, `lastname`) VALUES ('$userid', '$username', '$firstName', '$lastName')";
                $conn->query($insert);
            }
        }
        if ($row['username'] !== $username or $row['firstname'] != $firstName or $row['lastname'] != $lastName) {
            $deleteRecord = "DELETE FROM frasharpbot.user WHERE user.user_id = '$userid'";
            $conn->query($deleteRecord);
            $insert = "INSERT INTO frasharpbot.user (`user_id`, `username`, `firstname`, `lastname`) VALUES ('$userid', '$username', '$firstName', '$lastName')";
            $conn->query($insert);
        }
    } catch (PDOException) {}
});

$Bot->onMessage(function (Message $message) {
    $userid = $message?->from?->id;
    $conn = new mysqli("localhost", getenv("dbuser"), getenv("dbpass"), getenv("dbname"));
    if ($message->text === ":refresh" and (isAllowed($userid) or isBotOwner($userid))) {
        $refresh = $message->reply("refreshing...");

        $mysqliLogs = $conn->refresh(MYSQLI_REFRESH_LOG);
        $mysqliTables = $conn->refresh(MYSQLI_REFRESH_TABLES);


        $refreshLog = $mysqliLogs ? "✅" : "❌";
        $refreshTable = $mysqliTables ? "✅" : "❌";

        ($mysqliLogs) ? $checkLog = true : $checkLog = false;
        ($mysqliTables) ? $checkTable = true : $checkTable = false;

        $opcache_status = opcache_get_status();

        if (isset($opcache_status["opcache_enabled"]) and !$opcache_status["opcache_enabled"]) {
            $opcacheStatus = "❓ opcache empty";
        } else {
            $refreshCache = opcache_reset() ? "✅" : "❌";
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
});

$Bot->onCommand("kick", function (Message $message) {
    $firstNameReply = $message?->reply_to_message?->from?->first_name;
    $lastNameReply = $message?->reply_to_message?->from?->last_name;
    $replyToMessage = $message?->reply_to_message;
    $usernameReply = $message?->reply_to_message?->from?->username;
    $chatid = $message?->chat?->id;
    $userid = $message?->from?->id;
    $useridReply = $message?->reply_to_message?->from?->id;
    $chat = $message?->chat;
    $mentionUserReply = "<a href='tg://user?id=$useridReply'>$firstNameReply $lastNameReply</a>";

    if (str_starts_with($message->text, ":kick")) {
        try {
            $useridToKick = str_ireplace(":kick ", "", $message->text);
            if ($chat->type == "group") {
                $message->reply("this can't be used in normal groups");
            } else {
                if ((isBotSudo($userid) or hasRight($userid, $chatid, "can_restrict_members")) and is_null($replyToMessage) and is_null($usernameReply)) {
                    kickMember($chatid, $useridToKick);
                    $message->reply($useridToKick . " was kicked");
                } else {
                    if ((isBotSudo($userid) or hasRight($userid, $chatid, "can_restrict_members"))) {
                        kickMember($chatid, $useridReply);
                        $message->reply($mentionUserReply . " was kicked");
                    } else {
                        if (isAdmin($useridReply, $chatid) and (isBotSudo($userid) or hasRight($userid, $chatid, "can_restrict_members"))) $message->reply("this user is admin, it cannot be kicked");
                    }
                }
            }
        } catch (\skrtdev\Telegram\BadRequestException $e) {
            if (str_contains($e, "not enough rights to restrict/unrestrict chat member")) $message->reply("i don't have enough rights");
        }
    }
});

$Bot->onCommand('warn', function (Message $message) {
    $firstNameReply = $message?->reply_to_message?->from?->first_name;
    $lastNameReply = $message?->reply_to_message?->from?->last_name;
    $useridReply = $message?->reply_to_message?->from?->id;
    $mentionUserReply = "<a href='tg://user?id=$useridReply'>$firstNameReply $lastNameReply</a>";
    $chatid = $message?->chat?->id;
    $userid = $message?->from?->id;
    $useridReply = $message?->reply_to_message?->from?->id;
    $conn = new PDO("mysql:host=localhost;dbname=" . getenv("dbname"), getenv("dbuser"), getenv("dbpass"));

    try {
    if (!isAdmin($userid, $chatid) or !hasRight($userid, $chatid, "can_restrict_members")) {
        $message->reply("you dont have enough rights to warn a user");
    }
    elseif (isAdmin($useridReply, $chatid)) { $message->reply($mentionUserReply . " is admin, i can't warn them"); }
    elseif (warnMember($chatid, $useridReply) and !is_null($useridReply)) {
        $getWarns = $conn->query("select * from frasharpbot.warns where warns.id = '$useridReply'");
        $currentWarns = $getWarns?->fetch(PDO::FETCH_ASSOC);
        if ($currentWarns['warns'] <= 3)
            $message->reply("$mentionUserReply is warned: {$currentWarns['warns']}/3");
        if ($currentWarns['warns'] >= 3) {
            kickMember($chatid, $useridReply);
            $message->chat->sendMessage($mentionUserReply . " kicked. 3 warns limit exceeded");
            $conn->query("update frasharpbot.warns set warns.warns = 0 where warns.id = '$useridReply'");
        }
    }
} catch (\skrtdev\Telegram\BadRequestException $e) { if (str_contains($e, "not enough rights to restrict/unrestrict chat member")) $message->reply("i don't have enough rights"); }
});


$Bot->addErrorHandler(function ($e) {
    print('Caught '.get_class($e).' exception from general handler'.PHP_EOL);
    print($e.PHP_EOL);
});

$Bot->start();