<?php
session_start();
include 'connection.php';
include 'mapCategory.php';

date_default_timezone_set('Asia/Kolkata');

$userName = "";
$userIdNo = "";
$userImage = "";
$userScan = "";
$userDepartment = "";
$userCategory = "";
$userGender = "";
$userLocation = "";
$userAction = "";

$inputSubmitted = isset($_GET['idInput']);

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['location'])) {
    $userLocation = $_SESSION['location'];
} else {
    $userLocation = "Unknown";
}

if ($inputSubmitted) {

    $userIdNo = $_GET['idInput'];

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("Could not connect: " . mysqli_connect_error());
    }

    $sql = "SELECT name, image, department, gender FROM students WHERE roll_number='$userIdNo'";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Invalid query: " . mysqli_error($conn));
    }

    $data = mysqli_fetch_assoc($result);

    if ($data) {
        
        $userNotFound = false;
        $userScan = date("g:i A");
        $userName = $data['name'];
        $userImage = base64_encode($data['image']);
        $userDepartment = $data['department'];
        $userCategory = 'unknown';
        $userGender = $data['gender'];

        try {
            list($userDepartment, $userCategory) = mapDepartmentToCategoryAndType($userDepartment);
        } catch (Exception $e) {
            // Handle the exception as needed
        }

        // Check user status
        $sqlStatus = "SELECT inside, last_update FROM user_status WHERE user_id='$userIdNo'";
        $resultStatus = mysqli_query($conn, $sqlStatus);
        $statusData = mysqli_fetch_assoc($resultStatus);

        if ($statusData) {
            if ($statusData['inside']) {
                // User is inside, update to exit
                $sqlUpdateStatus = "UPDATE user_status SET inside=0, last_update=NOW() WHERE user_id='$userIdNo'";
                mysqli_query($conn, $sqlUpdateStatus);

                // Calculate duration
                $entryTime = strtotime($statusData['last_update']);
                $exitTime = time();
                $duration = $exitTime - $entryTime;

                $hours = floor($duration / 3600);
                $minutes = floor(($duration / 60) % 60);
                $seconds = $duration % 60;

                $hoursFormatted = str_pad($hours, 2, '0', STR_PAD_LEFT);
                $minutesFormatted = str_pad($minutes, 2, '0', STR_PAD_LEFT);
                $secondsFormatted = str_pad($seconds, 2, '0', STR_PAD_LEFT);

                $userAction = "exit";
                $userDuration = "$hoursFormatted:$minutesFormatted:$secondsFormatted";

                // Update the exit time in user_entries table
                $sqlUpdateEntry = "UPDATE user_entries SET exit_time=NOW(), duration=TIMEDIFF(exit_time, entry_time) WHERE user_id='$userIdNo' AND exit_time IS NULL";
                mysqli_query($conn, $sqlUpdateEntry);
            } else {
                // User is outside, update to enter
                $sqlUpdateStatus = "UPDATE user_status SET inside=1, last_update=NOW() WHERE user_id='$userIdNo'";
                mysqli_query($conn, $sqlUpdateStatus);

                $userAction = "entry";

                // Insert a new record in user_entries table for entry
                $sqlInsertEntry = "INSERT INTO user_entries (user_id, name, gender, category, user_type, entry_time, location) VALUES ('$userIdNo', '$userName', '$userGender', '$userCategory', '$userDepartment', NOW(), '$userLocation')";
                mysqli_query($conn, $sqlInsertEntry);
            }
        } else {
            // User not found in user_status, insert new record
            $sqlInsertStatus = "INSERT INTO user_status (user_id, inside, last_update)
                VALUES ('$userIdNo', 1, NOW())";
            mysqli_query($conn, $sqlInsertStatus);

            $userAction = "entry";

            // Insert a new record in user_entries table for first entry
            $sqlInsertEntry = "INSERT INTO user_entries (user_id, name, gender, category, user_type, entry_time, location) VALUES ('$userIdNo', '$userName', '$userGender', '$userCategory', '$userDepartment', NOW(), '$userLocation')";
            mysqli_query($conn, $sqlInsertEntry);
        }
    } else {
        $userNotFound = true;
    }

    mysqli_close($conn);

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library In/Out Register</title>
    <link rel="stylesheet" href="userStyles.css">
