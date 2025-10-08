<?php
session_start();
include "./connect.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$quiz_id = $_GET['quiz_id'] ?? '';

if (!$quiz_id) {
    echo "<script>alert('No quiz selected!'); window.location.href='admin.php';</script>";
    exit;
}

// Fetch quiz title
$qTitle = "Unknown Quiz";
$q = $conn->prepare("SELECT title FROM quizzes WHERE id = ?");
$q->bind_param("i", $quiz_id);
$q->execute();
$q->bind_result($qTitle);
$q->fetch();
$q->close();

// Fetch submissions
$stmt = $conn->prepare("
    SELECT s.id, u.username, s.score, s.total, s.submitted_at
    FROM submission s
    JOIN users u ON s.user_id = u.id
    WHERE s.quiz_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submissions - <?php echo htmlspecialchars($qTitle); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
.table-hover tbody tr:hover {
    background-color: #f1f5f9;
}
.page-header {
    margin: 30px 0;
}
.badge-score {
    font-size: 0.9rem;
    padding: 5px 8px;
}
</style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container mt-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-users me-2"></i>Submissions - <?php echo htmlspecialchars($qTitle); ?></h2>
        <a href="admin.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Score</th>
                        <th>Total</th>
                        <th>Percentage</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 1;
                    while ($row = $result->fetch_assoc()): 
                        $percent = round(($row['score'] / $row['total']) * 100, 2);
                    ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><span class="badge bg-success badge-score"><?php echo $row['score']; ?></span></td>
                        <td><?php echo $row['total']; ?></td>
                        <td><?php echo $percent; ?>%</td>
                        <td><i class="far fa-clock me-1"></i><?php echo $row['submitted_at']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info mt-4 text-center">
            <i class="fas fa-info-circle me-2"></i>No submissions yet for this quiz.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $stmt->close(); ?>
