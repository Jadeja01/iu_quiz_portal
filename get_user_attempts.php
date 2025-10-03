<?php
session_start();
include "./connect.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['id'];

// Fetch user's attempts with quiz details
$stmt = $conn->prepare("
    SELECT q.title, q.id AS quiz_id, COUNT(DISTINCT ua.question_id) AS total,
           SUM(CASE WHEN a.is_correct = 1 THEN 1 ELSE 0 END) AS score,
           MAX(ua.attempted_at) AS last_attempt
    FROM user_attempts ua
    INNER JOIN answers a ON ua.answer = a.id
    INNER JOIN questions ques ON ua.question_id = ques.id
    INNER JOIN quizzes q ON ques.quiz_id = q.id
    WHERE ua.user_id = ?
    GROUP BY q.id
    ORDER BY last_attempt DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$attempts = [];
while ($row = $result->fetch_assoc()) {
    $attempts[] = $row;
}

echo json_encode(["success" => true, "attempts" => $attempts]);
?>
