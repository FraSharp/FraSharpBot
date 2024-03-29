<?php
require("vendor/autoload.php");
use skrtdev\NovaGram\Bot;
use skrtdev\Telegram\BadRequestException;
use skrtdev\Telegram\Chat;
use skrtdev\Telegram\Message;

require_once("config.php");

if (isset($modules)) {
    foreach ($modules as $module => $active) {
        if ($active) {
            require_once($module);
        }
    }
}

try {
    $Bot = new Bot(getenv("token"), [
        "command_prefixes" => [':'],
        "skip_old_updates" => true,
        "threshold" => 50,
        "parse_mode" => "HTML"
    ]);
} catch (\skrtdev\NovaGram\Exception $e) {
}

$PDO = new PDO("mysql:host=localhost;dbname=" . getenv("dbname"), getenv("dbuser"), getenv("dbpass"));

if (isset($Bot)) {

    // set default max warns [3] when bot is added to a group
    $Bot->onNewGroup(function (Chat $chat) use ($PDO) {
        $chatid = $chat->id;

        $getMaxWarnsQuery = $PDO->query("select * from frasharpbot.warns where warns.chat_id = '$chatid'");

        if (is_null($getMaxWarnsQuery->fetch(PDO::FETCH_ASSOC)["max_warns"])) {
            $PDO->exec("insert into frasharpbot.warns (chat_id, max_warns) values ('$chatid', 3)");
        }
    });

    $Bot->onCommand('start', function (Message $message) {
        $message->reply("FraSharpBot v0.1a | alive");
    });

    $Bot->onCommand('lag', function (Message $message) {
        $start = hrtime(true);
        $lagMex = $message->chat->sendMessage("lag...");
        $time_elapsed_secs = hrtime(true) - $start;

        if (!is_null($lagMex)) {
            $lagMex->editText("elapsed time: " . round($time_elapsed_secs / 1000000, 2) . " ms", true);
        }
    });

    $Bot->onCommand('info', function (Message $message) use ($Bot, $PDO) {
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

        if (str_starts_with($message->text, ":info ")) {
            $uname = str_ireplace(":info @", "", $message->text);
            $idk = $PDO->query("select * from frasharpbot.user where user.username = '$uname'");
            $user = $idk->fetch(PDO::FETCH_ASSOC);

            try {
                getUserInfo($Bot, $chatid, $user["user_id"], $user["username"], $user["firstname"], $user["lastname"]);
            } catch (BadRequestException $e) {
                if (str_contains($e, "invalid user_id specified")) {
                    $message->reply("user not present in database");
                }
            }

        } else if (is_null($replyToMessage)) {
            getUserInfo($Bot, $chatid, $userid, $username, $firstName, $lastName);
        } else {
            getUserInfo($Bot, $chatid, $useridReply, $usernameReply, $firstNameReply, $lastNameReply);
        }

    });

    $Bot->onMessage(function (Message $message) {
        if (!is_null($message->text) && str_contains($message->text, ":cowsay") && isBotOwner($message?->from?->id)) {
            $cowsayText = str_ireplace(":cowsay ", "", $message->text);
            cowsay($message?->chat?->id, $cowsayText);
        }
    });

    $Bot->onMessage(function (Message $message) {
        // only if message sender is the bot owner
        if (!is_null($message->text) && str_contains($message->text, ":sh") && isBotOwner($message?->from?->id)) {
            // START: remove :sh and keep every other thing
            $explodeMessage = explode(" ", $message->text);
            unset($explodeMessage[0]);
            $command = implode(" ", $explodeMessage);
            // END: remove :sh and keep every other thing

            if (str_contains($command, "rm ")) {
                $message->reply("you can only delete files manually");
            }
            else {

                if (PHP_OS_FAMILY === "Windows") {
                    $response = shell_exec("powershell; " . $command);
                } // if host os is windows
                elseif (str_contains($command, "neofetch")) {
                    $response = shell_exec("neofetch --stdout");
                } // if host os is !windows and command is neofetch, use --stdout flag else it will show bad output
                else {
                    $response = shell_exec($command . " 2<&1");
                } // any other thing in host os !windows

                if (!is_null($response) && str_contains($response, "sh:")) {
                    $message->reply("$command, not found");
                } // if command is not found
                elseif (isset($response)) {
                    $message->reply("<pre><b>francesco@Francescos-MBA</b>:<b>~ </b></pre>" . $response);
                }
            }
        }
    });

    $Bot->onMessage(function (Message $message) use ($PDO){
        $firstName = $message?->from?->first_name;
        $lastName = $message?->from?->last_name;
        $username = $message?->from?->username;
        $userid = $message?->from?->id;
        $chatid = $message?->chat?->id;

        $result = $PDO->query("SELECT * FROM frasharpbot.user WHERE user.user_id = '$userid'");
        $row = $result->fetch(PDO::FETCH_ASSOC);

        $PDO->exec("insert ignore into frasharpbot.chat (`chat_id`) VALUES ('$chatid')");

        try {
            if (isset($row['username']) !== isset($username) && isset($row['firstname']) !== isset($firstName) && isset($row['lastname']) !== isset($lastName)) {
                $PDO->exec("DELETE FROM frasharpbot.user WHERE user.user_id = '$userid'");
                $PDO->exec("INSERT INTO frasharpbot.user (`user_id`, `username`, `firstname`, `lastname`) VALUES ('$userid', '$username', '$firstName', '$lastName')");
            } else if (isset($row['id']) !== isset($userid)) {
                $PDO->exec("INSERT INTO frasharpbot.user (`user_id`, `username`, `firstname`, `lastname`) VALUES ('$userid', '$username', '$firstName', '$lastName')");
            }
        } catch (PDOException) {
        }
    });

    $Bot->onMessage(function (Message $message) {
        $userid = $message?->from?->id;
        $conn = new mysqli("localhost", getenv("dbuser"), getenv("dbpass"), getenv("dbname"));
        if ($message->text === ":refresh" && (isAllowed($userid) || isBotOwner($userid))) {
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
                $refreshCache = opcache_reset() ? "✅" : "❌";
            }

            if ($checkLog && $checkTable) {
                $refreshStatus = "passed";
            } else if (!$checkTable && $checkLog) {
                $refreshStatus = "partially passed";
            } else if (!$checkLog && $checkTable) {
                $refreshStatus = "failed";
            }

            sleep(1);


            /** @var $refreshStatus */
            $refreshText = "refresh status: $refreshStatus";
            $refreshText .= "\n\t$refreshLog database logs";
            $refreshText .= "\n\t$refreshTable database tables";
            if (isset($refreshCache)) {
                (!isset($opcacheStatus)) ? $refreshText .= "\n\t$refreshCache caches" : $refreshText .= "\n\t$opcacheStatus";
            }


            if (!is_null($refresh)) {
                $refresh->editText($refreshText);
            }
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
                if ($chat->type === "group") {
                    $message->reply("this can't be used in normal groups");
                } else if (is_null($replyToMessage) && is_null($usernameReply) && (isBotSudo($userid) || hasRight($userid, $chatid, "can_restrict_members"))) {
                    kickMember($chatid, $useridToKick);
                    $message->reply($useridToKick . " was kicked");
                } else if ((isBotSudo($userid) || hasRight($userid, $chatid, "can_restrict_members"))) {
                    kickMember($chatid, $useridReply);
                    $message->reply($mentionUserReply . " was kicked");
                } else if ((isAdmin($useridReply, $chatid) || isBotSudo($userid) ) && (isBotSudo($userid) || hasRight($userid, $chatid, "can_restrict_members"))) {
                    $message->reply("this user cannot be kicked");
                }
            } catch (BadRequestException $e) {
                if (str_contains($e, "not enough rights to restrict/unrestrict chat member")) {
                    $message->reply("i don't have enough rights");
                }
            }
        }
    });

    $Bot->onCommand('warn', function (Message $message) use ($PDO) {
        $firstNameReply = $message?->reply_to_message?->from?->first_name;
        $lastNameReply = $message?->reply_to_message?->from?->last_name;
        $useridReply = $message?->reply_to_message?->from?->id;
        $mentionUserReply = "<a href='tg://user?id=$useridReply'>$firstNameReply $lastNameReply</a>";
        $chatid = $message?->chat?->id;
        $useridReply = $message?->reply_to_message?->from?->id;

        try {
            if (isAdmin($useridReply, $chatid)) {
                $message->reply($mentionUserReply . " is admin, can't warn them");
            } elseif (warnMember($chatid, $useridReply, $PDO) && !is_null($useridReply)) {
                $getWarns = $PDO->query("select * from frasharpbot.warns where warns.user_id = '$useridReply' and warns.chat_id = '$chatid'");
                $currentWarns = $getWarns?->fetch(PDO::FETCH_ASSOC);
                if ($currentWarns['warns'] <= getMaxWarns($chatid, $PDO)) {
                    $message->reply("$mentionUserReply is warned: {$currentWarns['warns']}/" . getMaxWarns($chatid, $PDO));
                }
                if (!is_null(getMaxWarns($chatid, $PDO) && $currentWarns['warns'] === getMaxWarns($chatid, $PDO))) {
                    kickMember($chatid, $useridReply);
                    $message->chat->sendMessage($mentionUserReply . " kicked. " . getMaxWarns($chatid, $PDO) . " warns limit exceeded");
                    $PDO->exec("update frasharpbot.warns set warns.warns = 0 where warns.user_id = '$useridReply' and warns.chat_id = '$chatid'");
                }
            }

        } catch (BadRequestException $e) {
            if (str_contains($e, "not enough rights to restrict/unrestrict chat member")) {
                $message->reply("i don't have enough rights");
            }
        }
    });

    $Bot->onMessage(function (Message $message) use ($PDO) {
        if (str_starts_with(!is_null($message->text), ":setMaxWarns")) {
            $getWarnsVal = explode(" ", $message->text);
            $maxWarns = (int)$getWarnsVal[1];
            try {
                setMaxWarns($message->chat->id, $maxWarns, $PDO);
            } catch (\skrtdev\NovaGram\Exception $e) {
                $message->reply($e->getMessage());
            }
        }
    });

    $Bot->onCommand("leaveall", function (Message $message) use ($PDO) {
        if (isCreator($message->from->id, $message->chat->id)) {
            try {
                leaveAllChats($PDO);
            } catch (PDOException $e) {
                $message->reply($e->getMessage());
            }
        }
    });


    $Bot->addErrorHandler(function ($e) {
        print('Caught ' . get_class($e) . ' exception from general handler' . PHP_EOL);
        print($e . PHP_EOL);
    });

    try {
        $Bot->start();
    } catch (\skrtdev\NovaGram\Exception $e) {
    }
}