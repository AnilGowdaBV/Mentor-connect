<?php
// Start session to retrieve the logged-in user email (Mentor's email)
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'mentor_connect');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$message = '';
$mentor_email = $_SESSION['email'] ?? ''; // Get mentor's email from session
$mentee_email = ''; // Initialize mentee email

// Check if mentor is logged in
if (empty($mentor_email)) {
    header("Location: mentor_login.php");
    exit();
}

// Fetch mentee's email from mentee_status table based on the logged-in mentor
$sql = "SELECT mentee_email FROM mentee_status WHERE mentor_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mentor_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $mentee_email = $row['mentee_email'];
} else {
    echo "No mentee associated with this mentor.";
    exit();
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $conn->real_escape_string($_POST['message']);
    $sender = 'mentor'; // Hardcoded for mentor sending messages
    // Insert message into chat table with mentor and mentee emails
    $sql = "INSERT INTO chat (message, sender, mentor_email, mentee_email, created_at) 
            VALUES ('$message', '$sender', '$mentor_email', '$mentee_email', NOW())";
    if ($conn->query($sql) === TRUE) {
        // Redirect to the same page to prevent message resubmission on refresh
        header("Location: mentor_chat.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Chat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .chat-container {
            width: 500px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .chat-box {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            padding: 10px;
            background: #f9f9f9;
        }
        .mentor-message {
            text-align: left;
            background-color: #d1e7dd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 10px;
        }
        .mentee-message {
            text-align: right;
            background-color: #f8d7da;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 10px;
        }
        .chat-form {
            display: flex;
            gap: 10px;
        }
        textarea {
            flex: 1;
            resize: none;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <h2>Mentor Chat</h2>
        <div class="chat-box" id="chat-box">
            <?php
            // Fetch messages between mentor and mentee
            $sql = "SELECT * FROM chat WHERE (mentor_email = ? AND mentee_email = ?) OR (mentor_email = ? AND mentee_email = ?) ORDER BY created_at";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $mentor_email, $mentee_email, $mentee_email, $mentor_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $class = ($row['sender'] === 'mentor') ? 'mentor-message' : 'mentee-message';
                    echo "<div class='$class'><p>{$row['message']}</p></div>";
                }
            } else {
                echo "<p>No messages yet.</p>";
            }
            $stmt->close();
            ?>
        </div>
        <form method="POST" class="chat-form">
            <textarea name="message" placeholder="Type your message..." required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
