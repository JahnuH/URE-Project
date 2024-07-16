<?php
include('connection.php');

function addEvent($message) {
    global $conn;

    $query = "INSERT INTO events (event_message) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $message);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
?>
