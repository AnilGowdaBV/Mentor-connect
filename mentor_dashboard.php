<?php
// Start session for user authentication
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mentor_connect";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if mentor is logged in
if (!isset($_SESSION['email'])) {
    header("Location: mentor_login.php");
    exit();
}

// Fetch mentor email from session
$mentor_email = $_SESSION['email'];

// Fetch mentor's profile information
$sql_profile = "SELECT * FROM mentors_info WHERE email = ?";
$stmt_profile = $conn->prepare($sql_profile);
$stmt_profile->bind_param("s", $mentor_email);
$stmt_profile->execute();
$result_profile = $stmt_profile->get_result();
$mentor_profile = $result_profile->fetch_assoc();
$stmt_profile->close();

// Fetch mentee requests
$sql_requests = "SELECT mentees_info.name AS mentee_name, mentees_info.email AS mentee_email, mentees_info.profile_picture AS profile_picture, mentees_info.area_of_interest AS area_of_interest 
        FROM mentee_requests 
        JOIN mentees_info ON mentee_requests.mentee_email = mentees_info.email 
        WHERE mentee_requests.mentor_email = ?";
$stmt_requests = $conn->prepare($sql_requests);
$stmt_requests->bind_param("s", $mentor_email);
$stmt_requests->execute();
$result_requests = $stmt_requests->get_result();
$requests = [];
while ($row = $result_requests->fetch_assoc()) {
    $requests[] = $row;
}
$stmt_requests->close();

