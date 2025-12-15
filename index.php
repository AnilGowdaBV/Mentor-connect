<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Connect - Welcome</title>
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
            position: relative;
            overflow: hidden;
        }

        /* Main Container Styling */
        .container {
            text-align: center;
            background: rgba(0, 0, 0, 0.8); /* Dark transparent background */
            padding: 5rem 6rem;
            border-radius: 20px;
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.3);
            width: 550px;
            max-width: 100%;
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: scale(1.05);
        }

        /* Title */
        .title {
            font-size: 3rem;
            color: #fff;
            margin-bottom: 3rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 5px;
        }

        /* Buttons */
        .buttons {
            display: flex;
            justify-content: center;
            gap: 2rem;
        }

        .btn {
            padding: 1.2rem 3rem;
            font-size: 1.3rem;
            font-weight: bold;
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .mentor-btn {
            background-color: #007bff;
        }

        .mentee-btn {
            background-color: #28a745;
        }

        .btn:hover {
            filter: brightness(90%);
        }

        /* Header and Footer Styling */
        .header, .footer {
            position: absolute;
            width: 100%;
            color: #fff;
            font-size: 1.2rem;
            font-weight: bold;
            padding: 10px;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 5px;
            letter-spacing: 2px;
        }

        .header {
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
        }

        .footer {
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
        }

        .footer a {
            color: #ff6a00;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        "Mentorship is the key to unlocking potential."
    </div>

    <!-- Main Content -->
    <div class="container">
        <h1 class="title">Mentor Connect</h1>
        
        <!-- Buttons for Mentor and Mentee -->
        <div class="buttons">
            <a href="mentor_login.php" class="btn mentor-btn">Mentor</a>
            <a href="mentee_login.php" class="btn mentee-btn">Mentee</a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Empowering the future through mentorship. <a href="#">Learn More</a>
    </div>
</body>
</html>
