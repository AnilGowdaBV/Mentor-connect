<?php
// Start session
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mentor_connect";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$name = 'N/A';
$phone = 'N/A';
$email = 'N/A';
$area_of_interest = 'N/A';
$profile_picture = 'https://via.placeholder.com/120'; // Default profile picture
$message = ''; // For displaying request status
$message_class = ''; // For applying a message class (e.g., success, error)

// Fetch mentee profile only if logged in
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Query to fetch mentee profile data
    $sql = "SELECT * FROM mentees_info WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['name'] ?? $name;
        $phone = $row['phone'] ?? $phone;
        $email = $row['email'] ?? $email;
        $area_of_interest = $row['area_of_interest'] ?? $area_of_interest;
        $profile_picture = $row['profile_picture'] ?? $profile_picture;
    }

    $stmt->close();
} else {
    header("Location: mentee_login.php");
    exit();
}

// Handle sending a request
if (isset($_POST['send_request'])) {
    $mentor_email = $_POST['mentor_email'];
    $mentee_email = $email;

    // Check if a request exists with a status of "Rejected"
    $check_sql = "SELECT * FROM mentee_requests WHERE mentee_email = ? AND mentor_email = ? AND status = 'Rejected'";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $mentee_email, $mentor_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Allow resending a request for rejected cases
        $update_sql = "UPDATE mentee_requests SET status = 'Pending' WHERE mentee_email = ? AND mentor_email = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ss", $mentee_email, $mentor_email);

        if ($stmt->execute()) {
            $message = "Request resent successfully!";
            $message_class = "success";
        } else {
            $message = "Failed to resend the request.";
            $message_class = "error";
        }
    } else {
        // Insert new request if no rejected status exists
        $insert_sql = "INSERT INTO mentee_requests (mentee_email, mentor_email, status) VALUES (?, ?, 'Pending')";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ss", $mentee_email, $mentor_email);

        if ($stmt->execute()) {
            $message = "Request sent successfully!";
            $message_class = "success";
        } else {
            $message = "Failed to send the request.";
            $message_class = "error";
        }
    }

    $stmt->close();
}

// Handle canceling a request
if (isset($_POST['cancel_request'])) {
    $mentor_email = $_POST['mentor_email'];
    $mentee_email = $email;

    // Delete request
    $delete_sql = "DELETE FROM mentee_requests WHERE mentee_email = ? AND mentor_email = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ss", $mentee_email, $mentor_email);

    if ($stmt->execute()) {
        $message = "Request canceled successfully!";
        $message_class = "success";
    } else {
        $message = "Failed to cancel the request.";
        $message_class = "error";
    }

    $stmt->close();
}

// Search functionality
$search_term = isset($_POST['search']) ? $_POST['search'] : '';
$mentor_profiles = [];

// Fetch mentors if a search term is provided
if (!empty($search_term)) {
    $search_sql = "SELECT * FROM mentors_info WHERE name LIKE ? OR stream LIKE ?";
    $stmt = $conn->prepare($search_sql);
    $like_term = "%" . $search_term . "%";
    $stmt->bind_param("ss", $like_term, $like_term);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($mentor = $result->fetch_assoc()) {
        $mentor_profiles[] = $mentor;
    }

    $stmt->close();
}

// Fetch mentee status messages (from mentee_status table)
$status_messages = [];
$status_sql = "SELECT ms.*, m.name as mentor_name FROM mentee_status ms 
               JOIN mentors_info m ON ms.mentor_email = m.email
               WHERE ms.mentee_email = ?";
