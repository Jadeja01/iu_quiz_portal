<?php
session_start();
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
<title>Admin Panel - Quiz Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<?php include "navbar.php"; ?>

<div class="container my-5">
  <h2 class="mb-4">Admin Panel - Manage Quizzes</h2>

  <!-- Add Quiz -->
  <div class="card mb-4 p-3">
    <h4>Add New Quiz</h4>
    <form id="quizForm">
      <div class="mb-3">
        <input type="text" id="quizTitle" class="form-control" placeholder="Quiz Title" required>
      </div>
      <div class="mb-3">
        <textarea id="quizDesc" class="form-control" placeholder="Quiz Description"></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Add Quiz</button>
    </form>
  </div>

  <!-- List Quizzes -->
  <h4>All Quizzes</h4>
  <div id="quizList"></div>
</div>

<script>
// Add quiz
document.getElementById('quizForm').addEventListener('submit', e => {
    e.preventDefault();
    const title = document.getElementById('quizTitle').value;
    const description = document.getElementById('quizDesc').value;

    fetch('api/add_quiz.php', {
        method: 'POST',
        headers:{ 'Content-Type':'application/json' },
        body: JSON.stringify({ title, description })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            alert('✅ Quiz added!');
            loadQuizzes();
        } else {
            alert(data.message || 'Failed to add quiz!');
        }
    });
});

// Load all quizzes
function loadQuizzes(){
    fetch('api/get_quizzes.php')
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('quizList');
        container.innerHTML = '';
        if(data.length === 0){
            container.innerHTML = '<p>No quizzes found.</p>';
            return;
        }
        data.forEach(q => {
            const div = document.createElement('div');
            div.className = 'card mb-2';
            div.innerHTML = `<div class="card-body"><h5>${q.title}</h5><p>${q.description}</p></div>`;
            container.appendChild(div);
        });
    });
}

// Initial load
loadQuizzes();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
