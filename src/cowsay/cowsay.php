<?php

function cowsay($chatid, $text)
{
    global $Bot;
    $response = shell_exec("cowsay -s \"$text\"");

    if (isset($response)) {
        return $Bot->sendMessage([
            "chat_id" => $chatid,
            "text" => "`" . $response . "`",
            "parse_mode" => "Markdown"
        ]);
    }
    return false;
}