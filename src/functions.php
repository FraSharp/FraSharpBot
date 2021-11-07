<?php
// function to demote a member
use skrtdev\Telegram\Message;

function demoteMember(int $chatId, int $userId): ?bool
{
    global $Bot;

    return $Bot->promoteChatMember([
        "chat_id" => $chatId,
        "user_id" => $userId,
        "can_change_info" => false,
        "can_delete_messages" => false,
        "can_restrict_members" => false,
        "can_pin_messages" => false,
        "can_promote_members" => false
    ]);

}


// function to demote a member
function muteMember(int $chatId, int $userId): ?bool
{
    global $Bot;

    return $Bot->restrictChatMember([
        "chat_id" => $chatId,
        "user_id" => $userId,
        "can_send_messages" => false,
        "can_send_media_messages" => false,
        "can_send_polls" => false,
        "can_send_other_messages" => false
    ]);

}


// function to demote a member
function unmuteMember(int $chatId, int $userId): ?bool
{
    global $Bot;

    return $Bot->restrictChatMember([
        "chat_id" => $chatId,
        "user_id" => $userId,
        "can_send_messages" => true,
        "can_send_media_messages" => true,
        "can_send_polls" => true,
        "can_send_other_messages" => true
    ]);

}


// function to promote a member
function promoteMember(int $chatId, int $userId): ?bool
{
    global $Bot;

    return $Bot->promoteChatMember([
        "chat_id" => $chatId,
        "user_id" => $userId,
        "can_change_info" => true,
        "can_delete_messages" => true,
        "can_restrict_members" => true,
        "can_pin_messages" => true,
        "can_promote_members" => false
    ]);
}


// function to custom promote a member
function customPromote($chatId, $userId, $text): ?bool
{
    global $Bot;
    $ai = explode(" ", $text);
    unset($ai[0]);

    return $Bot->promoteChatMember([
        "chat_id" => $chatId,
        "user_id" => $userId,
        "can_change_info" => boolval($ai[1]),
        "can_delete_messages" => boolval($ai[2]),
        "can_restrict_members" => boolval($ai[3]),
        "can_pin_messages" => boolval($ai[4]),
        "can_promote_members" => boolval($ai[5])
    ]);
}


// function to search into a json file
function idFromJson($json, $userid): bool
{
    foreach ($json as $id) {
        if ($id === $userid) {
            return true;
        }
    }
    return false;
}


/**
 * @throws JsonException
 */
function isAllowed($userId): bool
{
    $perm = json_decode(file_get_contents(__DIR__ . "/elevated/elevated.json"), false,  512, JSON_THROW_ON_ERROR);

    (idFromJson($perm->allowed_users->id, $userId)) ? $isAllowed = true : $isAllowed = false;

    return $isAllowed;
}


/**
 * @throws JsonException
 */
function isBotOwner($userId): bool
{
    $perm = json_decode(file_get_contents(__DIR__ . "/elevated/elevated.json"), false, 512, JSON_THROW_ON_ERROR);

    (idFromJson($perm->owner->id, $userId)) ? $isOwner = true : $isOwner = false;

    return $isOwner;
}


/**
 * @throws JsonException
 */
function isBotSudo($userId): bool
{
    return isAllowed($userId) || isBotOwner($userId);
}


function isGbanned($userId): bool
{
    $conn = new PDO("mysql:host=localhost;dbname=" . getenv("dbname"), getenv("dbuser"), getenv("dbpass"));

    $gbanQuery = "SELECT * FROM frasharpbot.banned WHERE warns.user_id = '$userId'";
    $gbanResult = $conn->query($gbanQuery);
    $gbanRow = $gbanResult->fetch(PDO::FETCH_ASSOC);
    (isset($gbanRow['id']) === $userId) ? $userIsGbanned = true : $userIsGbanned = false;

    return $userIsGbanned;
}


