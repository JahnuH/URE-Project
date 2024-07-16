<?php
include('connection.php');

header('Content-Type: application/json');

$sort = $_GET['sort'] ?? 'latest';
$filter = $_GET['filter'] ?? 'today';
$fromDate = $_GET['fromDate'] ?? '';
$toDate = $_GET['toDate'] ?? '';
$locationFilter = $_GET['locationFilter'] ?? 'All';

// Initialize where clauses and order clause
$whereClauses = [];
$orderClause = 'entry_time DESC'; // Default order by newest

// Handle filtering
switch ($filter) {
    case 'today':
        $whereClauses[] = "DATE(entry_time) = CURDATE()";
        break;
    case 'this_week':
        $monday = date('Y-m-d', strtotime('monday this week'));
        $today = date('Y-m-d');
        $whereClauses[] = "entry_time BETWEEN '$monday 00:00:00' AND '$today 23:59:59'";
        break;
    case 'this_month':
        $whereClauses[] = "MONTH(entry_time) = MONTH(CURDATE()) AND YEAR(entry_time) = YEAR(CURDATE())";
        break;
    case 'custom_range':
        if (!empty($fromDate) && !empty($toDate)) {
            $whereClauses[] = "entry_time BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'";
        }
        break;
}

// Handle location filter
if ($locationFilter !== 'All') {
    $whereClauses[] = "location = '$locationFilter'";
}

// Create the WHERE clause
$whereClause = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Adjust the ORDER BY clause
if ($sort === 'earliest') {
    $orderClause = 'entry_time ASC';
}

// Prepare the final SQL query
$query = "SELECT user_id, name, entry_time, exit_time, duration FROM user_entries $whereClause ORDER BY $orderClause";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

echo json_encode($records);
?>
