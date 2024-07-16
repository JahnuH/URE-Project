<?php
include('connection.php');

header('Content-Type: application/json');

$user_id = $_GET['user_id'] ?? '';

if (!empty($user_id)) {
    $query = "SELECT user_id, name, gender, category, MAX(entry_time) AS entry_time FROM user_entries WHERE user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $userDetails = [];
    while ($row = $result->fetch_assoc()) {
        $userDetails[] = $row;
    }

    echo json_encode($userDetails);
} else {
    echo json_encode(array());
}
?>
