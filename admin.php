<?php
session_start();
include "./connect.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Quiz Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    background: #f8f9fa;
}
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 0;
    margin-bottom: 40px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.page-header h2 {
    font-weight: 700;
    margin: 0;
}
.form-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin-bottom: 40px;
}
.btn-add, .btn-submit {
    border: none;
    border-radius: 50px;
    font-weight: 600;
    padding: 10px 25px;
    transition: all 0.3s;
}
.btn-add {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.btn-submit {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}
.btn-view {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
}
.btn-view:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}
.question-card {
    background: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}
.quiz-card {
    transition: all 0.2s ease;
}
.quiz-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<?php include "./navbar.php"; ?>

<!-- CREATE QUIZ SECTION -->
<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-plus-circle me-2"></i>Create New Quiz</h2>
        <p class="mb-0 mt-2">Design engaging quizzes for your students</p>
    </div>
</div>

<div class="container">
    <div class="form-card">
        <form id="quizForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fas fa-heading me-2"></i>Quiz Title</label>
                    <input type="text" class="form-control" id="quizTitle" placeholder="Enter quiz title" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fas fa-align-left me-2"></i>Quiz Description</label>
                    <input type="text" class="form-control" id="quizDesc" placeholder="Brief description" required>
                </div>
            </div>

            <h4 class="mt-4"><i class="fas fa-question-circle me-2"></i>Questions (Max 10)</h4>
            <div id="questionsContainer"></div>

            <button type="button" class="btn btn-add mb-3" id="addQuestion">
                <i class="fas fa-plus me-2"></i>Add Question
            </button>
            <button type="submit" class="btn btn-submit mb-3 ms-2">
                <i class="fas fa-check me-2"></i>Create Quiz
            </button>
        </form>
    </div>

    <!-- MY QUIZZES SECTION -->
    <div class="page-header" style="margin-bottom: 30px;">
        <div class="container">
            <h2><i class="fas fa-list me-2"></i>My Quizzes</h2>
        </div>
    </div>

    <div id="quizList" class="row mb-5"></div>
</div>

<script>
let questionCount = 0;
const maxQuestions = 10;

// Add question dynamically
document.getElementById('addQuestion').addEventListener('click', () => {
    if (questionCount >= maxQuestions) {
        alert("Maximum 10 questions allowed!");
        return;
    }

    const container = document.getElementById('questionsContainer');
    const div = document.createElement('div');
    div.classList.add('question-card');
    div.innerHTML = `
        <h5>Question ${questionCount + 1}</h5>
        <div class="mb-3">
            <input type="text" class="form-control questionText" placeholder="Enter question" required>
        </div>
        <div class="row">
            ${[1,2,3,4].map(i=>`
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control option" placeholder="Option ${i}" required>
                </div>`).join('')}
        </div>
        <div class="mt-3">
            <label class="form-label">Correct Option (0–3)</label>
            <input type="number" class="form-control correctOption" min="0" max="3" value="0" required>
        </div>`;
    container.appendChild(div);
    questionCount++;
});

// Create Quiz
document.getElementById('quizForm').addEventListener('submit', async e => {
    e.preventDefault();
    const title = document.getElementById('quizTitle').value;
    const description = document.getElementById('quizDesc').value;

    const questionDivs = document.querySelectorAll('#questionsContainer > div');
    const questions = Array.from(questionDivs).map(div => {
        const question = div.querySelector('.questionText').value;
        const options = Array.from(div.querySelectorAll('.option')).map(i => i.value);
        const correct = parseInt(div.querySelector('.correctOption').value);
        return { question, options, correct };
    });

    try {
        const res = await fetch('add_quiz.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, description, questions })
        });
        const data = await res.json();

        if (data.success) {
            alert(`Quiz Created! Code: ${data.quiz_code}`);
            document.getElementById('quizForm').reset();
            document.getElementById('questionsContainer').innerHTML = '';
            questionCount = 0;
            loadQuizzes();
        } else {
            alert(data.message || 'Failed to create quiz!');
        }
    } catch (err) {
        console.error(err);
        alert("Error creating quiz!");
    }
});

// Load all quizzes
async function loadQuizzes() {
    try {
        const res = await fetch('get_quizzes.php');
        const data = await res.json();

        const quizList = document.getElementById('quizList');
        quizList.innerHTML = "";

        if (data.success && data.quizzes.length > 0) {
            data.quizzes.forEach(q => {
                const card = document.createElement('div');
                card.classList.add('col-md-4', 'mb-4');
                card.innerHTML = `
                    <div class="card quiz-card shadow-sm">
                        <div class="card-header fw-bold">
                            <i class="fas fa-book me-2"></i>${q.title}
                        </div>
                        <div class="card-body">
                            <p class="card-text mb-3">${q.description}</p>
                            <div class="mb-2">
                                <span class="badge bg-primary"><i class="fas fa-key me-1"></i>Code: ${q.quiz_code}</span>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-success"><i class="fas fa-users"></i> ${q.attempts || 0} Attempts</span>
                            </div>
                            <p class="text-muted mb-3"><small><i class="far fa-calendar me-1"></i>${q.created_at}</small></p>
                            <button class="btn btn-view btn-sm w-100 mb-2" onclick="viewSubmissions(${q.id})">
                                <i class="fas fa-eye me-2"></i>View Submissions
                            </button>
                            <button class="btn btn-danger btn-sm w-100" onclick="deleteQuiz(${q.id})">
                                <i class="fas fa-trash me-2"></i>Delete Quiz
                            </button>
                        </div>
                    </div>
                `;
                quizList.appendChild(card);
            });
        } else {
            quizList.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No quizzes created yet.</p>
                </div>`;
        }
    } catch (err) {
        console.error("Error loading quizzes:", err);
    }
}

// View Submissions
function viewSubmissions(quizId) {
    window.location.href = 'view_submissions.php?quiz_id=' + quizId;
}

// Delete quiz
async function deleteQuiz(quizId) {
    if (!confirm("Are you sure you want to delete this quiz and all related data?")) return;

    try {
        const res = await fetch(`delete_quiz.php?quiz_id=${quizId}`, {
            method: 'GET'
        });

        const data = await res.json();

        if (data.success) {
            alert("Quiz deleted successfully!");
            loadQuizzes();
        } else {
            alert(data.message || "Failed to delete quiz!");
        }
    } catch (err) {
        console.error("Error deleting quiz:", err);
        alert("Something went wrong while deleting quiz!");
    }
}


window.addEventListener('DOMContentLoaded', loadQuizzes);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