// Fetch mentor's meetings
$sql_meetings = "SELECT * FROM meetings WHERE mentor_email = ?";
$stmt_meetings = $conn->prepare($sql_meetings);
$stmt_meetings->bind_param("s", $mentor_email);
$stmt_meetings->execute();
$result_meetings = $stmt_meetings->get_result();
$meetings = [];
while ($row = $result_meetings->fetch_assoc()) {
    $meetings[] = $row;
}
$stmt_meetings->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['mentee_email'])) {
        $action = $_POST['action'];
        $mentee_email = $_POST['mentee_email'];
        
        $status = ($action == 'accepted') ? 'accepted' : 'rejected';
        $message = ($status == 'accepted') ? 'Request Accepted' : 'Request Rejected';
        $decision_date = date("Y-m-d H:i:s");

        $stmt = $conn->prepare("INSERT INTO mentee_status (mentor_email, mentee_email, status, message, decision_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $mentor_email, $mentee_email, $status, $message, $decision_date);
        $stmt->execute();
        $stmt->close();

        if ($action == 'rejected') {
            $stmt_remove = $conn->prepare("DELETE FROM mentee_requests WHERE mentor_email = ? AND mentee_email = ?");
            $stmt_remove->bind_param("ss", $mentor_email, $mentee_email);
            $stmt_remove->execute();
            $stmt_remove->close();
        }

        echo json_encode(['success' => true, 'action' => $action, 'mentee_email' => $mentee_email]);
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #2c3e50;
            color: white;
            position: fixed;
            padding-top: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            margin-bottom: 5px;
        }
        .sidebar a:hover {
            background-color: #34495e;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .hidden {
            display: none;
        }
        .profile {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .profile img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
        .request-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
            padding: 10px 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .request-item img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }
        .request-actions button {
            margin-left: 10px;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .request-actions .accept {
            background-color: #28a745;
            color: #fff;
        }
        .request-actions .reject {
            background-color: #dc3545;
            color: #fff;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }
        .notification.error {
            background-color: #dc3545;
        }
        .search-bar {
            display: none;
            margin-bottom: 20px;
        }
        .search-bar input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .meeting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
            padding: 10px 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .meeting-item p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="#" onclick="showSection('home')">Home</a>
        <a href="#" onclick="showSection('profile')">Profile</a>
        <a href="#" onclick="showSection('requests')">Requests</a>
        <a href="#" onclick="showSection('meetings')">Meetings</a>
        <a href="mentor_chat.php" class="chat">Chat</a> <!-- Added redirect to mentor_chat.php -->
        <a href="javascript:void(0);" onclick="logout()">Logout</a>
    </div>

    <div class="main-content">
        <div id="home" class="section">
            <h2>Home</h2>
            <div class="search-bar" id="search-bar">
                <input type="text" placeholder="Search...">
            </div>
        </div>

        <div id="requests" class="section hidden">
            <h2>Requesting Mentees</h2>
            <?php if (empty($requests)): ?>
                <p>No mentee requests at the moment.</p>
            <?php else: ?>
                <?php foreach ($requests as $request): ?>
                    <div class="request-item" id="request_<?= $request['mentee_email'] ?>">
                        <div>
                            <img src="<?= $request['profile_picture'] ?>" alt="Profile Picture">
                            <p><strong>Name:</strong> <?= $request['mentee_name'] ?></p>
                            <p><strong>Email:</strong> <?= $request['mentee_email'] ?></p>
                            <p><strong>Area of Interest:</strong> <?= $request['area_of_interest'] ?></p>
                        </div>
                        <div class="request-actions">
                            <button class="accept" onclick="updateRequest('accepted', '<?= $request['mentee_email'] ?>')">Accept</button>
                            <button class="reject" onclick="updateRequest('rejected', '<?= $request['mentee_email'] ?>')">Reject</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="meetings" class="section hidden">
            <h2>Scheduled Meetings</h2>
            <?php if (empty($meetings)): ?>
                <p>No meetings scheduled at the moment.</p>
            <?php else: ?>
                <?php foreach ($meetings as $meeting): ?>
                    <div class="meeting-item">
                        <div>
                            <p><strong>Mentee_email:</strong> <?= $meeting['mentee_email'] ?></p>
                            <p><strong>Meeting Date:</strong> <?= $meeting['meeting_date'] ?></p>
                            <p><strong>Time:</strong> <?= $meeting['meeting_time'] ?></p>
                            <p><strong>Note:</strong> <?= $meeting['meeting_notes'] ?></p>
                            <p>Link: <a href="<?php echo htmlspecialchars($meeting['meeting_link']); ?>" target="_blank">Join Meeting</a></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="profile" class="section hidden">
            <h2>Mentor Profile</h2>
            <div class="profile">
                <img src="<?= $mentor_profile['profile_picture'] ?>" alt="Profile Picture">
                <div>
                    <p><strong>Name:</strong> <?= $mentor_profile['name'] ?></p>
                    <p><strong>Email:</strong> <?= $mentor_profile['email'] ?></p>
                    <p><strong>Position:</strong> <?= $mentor_profile['position'] ?></p>
                    <p><strong>Experience:</strong> <?= $mentor_profile['experience'] ?></p>
                    <p><strong>Stream:</strong> <?= $mentor_profile['stream'] ?></p>
                    <p><strong>Achievements:</strong> <?= $mentor_profile['achievements'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="notification" id="notification"></div>

    <script>
        function showSection(section) {
            document.querySelectorAll('.section').forEach(s => s.classList.add('hidden'));
            document.getElementById(section).classList.remove('hidden');
            if (section === 'home') {
                document.getElementById('search-bar').style.display = 'block';
            } else {
                document.getElementById('search-bar').style.display = 'none';
            }
        }

        function updateRequest(action, menteeEmail) {
            fetch('mentor_dashboard.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&mentee_email=${menteeEmail}`
            })
            .then(response => response.json())
            .then(data => {
                const notification = document.getElementById('notification');
                if (data.success) {
                    const requestElement = document.getElementById('request_' + data.mentee_email);
                    if (data.action === 'accepted') {
                        requestElement.querySelector('.request-actions').innerHTML = `
                            <button class="schedule-meeting" onclick="scheduleMeeting()">Schedule Meeting</button>
                        `;
                    } else {
                        requestElement.style.display = 'none';
                    }
                    notification.textContent = 'Status updated successfully!';
                } else {
                    notification.textContent = 'Something went wrong!';
                    notification.classList.add('error');
                }
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);
            });
        }

        function scheduleMeeting() {
            setTimeout(() => {
                window.location.href = 'schedule.php';
            }, 1000);
        }

        function logout() {
            const notification = document.getElementById('notification');
            notification.textContent = "Logging out...";
            notification.style.display = 'block';
            setTimeout(() => {
                window.location.href = 'mentor_login.php';
            }, 2000);
        }

        // Default view
        showSection('home');
    </script>
</body>
</html>
