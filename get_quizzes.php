<?php
session_start();
include "./connect.php";

// Only allow logged-in admins
if(!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin'){
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$admin_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT id, quiz_code, title, description, created_at FROM quizzes WHERE admin_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$quizzes = [];
while($row = $result->fetch_assoc()){
    $quizzes[] = $row;
}

echo json_encode(["success" => true, "quizzes" => $quizzes]);
?>
