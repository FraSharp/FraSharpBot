<?php
if (file_exists('vendor')) {
    require 'vendor/autoload.php';
}
else{
    if (!file_exists('novagram.phar')) {
        copy('http://gaetano.cf/novagram/phar.php', 'novagram.phar');
    }
    require_once 'novagram.phar';
}

use skrtdev\NovaGram\Utils;

function random_string(int $length = 10) {
    $characters = str_split('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    shuffle($characters);
    return substr(implode($characters), 0, $length);
}

$post = json_decode(file_get_contents("php://input"), true);

if(isset($post['token']) && isset($post['session_name'])){
    file_put_contents($post['session_name'].'.token'.random_string(), $post['token']);
    unlink(__FILE__);
}
elseif(isset($post['url'])){
    echo Utils::curl($post['url'], $post['data'] ?? []);
}

?>
