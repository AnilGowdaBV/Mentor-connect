<?php
// Start session for user authentication
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mentor_connect";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if mentor is logged in
if (!isset($_SESSION['email'])) {
    header("Location: mentor_login.php");
    exit();
}

$mentor_email = $_SESSION['email'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mentee_email = $_POST['mentee_email']; // Mentee's email
    $meeting_date = $_POST['meeting_date'];
    $meeting_time = $_POST['meeting_time'] . ' ' . $_POST['am_pm']; // Combine time and AM/PM
    $meeting_notes = $_POST['meeting_notes'];
    $meeting_link = $_POST['meeting_link'];

    // Insert data into meetings table
    $stmt = $conn->prepare("INSERT INTO meetings (mentee_email, mentor_email, meeting_date, meeting_time, meeting_notes, meeting_link) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $mentee_email, $mentor_email, $meeting_date, $meeting_time, $meeting_notes, $meeting_link);

    if ($stmt->execute()) {
        // Redirect to mentor_dashboard.php after successful scheduling
        header("Location: mentor_dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
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
    <title>Schedule Meeting</title>
    <style>
        /* Add your unique and customized styles here */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
            display: block;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .time-switch {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .time-switch button {
            padding: 5px 15px;
            margin-right: 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .time-switch button:hover {
            opacity: 0.9;
        }
        .submit-btn {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Schedule a Meeting</h1>
    <form method="POST" action="schedule.php">
        <!-- Mentee Email -->
        <div class="form-group">
            <label for="mentee_email">Mentee Email</label>
            <input type="email" id="mentee_email" name="mentee_email" placeholder="Enter mentee's email" required>
        </div>

        <!-- Meeting Date -->
        <div class="form-group">
            <label for="meeting_date">Meeting Date</label>
            <input type="date" id="meeting_date" name="meeting_date" required>
        </div>
        
        <!-- Meeting Time -->
        <div class="form-group">
            <label for="meeting_time">Meeting Time</label>
            <input type="time" id="meeting_time" name="meeting_time" required>
        </div>

        <!-- AM/PM Switch -->
        <div class="form-group time-switch">
            <label></label>
            <button type="button" id="am" onclick="switchAM()">AM</button>
            <button type="button" id="pm" onclick="switchPM()">PM</button>
            <input type="hidden" name="am_pm" id="am_pm" value="AM">
        </div>

        <!-- Meeting Notes -->
        <div class="form-group">
            <label for="meeting_notes">Meeting Notes</label>
            <textarea id="meeting_notes" name="meeting_notes" rows="4" placeholder="Add any notes or details about the meeting..."></textarea>
        </div>

        <!-- Meeting Link -->
        <div class="form-group">
            <label for="meeting_link">Meeting Link (e.g., Zoom/Google Meet)</label>
            <input type="url" id="meeting_link" name="meeting_link" placeholder="Enter meeting link" required>
        </div>

        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" class="submit-btn">Schedule Meeting</button>
        </div>
    </form>
</div>

<script>
    // Default to AM on page load
    let isAM = true;

    function switchAM() {
        document.getElementById('am_pm').value = "AM";
        document.getElementById('am').style.backgroundColor = "#3498db";
        document.getElementById('pm').style.backgroundColor = "#ccc";
        isAM = true;
    }

    function switchPM() {
        document.getElementById('am_pm').value = "PM";
        document.getElementById('am').style.backgroundColor = "#ccc";
        document.getElementById('pm').style.backgroundColor = "#3498db";
        isAM = false;
    }
</script>

</body>
</html>
