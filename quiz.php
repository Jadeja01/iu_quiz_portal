<?php
session_start();
include "./connect.php";
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit;
}
$user_id = $_SESSION['id'];
if (!isset($_GET['code']) || empty($_GET['code'])) {
    echo "Quiz code missing!";
    exit;
}
$quiz_code = $_GET['code'];
$stmt = $conn->prepare("SELECT id, title, description FROM quizzes WHERE quiz_code = ?");
$stmt->bind_param("s", $quiz_code);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
if (!$quiz) {
    echo "Quiz not found!";
    exit;
}
$quiz_id = $quiz['id'];
//Check if user already attempted this quiz
$check = $conn->prepare("
    SELECT COUNT(*) AS cnt 
    FROM user_attempts ua
    INNER JOIN questions q ON ua.question_id = q.id
    WHERE ua.user_id = ? AND q.quiz_id = ?
");
$check->bind_param("ii", $user_id, $quiz_id);
$check->execute();
$check_res = $check->get_result()->fetch_assoc();
if ($check_res['cnt'] > 0) {
    echo "<script>alert('You have already attempted this quiz!'); window.location.href='/user.php';</script>";
    exit;
}
//Fetch questions for this quiz
$qstmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ?");
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    background: #f8f9fa;
}
.quiz-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 0;
    margin-bottom: 40px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.quiz-header h2 {
    font-weight: 700;
    margin: 0;
}
.quiz-header p {
    margin: 10px 0 0 0;
    opacity: 0.95;
}
.quiz-container {
    max-width: 900px;
    margin: 0 auto;
    padding-bottom: 50px;
}
.question-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 25px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    transition: all 0.3s;
}
.question-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}
.question-number {
    display: inline-block;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
    line-height: 40px;
    font-weight: 700;
    margin-right: 15px;
}
.question-text {
    display: inline-block;
    font-size: 1.2rem;
    font-weight: 600;
    color: #1e293b;
    vertical-align: middle;
}
.option-wrapper {
    margin-top: 20px;
}
.form-check {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 12px;
    border: 2px solid #e2e8f0;
    transition: all 0.3s;
    cursor: pointer;
}
.form-check:hover {
    background: #e0e7ff;
    border-color: #667eea;
}
.form-check-input:checked + .form-check-label {
    font-weight: 600;
    color: #667eea;
}
.form-check-input {
    width: 20px;
    height: 20px;
    margin-right: 10px;
    cursor: pointer;
}
.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}
.form-check-label {
    cursor: pointer;
    font-size: 1.05rem;
    color: #475569;
}
.submit-section {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    text-align: center;
}
.btn-submit {
    padding: 15px 50px;
    font-size: 1.2rem;
    font-weight: 700;
    border-radius: 50px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    color: white;
    transition: all 0.3s;
}
.btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    color: white;
}
.progress-bar-custom {
    position: sticky;
    top: 0;
    background: white;
    padding: 15px 0;
    z-index: 100;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}
.info-badge {
    display: inline-block;
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50px;
    font-weight: 600;
    margin: 0 10px;
}
</style>
</head>
<body>

<div class="quiz-header">
    <div class="container">
        <h2><i class="fas fa-clipboard-question me-3"></i><?php echo htmlspecialchars($quiz['title']); ?></h2>
        <p><i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($quiz['description']); ?></p>
    </div>
</div>

<div class="container quiz-container">
    <div class="progress-bar-custom">
        <div class="text-center">
            <span class="info-badge">
                <i class="fas fa-question-circle me-2"></i>
                <span id="totalQuestions">0</span> Questions
            </span>
            <span class="info-badge">
                <i class="fas fa-clock me-2"></i>
                Answer All Questions
            </span>
        </div>
    </div>

    <form id="quizForm">
        <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
        <?php 
        $qno = 1;
        $total_questions = 0;
        while ($q = $questions->fetch_assoc()): 
            $total_questions++;
        ?>
            <div class="question-card">
                <div class="mb-3">
                    <span class="question-number"><?php echo $qno; ?></span>
                    <span class="question-text"><?php echo htmlspecialchars($q['question_text']); ?></span>
                </div>
                
                <div class="option-wrapper">
                    <?php 
                    $astmt = $conn->prepare("SELECT id, answer_text FROM answers WHERE question_id = ?");
                    $astmt->bind_param("i", $q['id']);
                    $astmt->execute();
                    $optionsRes = $astmt->get_result();
                    $opt_num = 1;
                    while ($opt = $optionsRes->fetch_assoc()): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" 
                                   id="q<?php echo $q['id']; ?>_opt<?php echo $opt_num; ?>"
                                   name="q_<?php echo $q['id']; ?>" 
                                   value="<?php echo $opt['id']; ?>" required>
                            <label class="form-check-label" for="q<?php echo $q['id']; ?>_opt<?php echo $opt_num; ?>">
                                <?php echo chr(64 + $opt_num) . '. ' . htmlspecialchars($opt['answer_text']); ?>
                            </label>
                        </div>
                    <?php 
                    $opt_num++;
                    endwhile; ?>
                </div>
            </div>
        <?php 
        $qno++;
        endwhile; ?>
        
        <div class="submit-section">
            <p class="text-muted mb-3">
                <i class="fas fa-info-circle me-2"></i>
                Make sure you've answered all questions before submitting
            </p>
            <button type="submit" class="btn btn-submit">
                <i class="fas fa-paper-plane me-2"></i>Submit Quiz
            </button>
        </div>
    </form>
</div>

<script>
// Set total questions count
document.getElementById('totalQuestions').textContent = <?php echo $total_questions; ?>;

document.getElementById('quizForm').addEventListener('submit', async e => {
    e.preventDefault();
    
    // Confirm submission
    if(!confirm('Are you sure you want to submit? You cannot change your answers after submission.')) {
        return;
    }
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    try {
        const res = await fetch("/submit_quiz.php", {
            method: "POST",
            body: JSON.stringify(data),
            headers: {"Content-Type": "application/json"}
        });
        const result = await res.json();
        
        if(result.success){
            alert(`🎉 Quiz submitted successfully!\n\nYour Score: ${result.score}/${result.total}\nPercentage: ${Math.round((result.score/result.total)*100)}%`);
            window.location.href = "/user.php";
        } else {
            alert(result.message);
        }
    } catch(err){
        console.error(err);
        alert("Error submitting quiz. Please try again.");
    }
});

// Smooth scroll to unanswered questions
document.querySelectorAll('.form-check-input').forEach(radio => {
    radio.addEventListener('change', function() {
        this.closest('.question-card').style.background = '#f0fdf4';
        setTimeout(() => {
            this.closest('.question-card').style.background = 'white';
        }, 300);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>