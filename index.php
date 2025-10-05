<?php
include "./connect.php";
session_start();
if (isset($_SESSION['id'])) {
    $role = $_SESSION['role'] ?? '';
    if ($role === 'admin') {
        header("Location: admin.php");
        exit;
    } elseif ($role === 'user') {
        header("Location: user.php");
        exit;
    } else {
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
$isLoggedIn = isset($_SESSION['id']);
$username   = $_SESSION['username'] ?? '';
$role       = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IU - Quiz Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="styles.css">
<style>
.hero-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 100px 0;
  text-align: center;
}
.hero-section h1 {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1rem;
}
.hero-section p {
  font-size: 1.3rem;
  margin-bottom: 2rem;
}
.btn-custom {
  padding: 12px 40px;
  font-size: 1.1rem;
  border-radius: 50px;
  margin: 10px;
}
.feature-box {
  padding: 30px;
  text-align: center;
  border-radius: 10px;
  background: white;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  margin-bottom: 30px;
  transition: transform 0.3s;
}
.feature-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}
.feature-icon {
  font-size: 3rem;
  color: #667eea;
  margin-bottom: 20px;
}
.feature-box h4 {
  font-weight: 600;
  margin-bottom: 15px;
}
</style>
</head>
<body>

<!-- Navbar -->
<?php include "navbar.php"; ?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <h1>Welcome to IU Quiz Portal</h1>
    <p>Test your knowledge with fun and engaging quizzes!</p>
    <button class="btn btn-light btn-custom" data-bs-toggle="modal" data-bs-target="#loginModal">
      <i class="fas fa-sign-in-alt me-2"></i>Login
    </button>
    <button class="btn btn-outline-light btn-custom" data-bs-toggle="modal" data-bs-target="#signupModal">
      <i class="fas fa-user-plus me-2"></i>Sign Up
    </button>
  </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
  <div class="container">
    <h2 class="text-center mb-5">Why Choose Us?</h2>
    <div class="row">
      <div class="col-md-3">
        <div class="feature-box">
          <div class="feature-icon"><i class="fas fa-brain"></i></div>
          <h4>Smart Quizzes</h4>
          <p>Adaptive questions that match your level</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="feature-box">
          <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
          <h4>Track Progress</h4>
          <p>Monitor your performance easily</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="feature-box">
          <div class="feature-icon"><i class="fas fa-users"></i></div>
          <h4>Compete</h4>
          <p>Challenge with your friends and win</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="feature-box">
          <div class="feature-icon"><i class="fas fa-trophy"></i></div>
          <h4>Earn Rewards</h4>
          <p>Unlock badges and achievements</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Main Content -->
<main class="container my-5" id="quizContainer"></main>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Login</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="loginForm">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" name="username" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Signup Modal -->
<div class="modal fade" id="signupModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Signup</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="signupForm">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" name="username" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select class="form-select" name="role" required>
              <option value="">Select Role</option>
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <button type="submit" class="btn btn-success w-100">Signup</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="script.js?v=1.0"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>