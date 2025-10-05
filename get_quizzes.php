<?php
session_start();
include "./connect.php";

if(!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin'){
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$admin_id = $_SESSION['id'];

// Fetch all quizzes created by this admin
$stmt = $conn->prepare("
    SELECT id, quiz_code, title, description, created_at 
    FROM quizzes 
    WHERE admin_id = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$quizzes = [];

while($row = $result->fetch_assoc()){
    // Count number of submissions for this quiz
    $countStmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) AS attempts FROM submission WHERE quiz_id = ?");
    $countStmt->bind_param("i", $row['id']);
    $countStmt->execute();
    $countRes = $countStmt->get_result()->fetch_assoc();

    $row['attempts'] = $countRes['attempts'] ?? 0;
    $quizzes[] = $row;
}

echo json_encode(["success" => true, "quizzes" => $quizzes]);
?>
