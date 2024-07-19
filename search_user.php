<?php
include('connection.php');

header('Content-Type: application/json');

$user_id = $_GET['user_id'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

if (!empty($user_id) && !empty($from_date) && !empty($to_date)) {
    $queryUser = "SELECT user_id, name, gender, category, MAX(entry_time) AS entry_time 
                  FROM user_entries 
                  WHERE user_id = ? 
                  GROUP BY user_id";

    $stmtUser = $conn->prepare($queryUser);
    $stmtUser->bind_param('s', $user_id);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();

    $userData = $resultUser->fetch_assoc();
    $userDetails = [
        'user' => $userData,
    ];

    $queryEntries = "SELECT entry_time 
                     FROM user_entries 
                     WHERE user_id = ? AND DATE(entry_time) BETWEEN ? AND ? 
                     ORDER BY entry_time DESC";

    $stmtEntries = $conn->prepare($queryEntries);
    $stmtEntries->bind_param('sss', $user_id, $from_date, $to_date);
    $stmtEntries->execute();
    $resultEntries = $stmtEntries->get_result();

    $entries = [];
    while ($row = $resultEntries->fetch_assoc()) {
        $entries[] = $row;
    }
    $userDetails['entries'] = $entries;

    echo json_encode($userDetails);
} else {
    echo json_encode(['error' => 'Missing parameters']);
}
?>
