<?php
// Start the session
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mentor_connect";  // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define target directory for uploads
$profile_picture_target_dir = "uploads/profile_pictures/";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $mentee_name = $_POST['name'];
    $mentee_email = $_POST['email'];
    $mentee_phone = $_POST['phone'];
    $mentee_area_of_interest = $_POST['area_of_interest'];

    // Handle file upload
    $profile_picture = $_FILES['profile_picture'];
    $target_file = $profile_picture_target_dir . time() . "_" . basename($profile_picture['name']); // Adding timestamp to make unique

    // Check if the upload directory exists, if not, create it
    if (!file_exists($profile_picture_target_dir)) {
        mkdir($profile_picture_target_dir, 0777, true); // Create directory with write permissions
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($profile_picture['tmp_name'], $target_file)) {
        // Prepare the SQL query to insert the mentee's data into the database
        $sql = "INSERT INTO mentees_info (name, email, phone, area_of_interest, profile_picture)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $mentee_name, $mentee_email, $mentee_phone, $mentee_area_of_interest, $target_file);

        // Execute the query and check for success
        if ($stmt->execute()) {
            // Redirect to mentee login page after successful upload
            header("Location: mentee_login.php");
            exit(); // Make sure to exit after header redirection
        } else {
            echo "<script>alert('Error saving information: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Error uploading file!');</script>";
    }
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Mentee Information</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Open+Sans:wght@300&display=swap" rel="stylesheet">
    <style>
        /* Styling */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: #f8f9fa; padding: 40px; }
        .container { background-color: #ffffff; padding: 40px; border-radius: 10px; box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1); max-width: 800px; margin: 0 auto; animation: fadeIn 1s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .header { text-align: center; font-size: 2rem; color: #333; margin-bottom: 30px; font-family: 'Open Sans', sans-serif; }
        label { display: block; margin-bottom: 10px; font-size: 1.1rem; color: #495057; font-weight: 500; }
        input[type="text"], input[type="email"], input[type="tel"], textarea, input[type="file"] { width: 100%; padding: 15px; margin: 12px 0 25px; border-radius: 8px; border: 2px solid #ddd; font-size: 1rem; transition: all 0.3s; }
        input[type="text"]:focus, input[type="email"]:focus, textarea:focus, input[type="file"]:focus { border: 2px solid #007bff; outline: none; }
        textarea { resize: vertical; }
        .submit-btn { width: 100%; padding: 15px; background-color: #007bff; color: #fff; border: none; border-radius: 8px; font-size: 1.2rem; cursor: pointer; transition: background-color 0.3s; }
        .submit-btn:hover { background-color: #0056b3; }
        .footer { text-align: center; margin-top: 40px; color: #6c757d; }
        .file-input { padding: 10px 0; background-color: #f1f3f5; }
        .file-input input { cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <h1 class="header">Upload Mentee Information</h1>

    <form action="mentee_upload.php" method="POST" enctype="multipart/form-data">
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" placeholder="Enter your email address" required>
        <label for="phone">Phone Number:</label>
        <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
        <label for="area_of_interest">Area of Interest (Topic for Mentorship):</label>
        <input type="text" id="area_of_interest" name="area_of_interest" placeholder="Enter the topic you want mentoring in" required>
        <label for="profile_picture" class="file-input">Upload Profile Picture:</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
        <button type="submit" class="submit-btn">Upload Information</button>
    </form>
</div>

<div class="footer">
    <p>Empowering Mentees, Shaping Futures</p>
</div>

</body>
</html>
