<?php

$servername = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "gestion_stages";

// create connection
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// if connection failed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
