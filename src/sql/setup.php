<?php
require_once(__DIR__ . "/../config.php");
$conn = new PDO("mysql:host=localhost;dbname=" . getenv("dbname"), getenv("dbuser"), getenv("dbpass"));
/*
 *
 *  create banned table
 *
 */


$sql = "CREATE TABLE IF NOT EXISTS frasharpbot.banned (";
$sql .= "id bigint NOT NULL UNIQUE )";
$conn->exec($sql);

/*
 *
 *  create warns table
 *
 */

$sql = "CREATE TABLE IF NOT EXISTS frasharpbot.warns (";
$sql .= "id int NOT NULL AUTO_INCREMENT,";
$sql .= "user_id bigint,";
$sql .= "chat_id bigint,";
$sql .= "max_warns bigint,";
$sql .= "warns int,";
$sql .= "PRIMARY KEY (id));";
$conn->exec($sql);


/*
 *
 *  create user table
 *
 */

$sql = "CREATE TABLE IF NOT EXISTS frasharpbot.user (";
$sql .= "user_id bigint NOT NULL UNIQUE,";
$sql .= "firstname varchar(255),";
$sql .= "lastname varchar(255),";
$sql .= "username varchar(255))";
$conn->exec($sql);


/*
 *
 *  create chats table
 *
 */

$sql = "CREATE TABLE IF NOT EXISTS frasharpbot.chat (";
$sql .= "chat_id bigint NOT NULL UNIQUE)";
$conn->exec($sql);
