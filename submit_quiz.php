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
unset($data['quiz_id']);

//Check if user already attempted this quiz
$check = $conn->prepare("SELECT id FROM submission WHERE user_id=? AND quiz_id=?");
$check->bind_param("ii", $user_id, $quiz_id);
$check->execute();
$check_res = $check->get_result();

if($check_res->num_rows > 0){
    echo json_encode(["success"=>false, "message"=>"You have already attempted this quiz."]);
    exit;
}

$score = 0;
$total = 0;

//Evaluate answers
foreach($data as $q_key => $answer_id){
    $question_id = intval(str_replace('q_','',$q_key));
    $answer_id = intval($answer_id);

    //record each answer
    $stmt = $conn->prepare("INSERT INTO user_attempts (user_id, question_id, answer, attempted_at) VALUES (?,?,?,NOW())");
    $stmt->bind_param("iii", $user_id, $question_id, $answer_id);
    $stmt->execute();

    // check correct answer
    $cstmt = $conn->prepare("SELECT is_correct FROM answers WHERE id=?");
    $cstmt->bind_param("i", $answer_id);
    $cstmt->execute();
    $res = $cstmt->get_result();
    $row = $res->fetch_assoc();

    if($row && $row['is_correct'] == 1){
        $score++;
    }

    $total++;
}

//Store final submission
$insert = $conn->prepare("INSERT INTO submission (user_id, quiz_id, score, total, submitted_at) VALUES (?,?,?,?,NOW())");
$insert->bind_param("iiii", $user_id, $quiz_id, $score, $total);
$insert->execute();

echo json_encode(["success"=>true,"score"=>$score,"total"=>$total]);
?>
