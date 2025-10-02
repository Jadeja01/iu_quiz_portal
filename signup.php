<?php
header('Content-Type: application/json');
include "./connect.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$role     = $data['role'] ?? '';

if (!$username || !$password || !$role) {
    echo json_encode(["success" => false, "message" => "All fields required!"]);
    exit;
}

// Check if username exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Username already exists!"]);
    exit;
}

// Insert user
$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $password, $role);

if ($stmt->execute()) {
    // Auto-login after signup
    $_SESSION['id']       = $conn->insert_id;
    $_SESSION['username'] = $username;
    $_SESSION['role']     = $role;

    echo json_encode([
        "success" => true,
        "message" => "Signup successful!",
        "role"    => $role
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Signup failed!"]);
}

$stmt->close();
$conn->close();
?>
