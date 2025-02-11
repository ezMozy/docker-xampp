<?php

$host = 'db';
$dbname = 'root_db';
$user = 'user';
$password = 'user';
$port = 3306;

$conn = new mysqli($host, $user, $password, $dbname, $port);

if ($conn->connect_error){
    die("Connessione fallita!: " . $conn->connect_error);
}
