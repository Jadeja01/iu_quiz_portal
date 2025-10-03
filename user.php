<?php
session_start();
include "./connect.php";

// Ensure user is logged in
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
</head>
<body>
<?php include "navbar.php"; ?>

<div class="container my-5">
    <h2>Welcome, <?php echo $_SESSION['username']; ?> 👋</h2>

    <!-- Search Box for Quiz Code -->
    <div class="input-group mb-3">
        <input type="text" id="quizCodeInput" class="form-control" placeholder="Enter Quiz Code">
        <button class="btn btn-primary" onclick="searchQuiz()">Find Quiz</button>
    </div>

    <!-- User's Attempted Quizzes -->
    <h4>Your Attempted Quizzes</h4>
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
            div.className = "card mb-3";
            div.innerHTML = `
                <div class="card-body">
                    <h5>${a.title}</h5>
                    <p>Score: ${a.score}/${a.total}</p>
                </div>
            `;
            container.appendChild(div);
        });
    } else {
        container.innerHTML = "<p>No quizzes attempted yet.</p>";
    }
}

// Search quiz by code
async function searchQuiz(){
    const code = document.getElementById("quizCodeInput").value.trim();
   	console.log(code)
    if(!code){ alert("Enter a quiz code!"); return; }

    const res = await fetch(`/get_quiz_by_code.php?code=${code}`);
    const data = await res.json();
    console.log("quizdata:",data);

    if(data.success){
        // Redirect user to quiz page
        window.location.href = `/quiz.php?code=${data.quiz.quiz_code}`;
    } else {
        alert("Quiz not found!");
    }
}

// Initial Load
loadAttempts();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
