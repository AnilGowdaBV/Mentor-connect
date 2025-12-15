<?php
// Start a session to maintain user state
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mentor_connect";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute query to verify email and password
    $sql = "SELECT * FROM mentors WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a matching record is found
    if ($result->num_rows > 0) {
        // Successful login - store session variables
        $_SESSION['email'] = $email;

        // Redirect to mentor_dashboard.php after login
        echo "<script>
                alert('Login successful!');
                setTimeout(function() {
                    window.location.href = 'mentor_dashboard.php';
                }, 1000); // Delay of 1 second
              </script>";
    } else {
        // Login failed - show an error alert
        echo "<script>alert('Invalid email or password');</script>";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Login</title>
    <style>
        /* Reset and Global Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(45deg, #ff6a00, #ee0979);
            color: #fff;
        }

        .container {
            background: rgba(0, 0, 0, 0.8);
            padding: 3rem 4rem;
            border-radius: 15px;
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.3);
            width: 400px;
            max-width: 100%;
            text-align: center;
        }

        .title {
            font-size: 2rem;
            color: #fff;
            margin-bottom: 1.5rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        label {
            font-size: 1rem;
            color: #f7f7f7;
            display: block;
            margin-bottom: 0.6rem;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            border: 1px solid #fff;
            border-radius: 5px;
            background-color: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 1rem;
        }

        input[type="submit"] {
            width: 100%;
            padding: 0.8rem;
            font-size: 1.1rem;
            background-color: #28a745;
            border: none;
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .signup-container {
            margin-top: 1rem;
        }

        .signup-text {
            color: #ffd700;
            font-size: 1rem;
        }

        .signup-link {
            color: #ffd700;
            font-size: 1rem;
            text-decoration: none;
            font-weight: bold;
        }

        .signup-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Main Content -->
    <div class="container">
        <h1 class="title">Mentor Login</h1>
        <form action="mentor_login.php" method="POST">
            <!-- Email -->
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <!-- Password -->
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <!-- Submit Button -->
            <input type="submit" value="Login">
        </form>

        <!-- Sign Up Statement and Link -->
        <div class="signup-container">
            <p class="signup-text">Don't have an account? <a href="mentor_register.php" class="signup-link">Sign up here</a></p>
        </div>
    </div>

</body>
</html>
