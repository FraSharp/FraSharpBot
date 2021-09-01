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
$conn->query($sql);

/*
 *
 *  create warns table
 *
 */

$sql = "CREATE TABLE IF NOT EXISTS frasharpbot.warns (";
$sql .= "id bigint NOT NULL UNIQUE,";
$sql .= "warns int NOT NULL )";
$conn->query($sql);


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
$conn->query($sql);