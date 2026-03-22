<?php

$host = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "healthcare_portal";
$port = 3308;

$conn = mysqli_connect($host, $user, $password, $dbname, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>