function sendUserPic($chatId, $userId, $caption = NULL, $sendAsDocument = false, $infos = NULL): Message|bool|string|null
{
    global $Bot;

    $userPic = $Bot->getUserProfilePhotos(["user_id" => $userId, "limit" => 1]);
    if (!is_null($userPic)) {
        $photoId = $userPic->photos['0']['0']->file_id;
    }
    $getFile = $Bot->getFile(["file_id" => $photoId]);
    if (!is_null($getFile)) {
        $filePath = $getFile->file_path;
    }
    $botToken = getenv("token");

    if (!$sendAsDocument) {
        return $Bot->sendPhoto(["chat_id" => $chatId, "photo" => $photoId, "caption" => $caption]);
    }

    $downloadPath = "https://api.telegram.org/file/bot" . $botToken . "/" . $filePath;
    $saveTo = "downloads/pic.png";
    $fp = fopen($saveTo, 'wb+');
    $ch = curl_init($downloadPath);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    if (curl_errno($ch)) {
        return curl_error($ch);
    }
    curl_close($ch);
    fclose($fp);
    //return $Bot->sendDocument(["chat_id" => $chatId, "document" => $downloadPath, "caption" => $caption]);
    return shell_exec("curl -v -F document=@downloads/pic.png https://api.telegram.org/bot" . $botToken . "/sendDocument?chat_id=" . $chatId . "&caption=" . $infos);
}


function banMember($chatid, $userid, $revokeMessages): ?bool
{
    global $Bot;
    return $Bot->kickChatMember([
        "chat_id" => $chatid,
        "user_id" => $userid,
        "revoke_messages" => $revokeMessages
    ]);
}


function kickMember($chatid, $userid) {
    global $Bot;
    $Bot->banChatMember([
        "chat_id" => $chatid,
        "user_id" => $userid
    ]);
    $Bot->unbanChatMember([
        "chat_id" => $chatid,
        "user_id" => $userid
    ]);
}


/**
 * @throws \skrtdev\NovaGram\Exception
 */
function setMaxWarns($chatid, $warns, $PDO) {
    $getMaxWarnsQuery = $PDO->query("select * from frasharpbot.warns where warns.chat_id = '$chatid'");
    $maxWarns = $getMaxWarnsQuery->fetch(PDO::FETCH_ASSOC)["max_warns"];

    if ($warns < 0 || $warns >= 10) {
        throw new \skrtdev\NovaGram\Exception("Exception: max warns must be higher than 0 and lower than 10, you tried with");
    }

    if (is_null($maxWarns)) {
        return $PDO->exec("insert into frasharpbot.warns (chat_id, max_warns) values ('$chatid', '$warns')");
    }

    return $PDO->exec("update frasharpbot.warns set warns.max_warns = '$warns' where warns.chat_id = '$chatid'");
}


function getMaxWarns($chatid, $PDO) {
     $getMaxWarnsQuery = $PDO->query("select * from frasharpbot.warns where warns.chat_id = '$chatid'");
     return $getMaxWarnsQuery->fetch(PDO::FETCH_ASSOC)["max_warns"];
}


function warnMember($chatid, $userid, $PDO): bool
{
    $getWarns = $PDO->query("select * from frasharpbot.warns where warns.user_id = '$userid' and warns.chat_id = '$chatid'");
    $currentWarns = $getWarns->fetch(PDO::FETCH_ASSOC);
    if ($currentWarns['user_id'] !== $userid) {
        $PDO->query("insert into frasharpbot.warns (user_id, warns, chat_id) values ('$userid', 1, '$chatid')");
        return true;
    }

    if ($currentWarns['warns'] < getMaxWarns($chatid, $PDO)) {
        $PDO->query("update frasharpbot.warns set warns.warns = warns.warns + 1 where warns.user_id = '$userid' and warns.chat_id = '$chatid'");
        return true;
    }
    return false;
}


// function to check if a user is an admin of the chat
function isAdmin($userId, $chatId): bool
{
    global $Bot;

    $chatMember = $Bot->getChatMember([
        "chat_id" => $chatId,
        "user_id" => $userId
    ]);
    if (!is_null($chatMember) && in_array($chatMember->status, ["administrator", "creator"])) {
        return in_array($chatMember->status, ["administrator", "creator"]);
    }
    return false;
}


// function to check if a user is the creator of the chat
function isCreator($userId, $chatId): bool
{
    global $Bot;

    $chatMember = $Bot->getChatMember([
        "chat_id" => $chatId,
        "user_id" => $userId
    ]);

    if (!is_null($chatMember)) {
        return $chatMember->status === "creator";
    }
    return false;
}


function hasRight(int $userId, int $chatId, string $rightToCheck): bool
{
    global $Bot;

    $chatMember = $Bot->getChatMember([
        "chat_id" => $chatId,
        "user_id" => $userId
    ]);

    if (!is_null($chatMember)) {
        return $chatMember->$rightToCheck === true;
    }
    return false;
}

// TODO: leave every chat