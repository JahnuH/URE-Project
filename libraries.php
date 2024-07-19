<?php
session_start();
include('connection.php');
include('addEventFunction.php');

if (!isset($_SESSION['user'])) {
    echo json_encode(['message' => 'You are not logged in.']);
    exit();
}

// Handle adding a library
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $libraryName = $_POST['library_name'];
    $maxCapacity = $_POST['max_capacity'];

    if (empty($libraryName) || empty($maxCapacity)) {
        echo json_encode(['message' => 'All fields are required.']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO libraries (library_name, max_capacity) VALUES (?, ?)");
    $stmt->bind_param("si", $libraryName, $maxCapacity);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Library added successfully.']);
        addEvent($libraryName . " - New Library Added");
    } else {
        echo json_encode(['message' => 'Failed to add library.']);
    }

    $stmt->close();
}

// Handle retrieving all libraries
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $libraries = [];
    $result = $conn->query("SELECT library_name, max_capacity FROM libraries");

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $libraries[] = [
                'library_name' => $row['library_name'],
                'max_capacity' => $row['max_capacity']
            ];
        }
        echo json_encode(['libraries' => $libraries]);
    } else {
        echo json_encode(['message' => 'No libraries found.']);
    }

    $result->close();
}

// Handle deleting a library
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $libraryName = $_POST['library_name'];

    $stmt = $conn->prepare("DELETE FROM libraries WHERE library_name = ?");
    $stmt->bind_param("s", $libraryName);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Library deleted successfully.']);
        addEvent($libraryName . " - Library Deleted");
    } else {
        echo json_encode(['message' => 'Failed to delete library.']);
    }

    $stmt->close();
}

$conn->close();
?>
