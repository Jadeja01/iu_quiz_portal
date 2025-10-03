<?php
session_start();
include "./connect.php";

// Ensure user is logged in
if(!isset($_SESSION['id']) || $_SESSION['role'] !== 'user'){
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

if(!isset($_GET['code']) || empty($_GET['code'])){
    echo json_encode(["success" => false, "message" => "Quiz code required"]);
    exit;
}

$quiz_code = $_GET['code'];

// Fetch quiz details
$stmt = $conn->prepare("SELECT id, quiz_code, title, description FROM quizzes WHERE quiz_code = ?");
$stmt->bind_param("s", $quiz_code);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){
    echo json_encode(["success" => true, "quiz" => $row]);
} else {
    echo json_encode(["success" => false, "message" => "Quiz not found"]);
}
