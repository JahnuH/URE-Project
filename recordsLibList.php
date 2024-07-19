<?php
include('connection.php');

// Query to fetch library names
$sql = "SELECT library_name FROM libraries";
$result = $conn->query($sql);

// Begin generating HTML options
$options = '';

// Generate options based on query result
$options .= '<option value="All">All</option>'; // Default option

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $options .= '<option value="' . htmlspecialchars($row['library_name']) . '">' . htmlspecialchars($row['library_name']) . '</option>';
    }
} else {
    $options .= '<option value="">No libraries found</option>';
}

// Close MySQL connection
$conn->close();

// Return generated options
echo $options;
?>
