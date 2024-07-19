<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Gate Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: url('uni_logo.jpg') no-repeat center center fixed;
            background-size: 100% 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            width: 300px;
            text-align: center;
        }
        .form-input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .form-submit {
            width: 100%;
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 12px 20px;
            text-decoration: none;
            display: inline-block;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .form-submit:hover {
            background-color: #05668D;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        footer {
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #007BFF;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php
        if (isset($_GET['error']) && $_GET['error'] == 'incorrect_password') {
            echo '<p class="error-message">Incorrect username or password. Please try again.</p>';
        }
        ?>
        <form action="login_verify.php" method="POST">
            <select name="user" id="user" class="form-input" onchange="toggleLocationSelect()" required>
                <option value="">Select User</option>
                <option value="Admin">Admin</option>
                <option value="User">User</option>
            </select><br>
            <input type="password" name="password" placeholder="Password" class="form-input" required><br>
            <select name="location" id="location" class="form-input" disabled>
                <option value="">Select Location</option>
            </select><br>
            <button type="submit" class="form-submit">Login</button>
        </form>
    </div>
    <footer>
        &copy; 2024 Presidency University Library Gate Register
    </footer>

    <script>
        function toggleLocationSelect() {
            var userSelect = document.getElementById('user');
            var locationSelect = document.getElementById('location');

            if (userSelect.value === 'User') {
                locationSelect.disabled = false;
                locationSelect.required = true;
                populateLibraryList(); // Call function to populate location list
            } else {
                locationSelect.disabled = true;
                locationSelect.required = false;
                locationSelect.innerHTML = '<option value="">Select Location</option>'; // Reset location dropdown
            }
        }

        function populateLibraryList() {
            // Using fetch to get the dropdown options from PHP file
            fetch('loginLibList.php')
              .then(response => response.text())
              .then(data => {
                document.getElementById('location').innerHTML = data;
              })
              .catch(error => console.error('Error fetching libraries:', error));
        }

        // Initial population of library list on page load
        document.addEventListener('DOMContentLoaded', function() {
            populateLibraryList();
        });
    </script>

</body>
</html>