</head>
<body>
    <div class="logo-panel">
        <img src="lib_logo.jpg" alt="Logo" class="logo">
        <button class="logout-button" onclick="logout()">Logout</button>
    </div>
    <div class="panels">
        <div class="panel left-panel">
            <div class="clock-panel">
                <h2 class="clock" id="clock"></h2>
            </div>
            <div class="summary-panel">
                <table class="summary-table">
                    <tr>
                        <td>Total Inside</td>
                        <td id="totalInside">0</td>
                    </tr>
                    <tr>
                        <td>Male</td>
                        <td id="maleCount">0</td>
                    </tr>
                    <tr>
                        <td>Female</td>
                        <td id="femaleCount">0</td>
                    </tr>
                    <tr>
                        <td>Students</td>
                        <td id="studentCount">0</td>
                    </tr>
                    <tr>
                        <td>Faculty</td>
                        <td id="facultyCount">0</td>
                    </tr>
                    <tr>
                        <td>Research Scholars</td>
                        <td id="researchScholarCount">0</td>
                    </tr>
                    <tr>
                        <td>Total Today</td>
                        <td id="totalToday">0</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="panel middle-panel">
            <div id="formCollection" class="form-panel">
                <h1>Scan or Enter ID</h1>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
                    <input type='text' name='idInput' autofocus="autofocus" placeholder="Enter ID Number">
                </form>
            </div>
            <div id="userCollection" class="userInfo-panel" style="display: none;">
                <h2>Welcome to PU Library,</h2>
                <h1><?php echo $userName; ?></h1>
                <p>Roll No: <?php echo $userIdNo; ?></p>
                <p>Department: <?php echo $userDepartment; ?></p>
                <p>Entry Time: <span id="userScan"><?php echo $userScan; ?></span></p>
            </div>
        </div>
        <div class="panel right-panel">
            <img id="userCollectionImg" src="data:image/jpeg;base64,<?php echo $userImage; ?>" style="display: none;">
        </div>
    </div>

    <script>

        function updateClock() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            var time = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
            document.getElementById('clock').innerHTML = time;
        }
        updateClock();
        setInterval(updateClock, 1000);

        function toggleUserDetails() {
            var formCollection = document.getElementById("formCollection");
            var userCollection = document.getElementById("userCollection");
            var userCollectionImg = document.getElementById("userCollectionImg");
            var userAction = "<?php echo $userAction; ?>";
            var userDuration = "<?php echo $userDuration ?? ''; ?>";

            formCollection.style.display = "none";
            userCollection.style.display = "block";
            userCollectionImg.style.display = "block";
            
            if (userAction === "exit") {
                document.getElementById("userCollection").innerHTML = `
                    <h2>Thanks for visiting PU Library,</h2>
                    <h1><?php echo $userName; ?></h1>
                    <p>Roll No: <?php echo $userIdNo; ?></p>
                    <p>Department: <?php echo $userDepartment; ?></p>
                    <p>Exit Time: <span id="userScan"><?php echo $userScan; ?></span></p>
                    <p>Duration: ${userDuration}</p>
                `;
            } else {
                document.getElementById("userCollection").innerHTML = `
                    <h2>Welcome to PU Library,</h2>
                    <h1><?php echo $userName; ?></h1>
                    <p>Roll No: <?php echo $userIdNo; ?></p>
                    <p>Department: <?php echo $userDepartment; ?></p>
                    <p>Entry Time: <span id="userScan"><?php echo $userScan; ?></span></p>
                `;
            }
            
            userCollection.style.animation = "flash 0.5s 2";

            setTimeout(function() {
                formCollection.style.display = "block";
                userCollection.style.display = "none";
                userCollectionImg.style.display = "none";
            }, 5000);
        }

        // Update summary table with latest values from database
        function updateSummaryTable() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "get_summary.php", true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var summary = JSON.parse(xhr.responseText);
                    document.getElementById("totalInside").textContent = summary.total_inside;
                    document.getElementById("maleCount").textContent = summary.male_count;
                    document.getElementById("femaleCount").textContent = summary.female_count;
                    document.getElementById("studentCount").textContent = summary.student_count;
                    document.getElementById("facultyCount").textContent = summary.faculty_count;
                    document.getElementById("researchScholarCount").textContent = summary.scholars_count;
                    document.getElementById("totalToday").textContent = summary.total_today;
                }
            };
            xhr.send();
        }
        updateSummaryTable();

        <?php
            if ($inputSubmitted === true && $userNotFound === false) {
                echo 'toggleUserDetails();';
                echo 'updateSummaryTable();';
            } else if ($inputSubmitted === true && $userNotFound === true) {
                echo 'console.log("User Not Found!");';
            }
        ?>

        function logout() {
            fetch('logout.php')
                .then(response => {
                    if (response.ok) {
                        window.location.href = 'login.php';
                    }
                });
        }

    </script>

</body>
</html>
