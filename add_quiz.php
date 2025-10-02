<?php
session_start();
include "connect.php";

if(!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin'){
    header("Location: index.php");
    exit;
}

// Random quiz code
function generateQuizCode() {
        $c = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    	$s = str_shuffle($c);
    	$quiz_code = substr($s,0,6);
    return $quiz_code;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $questions = $data['questions'] ?? [];

    if(!$title || !$description || empty($questions)){
        echo json_encode(["success"=>false, "message"=>"All fields are required!"]);
        exit;
    }

    // Insert into quizzes table
    $quiz_code = generateQuizCode();
    $stmt = $conn->prepare("INSERT INTO quizzes (admin_id, title, description, quiz_code) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $_SESSION['id'], $title, $description, $quiz_code);
    
    if($stmt->execute()){
        $quiz_id = $stmt->insert_id;

        // Insert questions & answers
        foreach($questions as $q){
            $question_text = $q['question'] ?? '';
            $options = $q['options'] ?? [];
            $correct = $q['correct'] ?? 0;

            if(!$question_text || count($options) !== 4){
                continue;
            }

            $stmt_q = $conn->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (?, ?)");
            $stmt_q->bind_param("is", $quiz_id, $question_text);
            $stmt_q->execute();
            $question_id = $stmt_q->insert_id;

            foreach($options as $index => $opt){
                $is_correct = ($index == $correct) ? 1 : 0;
                $stmt_a = $conn->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
                $stmt_a->bind_param("isi", $question_id, $opt, $is_correct);
                $stmt_a->execute();
            }
        }

        echo json_encode(["success"=>true, "message"=>"Quiz created!", "quiz_code"=>$quiz_code]);
    } else {
        echo json_encode(["success"=>false, "message"=>"Failed to create quiz.","error"=>$stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>
