<?php

function getUserInfo($chatid, $userid, &$username, &$firstName, &$lastName) {
	global $Bot;

    
    $conn = new mysqli("host", "user", "password", "frasharpbot");
    $gbanQuery = "SELECT * FROM `frasharpbot`.`banned` WHERE `id` = '$userid'";
    $gbanResult = $conn->query($gbanQuery);
    $gbanRow = $gbanResult->fetch_assoc();

    $infos = "❓ informations about the user: ";

    $infos .= "\n\n🆔 <pre>user id:</pre> $userid";

    if ($firstName == "" or $firstName == " ") {
        $infos .= "\n🧍 <pre>first name:</pre> not set";
    } else {
        if ($firstName != "" or $firstName != " ") {
            $infos .= "\n🧍 <pre>first name:</pre> $firstName";
        }
    }

    if ($lastName == "" or $lastName == " ") {
        $infos .= "\n🧍 <pre>last name:</pre> not set";
    } else {
        if ($lastName != "" or $lastName != " ") {
            $infos .= "\n🧍 <pre>last name:</pre> $lastName";
        }
    }

    $infos .= isAllowed($userid) ? "\n🚫 <pre>status (bot):</pre> allowed (sudo)" : "";
    $infos .= isBotOwner($userid) ? "\n🚫 <pre>status (bot):</pre> owner" : "";
    $infos .= (isGbanned($userid)) ? "\n<pre>gbanned:</pre> yes" : "";

    $infos .= "\n🕵️ <pre>username:</pre> @$username";

    $infos .= "\n🔗 <pre>permalink:</pre> <a href='tg://user?id=$userid'>";
    $infos .= (isset($lastName) or $lastName !== null) ? "$firstName $lastName</a>" : "$firstName</a>";

    $userPic = $Bot->getUserProfilePhotos(["user_id" => $userid, "limit" => 1]);
    if ($userPic->total_count > 0) {
        return sendUserPic($chatid, $userid, $infos);
    } else {
        return $Bot->sendMessage($infos);
    }
}