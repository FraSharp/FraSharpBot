<?php


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
    if (in_array($chatMember->status, ["creator"])) $isCreator = true;

    return $isCreator;
}
