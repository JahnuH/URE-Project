<?php
session_start();
include('connection.php');

$user = $_POST['user'];
$inputPassword = $_POST['password'];
$location = $_POST['location'];

$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->bind_result($storedPassword);
$stmt->fetch();
$stmt->close();

if ($inputPassword === $storedPassword) {
    $_SESSION['user'] = $user;
    
    if (!empty($location)) {
        $_SESSION['location'] = $location;
    }

    switch ($user) {
        case 'Admin':
            header("Location: admin.php");
            break;
        case 'User':
            header("Location: user.php");
            break;
        default:
            header("Location: login.php");
            break;
    }

    exit();
} else {
    header("Location: login.php?error=incorrect_password");
    exit();
}

$conn->close();
?>
