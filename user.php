<?php
session_start();
include "./connect.php";
if(!isset($_SESSION['id']) || $_SESSION['role'] !== 'user'){
    header("Location: index.php");
    exit;
}
$user_id = $_SESSION['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Panel - Quiz Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    background: #f8f9fa;
}
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 50px 0;
    margin-bottom: 40px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.page-header h2 {
    font-weight: 700;
    font-size: 2.5rem;
    margin: 0;
}
.search-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin-bottom: 40px;
}
.search-card h4 {
    color: #667eea;
    font-weight: 600;
    margin-bottom: 20px;
}
.search-input-group {
    position: relative;
}
.search-input-group input {
    border-radius: 50px;
    padding: 15px 25px;
    border: 2px solid #e0e0e0;
    font-size: 1.1rem;
    transition: all 0.3s;
}
.search-input-group input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
}
.search-input-group button {
    border-radius: 50px;
    padding: 15px 35px;
    font-weight: 600;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    transition: all 0.3s;
}
.search-input-group button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}
.section-title {
    color: #1e293b;
    font-weight: 700;
    font-size: 1.8rem;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.quiz-card {
    background: white;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}
.quiz-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.quiz-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
}
.quiz-card-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 1.3rem;
}
.quiz-card-body {
    padding: 25px;
}
.score-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}
.empty-state i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 20px;
}
.empty-state p {
    color: #64748b;
    font-size: 1.1rem;
}
.btn-start {
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s;
    border: none;
}
.btn-start-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.btn-start-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    color: white;
}
.btn-start-disabled {
    background: #e2e8f0;
    color: #94a3b8;
    cursor: not-allowed;
}
</style>
</head>
<body>
<?php include "navbar.php"; ?>

<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-user-circle me-3"></i>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p class="mb-0 mt-2">Start exploring quizzes and challenge yourself</p>
    </div>
</div>

<div class="container">
    <div class="search-card">
        <h4><i class="fas fa-search me-2"></i>Find a Quiz</h4>
        <div class="input-group search-input-group">
            <input type="text" id="quizCodeInput" class="form-control" placeholder="Enter Quiz Code (e.g., ABC123)">
            <button class="btn btn-primary" onclick="searchQuiz()">
                <i class="fas fa-search me-2"></i>Find Quiz
            </button>
        </div>
    </div>

    <h4 class="section-title">
        <i class="fas fa-history"></i>
        <span>Your Attempted Quizzes</span>
    </h4>
    <div id="attemptedQuizzes"></div>
</div>

<script>
async function loadAttempts() {
    const res = await fetch("/get_user_attempts.php");
    const data = await res.json();
    console.log("Data:",data);
    const container = document.getElementById("attemptedQuizzes");
    container.innerHTML = "";
    
    if(data.success && data.attempts.length > 0){
        data.attempts.forEach(a => {
            const div = document.createElement("div");
            div.className = "quiz-card";
            div.innerHTML = `
                <div class="quiz-card-header">
                    <h5><i class="fas fa-book-open me-2"></i>${a.title}</h5>
                </div>
                <div class="quiz-card-body">
                    <div class="score-badge">
                        <i class="fas fa-chart-line"></i>
                        <span>Score: ${a.score}/${a.total}</span>
                    </div>
                    <p class="text-muted mt-3 mb-0">
                        <i class="far fa-calendar me-2"></i>Completed
                    </p>
                </div>
            `;
            container.appendChild(div);
        });
    } else {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <p>No quizzes attempted yet.</p>
                <p class="text-muted small">Use the search box above to find and start a quiz!</p>
            </div>
        `;
    }
}

// Search quiz by code
async function searchQuiz() {
    const code = document.getElementById("quizCodeInput").value.trim();
    if (!code) { 
        alert("Please enter a quiz code!"); 
        return; 
    }
    
    const res = await fetch(`/get_quiz_by_code.php?code=${code}`);
    const data = await res.json();
    console.log("quizdata:", data);
    
    const container = document.getElementById("attemptedQuizzes");
    
    if (data.success) {
        const quiz = data.quiz;
        const alreadyAttempted = data.already_attempted;
        
        const div = document.createElement("div");
        div.className = "quiz-card mt-4";
        div.innerHTML = `
            <div class="quiz-card-header">
                <h5><i class="fas fa-book me-2"></i>${quiz.title}</h5>
            </div>
            <div class="quiz-card-body">
                <p class="mb-4">${quiz.description || "No description available."}</p>
                ${alreadyAttempted ? 
                    '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>You have already attempted this quiz.</div>' : 
                    ''
                }
                <button id="startBtn" class="btn btn-start ${alreadyAttempted ? "btn-start-disabled" : "btn-start-primary"}" ${alreadyAttempted ? "disabled" : ""}>
                    <i class="fas ${alreadyAttempted ? "fa-check" : "fa-play"} me-2"></i>
                    ${alreadyAttempted ? "Already Attempted" : "Start Quiz"}
                </button>
            </div>
        `;
        
        container.innerHTML = "";
        container.appendChild(div);
        
        if (!alreadyAttempted) {
            document.getElementById("startBtn").addEventListener("click", () => {
                window.location.href = `/quiz.php?code=${quiz.quiz_code}`;
            });
        }
    } else {
        alert("Quiz not found! Please check the code and try again.");
    }
}

// Initial Load
loadAttempts();

// Allow Enter key to search
document.getElementById("quizCodeInput").addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
        searchQuiz();
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>