$stmt = $conn->prepare($status_sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

while ($message_row = $result->fetch_assoc()) {
    $status_messages[] = $message_row;
}

$stmt->close();

// Fetch meetings for the mentee
$meetings = [];
$meetings_sql = "SELECT * FROM meetings WHERE mentee_email = ?";
$stmt = $conn->prepare($meetings_sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

while ($meeting = $result->fetch_assoc()) {
    $meetings[] = $meeting;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentee Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Open+Sans:wght@300&display=swap" rel="stylesheet">
    <style>
        /* General styling */
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            display: flex;
        }
        .sidebar {
            width: 20%;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            height: 100vh;
        }
        .menu-item {
            margin: 15px 0;
            cursor: pointer;
            padding: 10px;
            border-radius: 5px;
        }
        .menu-item.active, .menu-item:hover {
            background-color: #32495e;
        }
        .main-content {
            width: 80%;
            padding: 20px;
        }
        .search-bar {
            width: 80%;
            padding: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .mentor-profile {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .mentor-profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-right: 15px;
        }
        .mentor-profile p {
            margin: 5px 0;
        }
        .profile-info {
            text-align: center;
        }
        .profile-info img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
        }
        .request-buttons {
            margin-top: 10px;
        }
        .request-buttons button {
            margin-right: 10px;
            padding: 5px 10px;
            cursor: pointer;
        }
        .message {
            color: green;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .message.error {
            color: red;
        }
        .message.info {
            color: #ff8c00;
        }
        .message.success {
            color: green;
        }
        .status-message {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
        }
        .meeting-list {
            margin-top: 20px;
        }
        .meeting-item {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Mentee Dashboard</h2>
        <div class="menu-item active" onclick="showSection('home')">Home</div>
        <div class="menu-item" onclick="showSection('profile')">Profile</div>
        <div class="menu-item" onclick="showSection('requests')">Requests</div>
        <div class="menu-item" onclick="showSection('meetings')">Meetings</div>
        <div class="menu-item" onclick="window.location.href='mentee_chat.php'">Chat</div>
        <div class="menu-item" id="logoutBtn" onclick="logout()">Logout</div>
    </div>

    <div class="main-content">
        <!-- Display message -->
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_class; ?>" id="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Home Section -->
        <div id="home" class="section">
            <form method="POST">
                <input type="text" class="search-bar" name="search" placeholder="Search for mentors..." value="<?php echo htmlspecialchars($search_term); ?>">
            </form>

            <?php if (!empty($mentor_profiles)): ?>
                <div class="mentor-list">
                    <?php foreach ($mentor_profiles as $mentor): ?>
                        <div class="mentor-profile">
                            <img src="<?php echo htmlspecialchars($mentor['profile_picture']); ?>" alt="Mentor Picture">
                            <div>
                                <p><strong><?php echo htmlspecialchars($mentor['name']); ?></strong></p>
                                <p>Stream: <?php echo htmlspecialchars($mentor['stream']); ?></p>
                                <p>Position: <?php echo htmlspecialchars($mentor['position']); ?></p>
                                <p>Contact: <?php echo htmlspecialchars($mentor['email']); ?></p>
                                <form method="POST" style="margin-top: 10px;">
                                    <input type="hidden" name="mentor_email" value="<?php echo htmlspecialchars($mentor['email']); ?>">
                                    <button type="submit" name="send_request">Send Request</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Profile Section -->
        <div id="profile" class="section" style="display: none;">
            <h2>Your Profile</h2>
            <div class="profile-info">
                <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
                <p>Name: <?php echo htmlspecialchars($name); ?></p>
                <p>Phone: <?php echo htmlspecialchars($phone); ?></p>
                <p>Email: <?php echo htmlspecialchars($email); ?></p>
                <p>Area of Interest: <?php echo htmlspecialchars($area_of_interest); ?></p>
            </div>
        </div>

        <!-- Requests Section -->
        <div id="requests" class="section" style="display: none;">
            <h2>Your Requests</h2>

            <?php if (!empty($status_messages)): ?>
                <?php foreach ($status_messages as $status): ?>
                    <div class="status-message">
                        <p><strong>Mentor: <?php echo htmlspecialchars($status['mentor_name']); ?></strong></p>
                        <p>Status: <?php echo htmlspecialchars($status['status']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No requests sent yet.</p>
            <?php endif; ?>
        </div>

        <!-- Meetings Section -->
        <div id="meetings" class="section" style="display: none;">
            <h2>Your Meetings</h2>

            <?php if (!empty($meetings)): ?>
                <?php foreach ($meetings as $meeting): ?>
                    <div class="meeting-item">
                    <p><strong>Mentor_email:</strong> <?= $meeting['mentor_email'] ?></p>
                            <p><strong>Meeting Date:</strong> <?= $meeting['meeting_date'] ?></p>
                            <p><strong>Time:</strong> <?= $meeting['meeting_time'] ?></p>
                            <p><strong>Note:</strong> <?= $meeting['meeting_notes'] ?></p>
                        <p>Link: <a href="<?php echo htmlspecialchars($meeting['meeting_link']); ?>" target="_blank">Join Meeting</a></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No meetings scheduled yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showSection(section) {
            document.querySelectorAll('.section').forEach(function(el) {
                el.style.display = 'none';
            });
            document.getElementById(section).style.display = 'block';
        }

        function logout() {
            alert("Logging out...");
            window.location.href = 'mentee_login.php';
        }
    </script>
</body>
</html>
