<?php
session_start();
include "./connect.php";

if(!isset($_SESSION['id']) || $_SESSION['role'] !== 'user'){
    echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    exit;
}

$user_id = $_SESSION['id'];
$data = json_decode(file_get_contents("php://input"), true);

if(!$data || !isset($data['quiz_id'])){
    echo json_encode(["success"=>false,"message"=>"Invalid data"]);
    exit;
}

$quiz_id = intval($data['quiz_id']);
unset($data['quiz_id']); // Only answers remain

$score = 0;
$total = 0;

foreach($data as $q_key => $answer_id){
    $question_id = intval(str_replace('q_','',$q_key));
    $answer_id = intval($answer_id);

    // Save user's attempt
    $stmt = $conn->prepare("INSERT INTO user_attempts (user_id, question_id, answer, attempted_at) VALUES (?,?,?,NOW())");
    $stmt->bind_param("iii", $user_id, $question_id, $answer_id);
    $stmt->execute();

    // Check if selected answer is correct
    $cstmt = $conn->prepare("SELECT is_correct FROM answers WHERE id=?");
    $cstmt->bind_param("i",$answer_id);
    $cstmt->execute();
    $res = $cstmt->get_result();
    $row = $res->fetch_assoc();
    if($row && $row['is_correct'] == 1){
        $score++;
    }

    $total++;
}

echo json_encode(["success"=>true,"score"=>$score,"total"=>$total]);
?>
