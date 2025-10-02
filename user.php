<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

// Optional: restrict admin access
if ($_SESSION['role'] === 'admin') {
    header("Location: admin.php");
    exit;
}
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

<!-- Navbar -->
<?php include "navbar.php"; ?>

<div class="container my-5">
  <h2 class="mb-4">Available Quizzes</h2>
  <div id="quizContainer"></div>
</div>

<script>
// Load quizzes for user
function loadQuizzes() {
    fetch('api/get_quizzes.php')
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('quizContainer');
        container.innerHTML = '';
        if(data.length === 0){
            container.innerHTML = '<p>No quizzes available.</p>';
            return;
        }
        data.forEach(q => {
            const card = document.createElement('div');
            card.className = 'card mb-3';
            card.innerHTML = `
                <div class="card-body">
                    <h5>${q.title}</h5>
                    <p>${q.description}</p>
                    <button class="btn btn-success" onclick="startQuiz(${q.id}, '${q.title}')">Start Quiz</button>
                </div>
            `;
            container.appendChild(card);
        });
    });
}

// Start quiz function (redirect or alert)
function startQuiz(id, title){
    alert(`Starting Quiz: ${title} (ID: ${id})`);
    // You can redirect to a quiz page, e.g., quiz.php?id=${id}
}

// Initial load
loadQuizzes();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
