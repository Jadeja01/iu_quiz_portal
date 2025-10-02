<!-- navbar.php -->
<?php
session_start();
$isLoggedIn = isset($_SESSION['id']);
$username   = $_SESSION['username'] ?? '';
$role       = $_SESSION['role'] ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">IU - Quiz Portal</a>
    <div class="d-flex justify-content-end gap-3">
      <?php if ($isLoggedIn): ?>
        <div class="text-light me-2" style="display : grid;align-items : center">Hello, <?php echo htmlspecialchars($username); ?></div>
        <a href="./logout.php" class="btn btn-light">Logout</a>
      <?php else: ?>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#signupModal">Signup</button>
      <?php endif; ?>
    </div>
  </div>
</nav>
