<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

include('connection.php');
include('addEventFunction.php');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
} else {
    if (!isset($_SESSION['first_load']) || $_SESSION['first_load'] === true) {
        addEvent("Logged In");
        $_SESSION['first_load'] = false;
    }
}

$query = "SELECT event_message, event_time FROM events ORDER BY event_time DESC LIMIT 5";
$result = $conn->query($query);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="adminStyles.css">
</head>
<body>
    <div class="sidebar">
        <div>
            <h1>Presidency<br>University</h1>
            <div class="menu">
                <a href="#" id="dashboard-link" class="active" onclick="showTab('dashboard')">Dashboard</a>
                <a href="#" id="records-link" onclick="showTab('records')">Records</a>
                <a href="#" id="analytics-link" onclick="showTab('analytics')">Analytics</a>
                <a href="#" id="settings-link" onclick="showTab('settings')">Settings</a>
            </div>
        </div>
        <button class="logout-button" onclick="logout()">Logout</button>
    </div>
    <div class="container">

        <div id="dashboard-tab" class="tab">
            <h2>Dashboard</h2>

            <!-- Welcome Message and Insights Panel -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h3>Hello!</h3>
                </div>
                <div class="panel-content">
                    <p>Welcome to the Library Admin Panel of Presidency University.</p>
                    <p>Here you can view user entry and exit records, view analytics, and adjust settings.</p>
                </div>
            </div>

            <!-- User Search Panel -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h3>User Search</h3>
                </div>
                <div class="panel-content">
                    <label for="user-id">Enter User ID:</label>
                    <input type="text" id="user-id">
                    <button onclick="searchUser()">Search</button>
                    <div id="user-details">
                        <!-- User details will be displayed here -->
                    </div>
                </div>
            </div>

            <!-- Recent Activity Panel -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h3>Recent Activity</h3>
                </div>
                <div class="panel-content">
                    <ul id="recent-activity-list">
                        <?php foreach ($events as $event): ?>
                            <li>
                                <?php 
                                    $message = htmlspecialchars($event['event_message']);
                                    $timestamp = strtotime($event['event_time']); // Convert to Unix timestamp if not already

                                    // Format the timestamp in 12-hour AM/PM with date
                                    $formatted_time = date('h:i A, F j, Y', $timestamp);
                                ?>
                                <span class="event-message"><?php echo $message . ' at '; ?></span>
                                <span class="event-timestamp"><?php echo $formatted_time; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Quick Links Panel -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h3>Quick Links</h3>
                </div>
                <div class="panel-content">
                    <ul>
                        <li><a href="#" onclick="showTab('records')">View Records</a></li>
                        <li><a href="#" onclick="showTab('analytics')">View Analytics</a></li>
                        <li><a href="#" onclick="showTab('settings')">Change Password</a></li>
                    </ul>
                </div>
            </div>

        </div>
        
        <div id="records-tab" class="tab" style="display: none;">
            <h2>User Entry and Exit Records</h2>
            <div class="filter-container">
                <div class="filter-options">
                    <label for="location-filter">Location:</label>
                    <select id="location-filter">
                        <option value="All">All</option>
                        <option value="Main Library">Main Library</option>
                        <option value="Library-SoM">Library-SoM</option>
                        <option value="Library-SoL">Library-SoL</option>
                    </select>

                    <label for="sort">Sort By:</label>
                    <select id="sort">
                        <option value="latest">New</option>
                        <option value="earliest">Old</option>
                    </select>

                    <label for="records-filter">Filter:</label>
                    <select id="records-filter">
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="custom_range">Custom Range</option>
                    </select>

                    <div id="date-range" style="display: none;">
                        <label for="from_date">From:</label>
                        <input type="date" id="from_date">
                        <label for="to_date">To:</label>
                        <input type="date" id="to_date">
                    </div>
                </div>

                <div>
                    <label for="format">Format:</label>
                    <select id="format">
                        <option value="excel">Excel</option>
                        <option value="pdf">PDF</option>
                    </select>
                    <button onclick="downloadRecords()">Download</button>
                </div>
            </div>

            <table id="recordsTable" class="records-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Entry Time</th>
                        <th>Exit Time</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody id="records-table-body">
                    <!-- Records will be populated here by JavaScript -->
                </tbody>
            </table>
        </div>

        <div id="analytics-tab" class="tab" style="display: none;">
            <h2>Analytics</h2>
            <div class="date-range-selector">
                <label for="analytics-filter">Select Date Range:</label>
                <select id="analytics-filter">
                    <option value="today">Today</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="this_year">This Year</option>
                    <option value="all_time">All Time</option>
                </select>
            </div>
            <div class="analytics-charts">
                <div class="chart-container">
                    <canvas id="entries-over-time-chart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="entry-times-distribution-chart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="average-duration-by-gender-chart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="average-duration-by-category-chart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="user-distribution-by-gender-chart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="user-distribution-by-category-chart"></canvas>
                </div>
            </div>
        </div>
        <div id="settings-tab" class="tab" style="display: none;">
            <h2>Settings</h2>
            <div class="password-change-container">
                <h3>Change Password</h3>
                <label for="user-type">User Type:</label>
                <select id="user-type">
                    <option value="Admin">Admin</option>
                    <option value="User">User</option>
                </select>

                <label for="current-password">Current Password:</label>
                <input type="password" id="current-password" required>

                <label for="new-password">New Password:</label>
                <input type="password" id="new-password" required>

                <button onclick="changePassword()">Change Password</button>

                <div id="password-change-result" class="pass-change-result"></div>
            </div>
        </div>
    </div>

    <script lang="javascript" src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.2/html2pdf.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            fetchRecordsData();
            fetchAnalyticsData();

            document.getElementById('analytics-filter').addEventListener('change', fetchAnalyticsData);
            document.getElementById('records-filter').addEventListener('change', function () {
                const isCustomRange = this.value === 'custom_range';
                document.getElementById('date-range').style.display = isCustomRange ? 'flex' : 'none';
                fetchRecordsData();
            });

            document.getElementById('sort').addEventListener('change', fetchRecordsData);
            document.getElementById('location-filter').addEventListener('change', fetchRecordsData);
            document.getElementById('from_date').addEventListener('change', fetchRecordsData);
            document.getElementById('to_date').addEventListener('change', fetchRecordsData);
        });

        function logout() {
            fetch('logout.php')
                .then(response => {
                    if (response.ok) {
                        window.location.href = 'login.php';
                    }
                });
        }

        function showTab(tabId) {
            document.querySelectorAll('.tab').forEach(tab => tab.style.display = 'none');
            document.getElementById(`${tabId}-tab`).style.display = 'block';
            document.querySelectorAll('.menu a').forEach(link => link.classList.remove('active'));
            document.getElementById(`${tabId}-link`).classList.add('active');

            if (tabId === 'records') {
                fetchRecordsData();
            } else if (tabId === 'analytics') {
                fetchAnalyticsData();
            }
        }

        function searchUser() {
            const userId = document.getElementById('user-id').value;
            
            fetch(`search_user.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    const userDetails = document.getElementById('user-details');
                    if (data && data.length > 0) {
                        const user = data[0];
                        const entryTime = user.entry_time ? new Date(user.entry_time).toLocaleString() : 'Never';

                        userDetails.innerHTML = `
                            <p>User ID: ${user.user_id}</p>
                            <p>Name: ${user.name}</p>
                            <p>Gender: ${user.gender}</p>
                            <p>Category: ${user.category}</p>
                            <p>Last Entry: ${entryTime}</p>
                        `;
                    } else {
                        userDetails.innerHTML = '<p>User not found.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching user details:', error);
                    const userDetails = document.getElementById('user-details');
                    userDetails.innerHTML = '<p>An error occurred while fetching user details.</p>';
                });
        }

        function downloadRecords() {
            const format = document.getElementById('format').value;
            const table = document.getElementById('recordsTable');

            if (format === 'excel') {
                let wb = XLSX.utils.table_to_book(table, {sheet: 'Records'});
                let ws = wb.Sheets['Records'];

                const columnWidths = [];
                XLSX.utils.sheet_to_json(ws, { header: 1 }).forEach((row) => {
                    row.forEach((cell, i) => {
                        const cellWidth = cell.toString().length;
                        if (!columnWidths[i] || cellWidth > columnWidths[i]) {
                            columnWidths[i] = cellWidth;
                        }
                    });
                });

                ws['!cols'] = columnWidths.map((width) => ({ width: width * 1.2 }));

                XLSX.writeFile(wb, 'Records.xlsx');

            } else if (format === 'pdf') {
                var opt = {
                    margin: 0.5,
                    filename: 'Records.pdf',
                    image: { type: 'jpeg', quality: 1 },
                    html2canvas: { 
                        scale: 2,
                        useCORS: true
                    },
                    jsPDF: { 
                        unit: 'in', 
                        format: 'letter', 
                        orientation: 'landscape',
                        putOnlyUsedFonts: true,
                        floatPrecision: 16 
                    }
                };
                html2pdf().from(table).set(opt).save();
            } else {
                console.log('Invalid format selected.');
            }
        }

        function fetchRecordsData() {
            const sort = document.getElementById('sort').value;
            const filter = document.getElementById('records-filter').value;
            const fromDate = document.getElementById('from_date').value;
            const toDate = document.getElementById('to_date').value;
            const locationFilter = document.getElementById('location-filter').value;

            const params = new URLSearchParams({ sort, filter, fromDate, toDate, locationFilter });

            fetch(`fetch_records.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('records-table-body');
                    tableBody.innerHTML = '';

                    data.forEach(record => {
                        const row = document.createElement('tr');
                        const duration = formatDuration(record.duration);
                        row.innerHTML = `
                            <td>${record.user_id}</td>
                            <td>${record.name}</td>
                            <td>${new Date(record.entry_time).toLocaleString()}</td>
                            <td>${record.exit_time ? new Date(record.exit_time).toLocaleString() : '-'}</td>
                            <td>${duration}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                })
                .catch(error => console.error('Error fetching records:', error));
        }

        function formatDuration(duration) {
            if (!duration) return '-';
            const parts = duration.split(':').map(part => parseInt(part));

            let formattedDuration = '';
            if (parts[0] > 0) {
                formattedDuration += `${parts[0]}h `;
            }
            if (parts[1] > 0) {
                formattedDuration += `${parts[1]}m `;
            }
            if (parts[2] > 0 || formattedDuration === '') {
                formattedDuration += `${parts[2]}s`;
            }

            return formattedDuration.trim();
        }

        function fetchAnalyticsData() {
            const filter = document.getElementById('analytics-filter').value;
            let url = `analytics_data.php?filter=${filter}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    
                    renderChart('entries-over-time-chart', {
                        type: 'line',
                        data: {
                            labels: data.entries_over_time.dates,
                            datasets: [{
                                data: data.entries_over_time.counts,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Entries Over Time'
                                },
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Date'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Count'
                                    }
                                }
                            }
                        }
                    });

                    renderChart('entry-times-distribution-chart', {
                        type: 'bar',
                        data: {
                            labels: data.entry_times_distribution.times,
                            datasets: [{
                                data: data.entry_times_distribution.counts,
                                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                borderColor: 'rgba(153, 102, 255, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Entry Times Distribution'
                                },
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Time'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Count'
                                    }
                                }
                            }
                        }
                    });

                    renderChart('average-duration-by-gender-chart', {
                        type: 'bar',
                        data: {
                            labels: data.average_duration_by_gender.genders,
                            datasets: [{
                                data: data.average_duration_by_gender.durations,
                                backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)'],
                                borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Average Duration by Gender'
                                },
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            let duration = tooltipItem.raw;
                                            let hours = Math.floor(duration / 60);
                                            let minutes = Math.floor(duration % 60);
                                            let seconds = Math.round((duration % 1) * 60);

                                            if (seconds === 60) {
                                                minutes++;
                                                seconds = 0;
                                            }

                                            if (hours > 0) {
                                                return `${hours} ${hours > 1 ? 'hours' : 'hour'} ${minutes} ${minutes > 1 ? 'minutes' : 'minute'}`;
                                            } else if (minutes > 0) {
                                                return `${minutes} ${minutes > 1 ? 'minutes' : 'minute'} ${seconds} ${seconds > 1 ? 'seconds' : 'second'}`;
                                            } else {
                                                return `${seconds} ${seconds > 1 ? 'seconds' : 'second'}`;
                                            }
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Gender'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Average Time (minutes)'
                                    }
                                }
                            }
                        }
                    });

                    renderChart('average-duration-by-category-chart', {
                        type: 'bar',
                        data: {
                            labels: data.average_duration_by_category.categories,
                            datasets: [{
                                data: data.average_duration_by_category.durations,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Average Duration by Category'
                                },
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            let duration = tooltipItem.raw;
                                            let hours = Math.floor(duration / 60);
                                            let minutes = Math.floor(duration % 60);
                                            let seconds = Math.round((duration % 1) * 60);

                                            if (seconds === 60) {
                                                minutes++;
                                                seconds = 0;
                                            }

                                            if (hours > 0) {
                                                return `${hours} ${hours > 1 ? 'hours' : 'hour'} ${minutes} ${minutes > 1 ? 'minutes' : 'minute'}`;
                                            } else if (minutes > 0) {
                                                return `${minutes} ${minutes > 1 ? 'minutes' : 'minute'} ${seconds} ${seconds > 1 ? 'seconds' : 'second'}`;
                                            } else {
                                                return `${seconds} ${seconds > 1 ? 'seconds' : 'second'}`;
                                            }
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Category'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Average Time (minutes)'
                                    }
                                }
                            }
                        }
                    });

                    renderChart('user-distribution-by-gender-chart', {
                        type: 'pie',
                        data: {
                            labels: data.user_distribution_by_gender.genders,
                            datasets: [{
                                label: 'User Distribution by Gender',
                                data: data.user_distribution_by_gender.counts,
                                backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)'],
                                borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'User Distribution by Gender'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            return `${tooltipItem.label}: ${tooltipItem.raw}`;
                                        }
                                    }
                                }
                            }
                        }
                    });

                    renderChart('user-distribution-by-category-chart', {
                        type: 'pie',
                        data: {
                            labels: data.user_distribution_by_category.categories,
                            datasets: [{
                                label: 'User Distribution by Category',
                                data: data.user_distribution_by_category.counts,
                                backgroundColor: ['rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)'],
                                borderColor: ['rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'User Distribution by Category'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            return `${tooltipItem.label}: ${tooltipItem.raw}`;
                                        }
                                    }
                                }
                            }
                        }
                    });

                }).catch(error => {
                    console.error('Error fetching data:', error);
                });
        }

        function renderChart(canvasId, chartConfig) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            let existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }
            new Chart(ctx, chartConfig);
        }

        function changePassword() {
            const userType = document.getElementById('user-type').value;
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;

            if (!currentPassword || !newPassword) {
                document.getElementById('password-change-result').innerText = "Please fill out all fields.";
                return;
            }

            const formData = new FormData();
            formData.append('user_type', userType);
            formData.append('current_password', currentPassword);
            formData.append('new_password', newPassword);

            fetch('change_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('password-change-result').innerText = data.message;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('password-change-result').innerText = "An error occurred.";
            });
        }
        
    </script>
</body>
</html>
