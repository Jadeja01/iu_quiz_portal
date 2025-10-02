<?php
header('Content-Type: application/json');
include "./connect.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(["success" => false, "message" => "All fields required!"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($password === $row['password']) {
        $_SESSION['id']       = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role']     = $row['role'];

        echo json_encode([
            "success"  => true,
            "message"  => "Login successful!",
            "role"     => $row['role']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid password!"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "User not found!"]);
}

$stmt->close();
$conn->close();
?>
