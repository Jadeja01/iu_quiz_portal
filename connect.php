<?php

$servername = "sql302.infinityfree.com";
$username   = "if0_39993391";
$password   = "Quizportal8080";
$dbname     = "if0_39993391_quiz_portal";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die(json_encode(["success" => false, "message" => "Connection failed"]));
}
?>
