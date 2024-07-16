<?php
session_start();
include('connection.php');
include('addEventFunction.php');

if (!isset($_SESSION['user'])) {
    echo json_encode(['message' => 'You are not logged in.']);
    exit();
}

$userType = $_POST['user_type'];
$currentPassword = $_POST['current_password'];
$newPassword = $_POST['new_password'];

if (empty($userType) || empty($currentPassword) || empty($newPassword)) {
    echo json_encode(['message' => 'All fields are required.']);
    exit();
}

$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $userType);
$stmt->execute();
$stmt->bind_result($storedPassword);
$stmt->fetch();
$stmt->close();

if ($currentPassword !== $storedPassword) {
    echo json_encode(['message' => 'Current password is incorrect.']);
    exit();
}

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $newPassword, $userType);
if ($stmt->execute()) {
    echo json_encode(['message' => 'Password changed successfully.']);
    addEvent($userType . " password changed");
} else {
    echo json_encode(['message' => 'Failed to change password.']);
}
$stmt->close();
$conn->close();
?>
