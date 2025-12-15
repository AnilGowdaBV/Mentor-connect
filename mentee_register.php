<?php
// Database connection details
$servername = "localhost";
$username = "root";  // Replace with your database username if different
$password = "";      // Replace with your database password if different
$dbname = "mentor_connect";  // Name of your database where 'mentees' table is created

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Registration handling
$registration_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password']; // Original password, not hashed for now

    // Prepare and execute the SQL insert query
    $sql = "INSERT INTO mentees (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $password);

    if ($stmt->execute()) {
        // Set registration success to true
        $registration_success = true;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentee Registration</title>
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
            background: linear-gradient(45deg, #00c6ff, #0072ff);
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

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
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

        .message {
            margin-top: 1.5rem;
            font-size: 1.2rem;
            color: #ffd700;
        }

        /* Style for the success message */
        .success-message {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1 class="title">Mentee Registration</h1>

        <!-- Registration Form -->
        <form action="mentee_register.php" method="POST">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Register">
        </form>

        <!-- Success Message (appears after registration) -->
        <?php if ($registration_success): ?>
            <div class="message success-message">
                Registration successful! Redirecting you to the upload page...
            </div>
            <script>
                setTimeout(function() {
                    // Redirect to the mentee upload page after 3 seconds
                    window.location.href = "mentee_upload.php";
                }, 3000); // 3 seconds
            </script>
        <?php endif; ?>
    </div>

</body>
</html>
