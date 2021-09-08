<?php

$TOKEN = "";

putenv("token=");
putenv("dbname=");
putenv("dbuser=");
putenv("dbpass=");

$dbname = "";
$dbpass = "";
$dbuser = "";

$modules = array(
    "info/info.php" => true,
    "cowsay/cowsay.php" => true,
    "functions.php" => true
);