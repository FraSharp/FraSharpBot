<?php
require_once(__DIR__ . "/../config.php");

/*
 *
 *  create banned table
 *
 */

$conn = new PDO("mysql:host=localhost;dbname=" . getenv("dbname"), getenv("dbuser"), getenv("dbpass"));
$sql = "CREATE TABLE IF NOT EXISTS frasharpbot.banned (";
$sql .= "id bigint NOT NULL UNIQUE )";
$conn->query($sql);


/*
 *
 *  create warns table
 *
 */

$conn = new PDO("mysql:host=localhost;dbname=" . getenv("dbname"), getenv("dbuser"), getenv("dbpass"));
$sql = "CREATE TABLE IF NOT EXISTS frasharpbot.warns (";
$sql .= "id bigint NOT NULL UNIQUE,";
$sql .= "warns int NOT NULL )";
$conn->query($sql);