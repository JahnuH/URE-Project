<?php
include 'connection.php';

$filter = $_GET['filter'] ?? 'today';

switch ($filter) {
    case 'this_week':
        $startDate = date('Y-m-d', strtotime('monday this week'));
        $endDate = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'this_month':
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        break;
    case 'this_year':
        $startDate = date('Y-01-01');
        $endDate = date('Y-12-31');
        break;
    case 'all_time':
        $startDate = '1970-01-01';
        $endDate = date('Y-m-d');
        break;
    case 'today':
    default:
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        break;
}

$data = [];

// Entries Over Time
$entriesOverTimeQuery = "SELECT DATE(entry_time) as date, COUNT(*) as count 
                         FROM user_entries 
                         WHERE DATE(entry_time) BETWEEN '$startDate' AND '$endDate' 
                         GROUP BY DATE(entry_time)";
$entriesOverTimeResult = mysqli_query($conn, $entriesOverTimeQuery);
$dates = [];
$counts = [];
while ($row = mysqli_fetch_assoc($entriesOverTimeResult)) {
    $dates[] = $row['date'];
    $counts[] = $row['count'];
}
$data['entries_over_time'] = ['dates' => $dates, 'counts' => $counts];

// Entry Times Distribution
$entryTimesDistributionQuery = "SELECT HOUR(entry_time) as hour, COUNT(*) as count 
                                FROM user_entries 
                                WHERE DATE(entry_time) BETWEEN '$startDate' AND '$endDate' 
                                GROUP BY HOUR(entry_time)";
$entryTimesDistributionResult = mysqli_query($conn, $entryTimesDistributionQuery);
$times = [];
$counts = [];
while ($row = mysqli_fetch_assoc($entryTimesDistributionResult)) {
    $times[] = $row['hour'] . ':00';
    $counts[] = $row['count'];
}
$data['entry_times_distribution'] = ['times' => $times, 'counts' => $counts];

// Average Duration by Gender
$avgDurationByGenderQuery = "SELECT gender, AVG(TIMESTAMPDIFF(SECOND, entry_time, exit_time)) as avg_duration 
                             FROM user_entries 
                             WHERE DATE(entry_time) BETWEEN '$startDate' AND '$endDate' AND exit_time IS NOT NULL 
                             GROUP BY gender";
$avgDurationByGenderResult = mysqli_query($conn, $avgDurationByGenderQuery);
$genders = [];
$durations = [];
while ($row = mysqli_fetch_assoc($avgDurationByGenderResult)) {
    $genders[] = $row['gender'];
    $durations[] = round($row['avg_duration'] / 60, 2); // Duration in minutes
}
$data['average_duration_by_gender'] = ['genders' => $genders, 'durations' => $durations];

// Average Duration by Category
$avgDurationByCategoryQuery = "SELECT category, AVG(TIMESTAMPDIFF(SECOND, entry_time, exit_time)) as avg_duration 
                               FROM user_entries 
                               WHERE DATE(entry_time) BETWEEN '$startDate' AND '$endDate' AND exit_time IS NOT NULL 
                               GROUP BY category";
$avgDurationByCategoryResult = mysqli_query($conn, $avgDurationByCategoryQuery);
$categories = [];
$durations = [];
while ($row = mysqli_fetch_assoc($avgDurationByCategoryResult)) {
    $categories[] = $row['category'];
    $durations[] = round($row['avg_duration'] / 60, 2); // Duration in minutes
}
$data['average_duration_by_category'] = ['categories' => $categories, 'durations' => $durations];

// User Distribution by Gender
$userDistributionByGenderQuery = "SELECT gender, COUNT(*) as count 
                                  FROM user_entries 
                                  WHERE DATE(entry_time) BETWEEN '$startDate' AND '$endDate' 
                                  GROUP BY gender";
$userDistributionByGenderResult = mysqli_query($conn, $userDistributionByGenderQuery);
$genders = [];
$counts = [];
while ($row = mysqli_fetch_assoc($userDistributionByGenderResult)) {
    $genders[] = $row['gender'];
    $counts[] = $row['count'];
}
$data['user_distribution_by_gender'] = ['genders' => $genders, 'counts' => $counts];

// User Distribution by Category
$userDistributionByCategoryQuery = "SELECT category, COUNT(*) as count 
                                    FROM user_entries 
                                    WHERE DATE(entry_time) BETWEEN '$startDate' AND '$endDate' 
                                    GROUP BY category";
$userDistributionByCategoryResult = mysqli_query($conn, $userDistributionByCategoryQuery);
$categories = [];
$counts = [];
while ($row = mysqli_fetch_assoc($userDistributionByCategoryResult)) {
    $categories[] = $row['category'];
    $counts[] = $row['count'];
}
$data['user_distribution_by_category'] = ['categories' => $categories, 'counts' => $counts];

echo json_encode($data);
?>
