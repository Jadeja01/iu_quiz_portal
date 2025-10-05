<!-- navbar.php -->
<?php
session_start();
$isLoggedIn = isset($_SESSION['id']);
$username   = $_SESSION['username'] ?? '';
$role       = $_SESSION['role'] ?? '';
?>
<style>
.navbar-custom {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1rem 0;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.navbar-brand {
  font-weight: 700;
  font-size: 1.5rem;
  letter-spacing: 0.5px;
}
.user-info {
  display: flex;
  align-items: center;
  background: rgba(255,255,255,0.15);
  padding: 8px 20px;
  border-radius: 50px;
  margin-right: 15px;
  backdrop-filter: blur(10px);
}
.user-avatar {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 10px;
  color: #667eea;
  font-weight: 600;
}
.btn-nav {
  padding: 10px 25px;
  border-radius: 50px;
  font-weight: 600;
  border: none;
  transition: all 0.3s;
}
.btn-nav:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="/">
      <i class="fas fa-graduation-cap me-2"></i>IU Quiz Portal
    </a>
    <div class="d-flex align-items-center gap-2">
      <?php if ($isLoggedIn): ?>
        <div class="user-info text-white">
          <div class="user-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
          <span><?php echo htmlspecialchars($username); ?></span>
        </div>
        <a href="./logout.php" class="btn btn-light btn-nav">
          <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
      <?php else: ?>
        <button class="btn btn-light btn-nav" data-bs-toggle="modal" data-bs-target="#loginModal">
          <i class="fas fa-sign-in-alt me-2"></i>Login
        </button>
        <button class="btn btn-warning btn-nav" data-bs-toggle="modal" data-bs-target="#signupModal">
          <i class="fas fa-user-plus me-2"></i>Sign Up
        </button>
      <?php endif; ?>
    </div>
  </div>
</nav>