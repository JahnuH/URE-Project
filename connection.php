<?php
$servername = "localhost";
$username = "root";
$dbname = "koha"; //$dbname = "koha_library"; <-@university
$password = ""; //$password = "mysqlroot"; <-@university

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
