<?php
session_start();
include "./connect.php";
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$quiz_id = intval($_GET['quiz_id'] ?? 0);
if (!$quiz_id) {
    echo json_encode(["success" => false, "message" => "Invalid quiz ID"]);
    exit;
}

$conn->begin_transaction();

try {
    // Delete all submissions related to this quiz
    $stmt = $conn->prepare("DELETE FROM submission WHERE quiz_id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    // Delete the quiz itself
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode(["success" => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
