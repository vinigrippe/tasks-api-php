<?php

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$dbhost = $_ENV['DB_HOST'];
$dbuser = $_ENV['DB_USER'];
$dbpass = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

$dsn = "mysql:host=$dbhost;dbname=$dbname";
$pdo = new PDO($dsn, $dbuser, $dbpass);

return $pdo;