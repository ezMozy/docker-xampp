<?php

$host = 'db';
$dbname = 'chatroom';
$user = 'user';
$password = 'user';
$port = 3306;

$connection = new mysqli($host, $user, $password, $dbname, $port);

if ($connection->connect_error){
    die("Connessione fallita!: " . $connection->connect_error);
}
