<?php
session_start();
include "./connect.php";

// Only allow logged-in admins
if(!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin'){
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Create Quiz</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container my-5">
    <h2>Create New Quiz</h2>
    <form id="quizForm">
        <div class="mb-3">
            <input type="text" class="form-control" id="quizTitle" placeholder="Quiz Title" required>
        </div>
        <div class="mb-3">
            <textarea class="form-control" id="quizDesc" placeholder="Quiz Description" required></textarea>
        </div>

        <h4>Questions (Max 10)</h4>
        <div id="questionsContainer"></div>

        <button type="button" class="btn btn-secondary mb-3" id="addQuestion">Add Question</button>
        <br>
        <button type="submit" class="btn btn-primary">Create Quiz</button>
    </form>
</div>

<div class="container my-5">
    <h2>My Quizzes</h2>
    <div id="quizList" class="row"></div>
</div>

<script>
let questionCount = 0;
const maxQuestions = 10;

// Add Question dynamically
document.getElementById('addQuestion').addEventListener('click', () => {
    if(questionCount >= maxQuestions){
        alert("Maximum 10 questions allowed!");
        return;
    }

    const container = document.getElementById('questionsContainer');
    const div = document.createElement('div');
    div.classList.add('mb-4', 'border', 'p-3');
    div.innerHTML = `
        <h5>Question ${questionCount+1}</h5>
        <input type="text" class="form-control mb-2 questionText" placeholder="Question" required>
        <input type="text" class="form-control mb-2 option" placeholder="Option 1" required>
        <input type="text" class="form-control mb-2 option" placeholder="Option 2" required>
        <input type="text" class="form-control mb-2 option" placeholder="Option 3" required>
        <input type="text" class="form-control mb-2 option" placeholder="Option 4" required>
        <label>Correct Option (0-3)</label>
        <input type="number" class="form-control mb-2 correctOption" min="0" max="3" value="0" required>
    `;
    container.appendChild(div);
    questionCount++;
});

// Handle quiz creation form submit
document.getElementById('quizForm').addEventListener('submit', async e => {
    e.preventDefault();
    const title = document.getElementById('quizTitle').value;
    const description = document.getElementById('quizDesc').value;

    const questionDivs = document.querySelectorAll('#questionsContainer > div');
    const questions = Array.from(questionDivs).map(div => {
        const question_text = div.querySelector('.questionText').value;
        const options = Array.from(div.querySelectorAll('.option')).map(i=>i.value);
        const correct = parseInt(div.querySelector('.correctOption').value);
        return { question: question_text, options, correct };
    });

    try {
        const res = await fetch('/add_quiz.php', {
            method:'POST',
            body: JSON.stringify({ title, description, questions }),
            headers: { 'Content-Type':'application/json' }
        });
        const data = await res.json();
        if(data.success){
            alert(`Quiz Created! Code: ${data.quiz_code}`);
            loadQuizzes();
            document.getElementById("quizForm").reset();
            document.getElementById("questionsContainer").innerHTML = "";
            questionCount = 0;
        } else {
            alert(data.message);
        }
    } catch(err){
        console.error(err);
        alert("Error creating quiz!");
    }
});

// Load quizzes with attempts count
async function loadQuizzes() {
    try {
        const res = await fetch('/get_quizzes.php');
        const data = await res.json();

        const quizList = document.getElementById('quizList');
        quizList.innerHTML = "";

        if(data.success && data.quizzes.length > 0){
            console.log(data.quizzes);
            data.quizzes.forEach(q => {
                const card = document.createElement('div');
                card.classList.add('col-md-4', 'mb-3');
                card.innerHTML = `
                    <div class="card shadow">
                        <div class="card-body">
                            <h5 class="card-title">${q.title}</h5>
                            <p class="card-text">${q.description}</p>
                            <p><strong>Code:</strong> ${q.quiz_code}</p>
                            <p><strong>Attempts:</strong> ${q.attempts || 0}</p>
                            <p><small class="text-muted">Created: ${q.created_at}</small></p>
                            <button class="btn btn-danger btn-sm" onclick="deleteQuiz(${q.id})">Delete Quiz</button>
                        </div>
                    </div>
                `;
                quizList.appendChild(card);
            });
        } else {
            quizList.innerHTML = `<p>No quizzes created yet.</p>`;
        }
    } catch(err){
        console.error("Error loading quizzes:", err);
    }
}

// Delete quiz and all related data
async function deleteQuiz(quizId){
    if(!confirm("Are you sure you want to delete this quiz and all related data?")) return;

    try {
        const res = await fetch('/delete_quiz.php', {
            method: "POST",
            body: JSON.stringify({ quiz_id: quizId }),
            headers: {"Content-Type":"application/json"}
        });
        const data = await res.json();
        if(data.success){
            alert("Quiz deleted successfully!");
            loadQuizzes();
        } else {
            alert(data.message);
        }
    } catch(err){
        console.error(err);
        alert("Error deleting quiz.");
    }
}

window.addEventListener('DOMContentLoaded', loadQuizzes);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
