<?php
session_start();
include 'connection.php';

if (isset($_SESSION['location'])) {
    $userLocation = $_SESSION['location'];
} else {
    $userLocation = "Unknown";
}

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Could not connect: " . mysqli_connect_error());
}

$summary = [];

// Query for total_inside
$sql_total_inside = "SELECT COUNT(*) AS total_inside FROM user_entries WHERE exit_time IS NULL AND location = '$userLocation';";
$result_total_inside = mysqli_query($conn, $sql_total_inside);
if ($result_total_inside) {
    $row = mysqli_fetch_assoc($result_total_inside);
    $summary['total_inside'] = $row['total_inside'];
}

// Query for male_count
$sql_male_count = "SELECT COUNT(*) AS male_count FROM user_entries WHERE exit_time IS NULL AND gender = 'male' AND location = '$userLocation';";
$result_male_count = mysqli_query($conn, $sql_male_count);
if ($result_male_count) {
    $row = mysqli_fetch_assoc($result_male_count);
    $summary['male_count'] = $row['male_count'];
}

// Query for female_count
$sql_female_count = "SELECT COUNT(*) AS female_count FROM user_entries WHERE exit_time IS NULL AND gender = 'female' AND location = '$userLocation';";
$result_female_count = mysqli_query($conn, $sql_female_count);
if ($result_female_count) {
    $row = mysqli_fetch_assoc($result_female_count);
    $summary['female_count'] = $row['female_count'];
}

// Query for student count
$sql_student_count = "SELECT COUNT(*) AS student_count FROM user_entries WHERE exit_time IS NULL AND category = 'student' AND location = '$userLocation';";
$result_student_count = mysqli_query($conn, $sql_student_count);
if ($result_student_count) {
    $row = mysqli_fetch_assoc($result_student_count);
    $summary['student_count'] = $row['student_count'];
}

// Query for faculty_count
$sql_faculty_count = "SELECT COUNT(*) AS faculty_count FROM user_entries WHERE exit_time IS NULL AND category = 'faculty' AND location = '$userLocation';";
$result_faculty_count = mysqli_query($conn, $sql_faculty_count);
if ($result_faculty_count) {
    $row = mysqli_fetch_assoc($result_faculty_count);
    $summary['faculty_count'] = $row['faculty_count'];
}

// Query for scholars_count
$sql_scholars_count = "SELECT COUNT(*) AS scholars_count FROM user_entries WHERE exit_time IS NULL AND category = 'Research Scholar' AND location = '$userLocation';";
$result_scholars_count = mysqli_query($conn, $sql_scholars_count);
if ($result_scholars_count) {
    $row = mysqli_fetch_assoc($result_scholars_count);
    $summary['scholars_count'] = $row['scholars_count'];
}

// Query for total_today
$sql_total_today = "SELECT COUNT(*) AS total_today FROM user_entries WHERE DATE(entry_time) = CURDATE() AND location = '$userLocation';";
$result_total_today = mysqli_query($conn, $sql_total_today);
if ($result_total_today) {
    $row = mysqli_fetch_assoc($result_total_today);
    $summary['total_today'] = $row['total_today'];
}

echo json_encode($summary);

mysqli_close($conn);
?>
