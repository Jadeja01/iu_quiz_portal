<?php
session_start();
include "./connect.php";

// Ensure user is logged in
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

if (!isset($_GET['code']) || empty($_GET['code'])) {
    echo json_encode(["success" => false, "message" => "Quiz code required"]);
    exit;
}

$quiz_code = $_GET['code'];
$user_id = $_SESSION['id'];

// Fetch quiz details
$stmt = $conn->prepare("SELECT id, quiz_code, title, description FROM quizzes WHERE quiz_code = ?");
$stmt->bind_param("s", $quiz_code);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $quiz_id = $row['id'];

    // Check if the user has already attempted this quiz
    $check = $conn->prepare("
        SELECT COUNT(*) AS attempts 
        FROM user_attempts 
        INNER JOIN questions ON user_attempts.question_id = questions.id 
        WHERE user_attempts.user_id = ? AND questions.quiz_id = ?
    ");
    $check->bind_param("ii", $user_id, $quiz_id);
    $check->execute();
    $attemptResult = $check->get_result()->fetch_assoc();

    $already_attempted = ($attemptResult['attempts'] > 0);

    echo json_encode([
        "success" => true,
        "quiz" => $row,
        "already_attempted" => $already_attempted
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Quiz not found"]);
}
?>
