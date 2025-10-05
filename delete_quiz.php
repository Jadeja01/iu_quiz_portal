<?php
session_start();
include "./connect.php";

if(!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin'){
    echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$quiz_id = intval($data['quiz_id']);

if(!$quiz_id){
    echo json_encode(["success"=>false,"message"=>"Invalid quiz ID"]);
    exit;
}

// Delete related data
$conn->begin_transaction();

try {
    // Delete user attempts
    $stmt = $conn->prepare("DELETE ua.* FROM user_attempts ua INNER JOIN questions q ON ua.question_id=q.id WHERE q.quiz_id=?");
    $stmt->bind_param("i",$quiz_id);
    $stmt->execute();

    // Delete submissions
    $stmt = $conn->prepare("DELETE FROM submission WHERE quiz_id=?");
    $stmt->bind_param("i",$quiz_id);
    $stmt->execute();

    // Delete answers
    $stmt = $conn->prepare("DELETE a.* FROM answers a INNER JOIN questions q ON a.question_id=q.id WHERE q.quiz_id=?");
    $stmt->bind_param("i",$quiz_id);
    $stmt->execute();

    // Delete questions
    $stmt = $conn->prepare("DELETE FROM questions WHERE quiz_id=?");
    $stmt->bind_param("i",$quiz_id);
    $stmt->execute();

    // Delete quiz
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id=?");
    $stmt->bind_param("i",$quiz_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode(["success"=>true]);

} catch(Exception $e){
    $conn->rollback();
    echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
}
?>
