<?php
session_start();
include "./connect.php";

// Only allow logged-in users
if(!isset($_SESSION['id']) || $_SESSION['role'] !== 'user'){
    header("Location: index.php");
    exit;
}

if(!isset($_GET['code'])){
    echo "Quiz code missing!";
    exit;
}

$quiz_code = $_GET['code'];

// Fetch quiz details
$stmt = $conn->prepare("SELECT id, title, description FROM quizzes WHERE quiz_code = ?");
$stmt->bind_param("s", $quiz_code);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if(!$quiz){
    echo "Quiz not found!";
    exit;
}

$quiz_id = $quiz['id'];

// Fetch questions with their options
$qstmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id=?");
$qstmt->bind_param("i", $quiz_id);
$qstmt->execute();
$questions = $qstmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($quiz['title']); ?> - Attempt Quiz</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container my-5">

<h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
<p><?php echo htmlspecialchars($quiz['description']); ?></p>

<form id="quizForm">
    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
    <?php 
    $qno = 1;
    while($q = $questions->fetch_assoc()): ?>
        <div class="mb-4">
            <h5><?php echo $qno++ . ". " . htmlspecialchars($q['question_text']); ?></h5>
            <?php 
            // Fetch options for this question
            $astmt = $conn->prepare("SELECT id, answer_text FROM answers WHERE question_id=?");
            $astmt->bind_param("i", $q['id']);
            $astmt->execute();
            $optionsRes = $astmt->get_result();

            while($opt = $optionsRes->fetch_assoc()): ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" 
                           name="q_<?php echo $q['id']; ?>" 
                           value="<?php echo $opt['id']; ?>" required>
                    <label class="form-check-label"><?php echo htmlspecialchars($opt['answer_text']); ?></label>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endwhile; ?>

    <button type="submit" class="btn btn-primary">Submit Quiz</button>
</form>

<script>
document.getElementById('quizForm').addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    try {
        const res = await fetch("/submit_quiz.php", {
            method: "POST",
            body: JSON.stringify(data),
            headers: {"Content-Type":"application/json"}
        });
        const result = await res.json();
        if(result.success){
            alert(`Quiz submitted! Score: ${result.score}/${result.total}`);
            window.location.href = "/user.php";
        } else {
            alert(result.message);
        }
    } catch(err){
        console.error(err);
        alert("Error submitting quiz.");
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
