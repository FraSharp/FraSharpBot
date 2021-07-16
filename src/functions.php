<?php

// function to demote a member
function demoteMember(int $chatId, int $userId) {
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
function muteMember(int $chatId, int $userId) {
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
function unmuteMember(int $chatId, int $userId) {
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
function promoteMember(int $chatId, int $userId) {
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
function customPromote($chatId, $userId, $text) {
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


// function to get json from a webpage with cURL
function get_json($url){
    $base = "https://api.github.com/";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $base . $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $content = curl_exec($curl);
    curl_close($curl);
    return $content;
}


// function to get tiny url of a text
function get_tiny_url($url)
{
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url=' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}


// function to create an hastebin with a text
function do_hastebin_file($text)
{
    $url = 'https://hastebin.com/documents';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('data' => $text));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 0);

    $normal = "https://hastebin.com/";
    $r = json_decode(curl_exec($ch), TRUE);
    $normal .= $r["key"];
    $raw = str_replace('com/', 'com/raw/', $normal);
    curl_close($ch);
    //return array(get_tiny_url($normal), get_tiny_url($raw));
    return $r;
}


function hastebin($text) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://hastebin.com/documents",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_POSTFIELDS => array('data' => $text),
        CURLOPT_HTTPHEADER => array(
            "accept: application/json, text/javascript, */*; q=0.01",
            "cache-control: no-cache",
            "content-type: application/json; charset=UTF-8",
            "origin: https://hastebin.com",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36",
            "x-requested-with: XMLHttpRequest"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $response_array = json_decode($response, true);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        //echo $response_array['key'];
        //return $result = "https://hastebin.com/" . $response_array['key'];
        return $response_array;
    }

}


// function to search into a json file
function idFromJson($json, $userid) {
    $isHere = false;
    foreach ($json as $id) {
        if ($id == $userid) $isHere = true;
    }
    return $isHere;
}

function isAllowed($userId) {
    $perm = json_decode(file_get_contents(__DIR__ . "/elevated/elevated.json"));

    (idFromJson($perm->allowed_users->id, $userId)) ? $isAllowed = true : $isAllowed = false;

    return $isAllowed;
}


function isBotOwner($userId) {
    $perm = json_decode(file_get_contents(__DIR__ . "/elevated/elevated.json"));

    (idFromJson($perm->owner->id, $userId)) ? $isOwner = true : $isOwner = false;

    return $isOwner;
}


function isBotSudo($userId) {
    if (isAllowed($userId) or isBotOwner($userId)) {
        return true;
    } else {
        return false;
    }
}


function isGbanned($userId) {
    $conn = new PDO("mysql:host=localhost;dbname=" . getenv("dbname"), getenv("dbuser"), getenv("dbpass"));

    $gbanQuery = "SELECT * FROM `frasharpbot`.`banned` WHERE `id` = '$userId'";
    $gbanResult = $conn->query($gbanQuery);
    $gbanRow = $gbanResult->fetch(PDO::FETCH_ASSOC);
    (isset($gbanRow['id']) === $userId) ? $userIsGbanned = true : $userIsGbanned = false;

    return $userIsGbanned;
}


function sendUserPic($chatId, $userId, $caption = NULL, $sendAsDocument = false) {
    global $Bot;

    $userPic = $Bot->getUserProfilePhotos(["user_id" => $userId, "limit" => 1]);
    $photoId = $userPic->photos['0']['0']->file_id;
    $getFile = $Bot->getFile(["file_id" => $photoId]);
    $filePath = $getFile->file_path;
    $botToken = getenv("token");

    if ($sendAsDocument == false) {
        return $Bot->sendPhoto(["chat_id" => $chatId, "photo" => $photoId, "caption" => $caption]);
    } else {
        if ($sendAsDocument == true) {
            $downloadPath = "https://api.telegram.org/file/bot" . $botToken . "/" . $filePath;
            $saveTo = "downloads/pic.png";
            $fp = fopen($saveTo, 'w+');
            $ch = curl_init($downloadPath);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
            curl_close($ch);
            if (isset($error_msg)) { }
            fclose($fp);
            //return $Bot->sendDocument(["chat_id" => $chatId, "document" => $downloadPath, "caption" => $caption]);
            return shell_exec("curl -v -F document=@downloads/pic.png https://api.telegram.org/bot" . $botToken . "/sendDocument?chat_id=" . $chatId . "&caption=" . $infos);
        }
    }
}


function banMember($chatid, $userid, $revokeMessages) {
    global $Bot;
    return $Bot->kickChatMember([
        "chat_id" => $chatid,
        "user_id" => $userid,
        "revoke_messages" => $revokeMessages
    ]);
}


function kickMember($chatid, $userid) {
    global $Bot;
    $Bot->kickChatMember([
        "chat_id" => $chatid,
        "user_id" => $userid
    ]);
    $Bot->unbanChatMember([
        "chat_id" => $chatid,
        "user_id" => $userid
    ]);
}

function warnMember($chatid, $userid) {
    $conn = new PDO("mysql:host=localhost;dbname=" . getenv("dbname"), getenv("dbuser"), getenv("dbpass"));
    $getWarns = $conn->query("select * from frasharpbot.warns where warns.id = '$userid'");
    $currentWarns = $getWarns->fetch(PDO::FETCH_ASSOC);
    if ($currentWarns['id'] != $userid) {
        $conn->query("insert into frasharpbot.warns (id, warns) values ('$userid', 1)");
        return true;
    } elseif ($currentWarns['warns'] < 3) {
        $conn->query("update frasharpbot.warns set warns.warns = warns.warns + 1 where warns.id = '$userid'");
        return true;
    }
}

// function to check if a user is an admin of the chat
function isAdmin($userId, $chatId) {
    global $Bot;
    $isAdmin = false;

    $chatMember = $Bot->getChatMember([
        "chat_id" => $chatId,
        "user_id" => $userId
    ]);
    if (in_array($chatMember->status, ["administrator", "creator"])) $isAdmin = true;

    return $isAdmin;
}


// function to check if a user is the creator of the chat
function isCreator($userId, $chatId) {
    global $Bot;
    $isCreator = false;

    $chatMember = $Bot->getChatMember([
        "chat_id" => $chatId,
        "user_id" => $userId
    ]);
    if ($chatMember->status == "creator") $isCreator = true;

    return $isCreator;
}


function hasRight(int $userId, int $chatId, string $rightToCheck) {
    global $Bot;
    $hasRight = false;

    $chatMember = $Bot->getChatMember([
        "chat_id" => $chatId,
        "user_id" => $userId
    ]);

    if ($chatMember->$rightToCheck === true) $hasRight = true;

    return $hasRight;
}