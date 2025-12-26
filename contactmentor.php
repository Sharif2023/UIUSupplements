<?php

session_start();
$user_id = $_SESSION['user_id'] ?? null; // Ensure the session is correctly started and set
if (!isset($user_id)) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";  // Replace with your database password
$dbname = "uiusupplements";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get mentor_id from URL
$mentor_id = isset($_GET['mentor_id']) ? intval($_GET['mentor_id']) : 0;

if ($mentor_id > 0) {
    // Fetch mentor details from the database
    $sql = "SELECT name, hourly_rate, photo FROM uiumentorlist WHERE id = $mentor_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $mentor = $result->fetch_assoc();
        $hourly_rates = explode(',', $mentor['hourly_rate']);  // Split the hourly_rate into an array
    } else {
        echo "Mentor not found.";
        exit;
    }
} else {
    echo "Invalid mentor ID.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_price = $_POST['session_price'];
    $communication_method = $_POST['communication_method'];
    $time = $_POST['selected_time'];
    $date = $_POST['selected_date'];
    $problem_description = $_POST['problem_description'];
    $created_time = date('Y-m-d H:i:s');

    // Ensure user_id is correctly fetched from the session
    $user_id = $_SESSION['user_id'];

    // Prepare SQL statement for insertion
    $stmt = $conn->prepare("INSERT INTO request_mentorship_session (user_id, mentor_id, session_time, session_price, communication_method, session_date, problem_description, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssss", $user_id, $mentor_id, $time, $session_price, $communication_method, $date, $problem_description, $created_time);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Request submitted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Request</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700");

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;

            background-color: #f0f0f5;
        }

        .container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Navigation */
        nav {
            width: 100%;
            max-width: 250px;
            background-color: #fff;
            padding: 20px;
            height: 100vh;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            transition: top 0.3s ease-in-out;
        }

        .styled-title {
            font-size: 1.4rem;
            color: #1F1F1F;
            text-shadow: 0 0 5px #ff005e, 0 0 10px #ff005e, 0 0 20px #ff005e, 0 0 40px #ff005e, 0 0 80px #ff005e;
            animation: glow 1.5s infinite alternate;
        }

        .styled-title:hover {
            transform: translateY(-5px);
            text-shadow: 3px 3px 5px rgba(0, 0, 0, 0.3);
        }

        @keyframes glow {
            0% {
                text-shadow: 0 0 5px #ff005e, 0 0 10px #ff005e, 0 0 20px #ff005e, 0 0 40px #ff005e, 0 0 80px #ff005e;
            }

            100% {
                text-shadow: 0 0 10px #00d4ff, 0 0 20px #00d4ff, 0 0 40px #00d4ff, 0 0 80px #00d4ff, 0 0 160px #00d4ff;
            }
        }

        nav ul {
            list-style-type: none;
            padding-top: 20px;
        }

        nav ul li {
            margin: 15px 0;
        }

        nav ul li a {
            color: #555;
            font-size: 18px;
            display: flex;
            align-items: center;
            padding: 10px;
            text-decoration: none;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background-color: #f0f0f5;
            border-radius: 10px;
        }

        nav ul li a .nav-item {
            margin-left: 15px;
        }

        /* Log Out Button */
        .logout-btn {
            background-color: #FF3300;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            margin-top: 20px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn i {
            margin-right: 10px;
        }

        .logout-btn:hover {
            background-color: #1F1F1F;
        }

        /* Main Section */
        .main {
            margin-left: 270px;
            /* Adjusted to ensure proper alignment with sidebar */
            flex: 1;
            padding: 40px;
        }

        .main h1 {
            font-size: 30px;
            color: #333;
            text-align: center;
        }

        /* Session Details Container */
        .session-details {
            max-width: 800px;
            background-color: #fff;
            margin: 30px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .mentor-info {
            display: flex;
            align-items: center;
        }

        .mentor-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .mentor-name {
            font-size: 18px;
            font-weight: bold;
        }

        .divider {
            border: none;
            height: 1px;
            background-color: #ccc;
            margin: 15px 0;
        }

        /* Time and Price Options */
        .session-options {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
        }

        .time-price-option {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            text-align: center;
            flex: 1;
            margin-right: 10px;
            cursor: pointer;
        }

        .time-price-option:last-child {
            margin-right: 0;
        }

        .time-price-option:hover {
            background-color: #0056b3;
        }

        .low-opacity {
            color: rgba(255, 255, 255, 0.8);
        }

        /* Communication Section */
        .communication h2 {
            font-size: 20px;
            margin-top: 20px;
        }

        .communication p {
            margin: 5px 0 15px;
        }

        .comm-btn {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            display: inline-flex;
            align-items: center;
        }

        .comm-btn img {
            width: 20px;
            margin-right: 10px;
        }

        .comm-btn:hover {
            background-color: #0056b3;
        }

        /* Schedule Session Section */
        .schedule-session h2 {
            font-size: 20px;
            margin-top: 20px;
        }

        .schedule-session p {
            margin: 5px 0;
            font-size: 14px;
            color: rgba(0, 0, 0, 0.6);
        }

        /* Problem/Challenge Section */
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-top: 10px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }

        .checkbox-container label {
            margin-left: 10px;
        }

        /* Confirm Button */
        .confirm-btn {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }

        .confirm-btn:hover {
            background-color: #0056b3;
        }

        /* Navigation Buttons */
        .nav-buttons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .nav-btn {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 10px;
        }

        .nav-btn:hover {
            background-color: #555;
        }

        /* Selected button highlighting */
        .selected {
            border: 2px solid #28a745;
            background-color: #28a745 !important;
        }

        /* Date-Time Picker Styles */
        .schedule-session {
            margin-top: 20px;
        }

        .date-time-option {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
        }

        .date-btn,
        .time-btn {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .date-btn:hover,
        .time-btn:hover {
            background-color: #218838;
        }

        .date-btn img,
        .time-btn img {
            width: 20px;
            margin-right: 10px;
        }

        .selected-datetime {
            margin-top: 10px;
            font-size: 16px;
            color: #333;
        }

        .custom-picker {
            display: none;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            position: absolute;
            z-index: 1000;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .custom-picker input {
            width: 200px;
            padding: 5px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /*footer*/
        .content {
            flex: 1;
        }

        .footer {
            background-color: #1F1F1F;
            color: white;
            text-align: center;
            padding: 20px;
            width: 100%;
            position: relative;
            /* Change from fixed to relative */
        }

        .social-icons {
            margin: 20px 0;
        }

        .social-icons a {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            margin: 5px;
            background-color: transparent;
            color: white;
            border: 1px solid white;
            border-radius: 50%;
            text-align: center;
            text-decoration: none;
            font-size: 20px;
        }

        .social-icons a:hover {
            background-color: white;
            color: #FF3300;
        }

        .copyright {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 10px;
            margin-top: 10px;
        }

        .copyright a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <nav>
            <ul>
                <li><a href="uiusupplementhomepage.php" class="logo">
                        <h1 class="styled-title">UIU Supplement</h1>
                    </a></li>
                <li><a href="uiusupplementhomepage.php">
                        <i class="fas fa-home"></i>
                        <span class="nav-item">Home</span>
                    </a></li>
                <li><a href="SellAndExchange.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span class="nav-item">Sell</span>
                    </a></li>
                <li><a href="availablerooms.php">
                        <i class="fas fa-building"></i>
                        <span class="nav-item">Room Rent</span>
                    </a></li>
                <li><a href="browsementors.php" class="active">
                        <i class="fas fa-user"></i>
                        <span class="nav-item">Mentorship</span>
                    </a></li>
                <li><a href="parttimejob.php">
                        <i class="fas fa-briefcase"></i>
                        <span class="nav-item">Jobs</span>
                    </a></li>
                <li><a href="lostandfound.php">
                        <i class="fas fa-dumpster"></i>
                        <span class="nav-item">Lost and Found</span>
                    </a></li>
                <li><a href="shuttle_tracking_system.php">
                        <i class="fas fa-bus"></i>
                        <span class="nav-item">Shuttle Services</span>
                    </a></li>
            </ul>
            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </nav>

        <div class="main">
            <h1>Request a Mentorship Session</h1>

            <div class="session-details">
                <div class="mentor-info">
                    <div class="mentor-name"><?php echo htmlspecialchars($mentor['name']); ?></div>
                </div>

                <hr class="divider">

                <div class="session-options">
                    <?php foreach ($hourly_rates as $index => $rate): ?>
                        <button class="time-price-option" onclick="selectOption(this, '<?php echo htmlspecialchars(trim($rate)); ?>')">
                            <span class="low-opacity"><?php echo ($index + 1) * 30 . ' min'; ?></span><br>
                            <?php echo htmlspecialchars(trim($rate)) . ' Tk'; ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <hr class="divider">

                <div class="communication">
                    <h2>Communication</h2>
                    <p>Choose a method:</p>
                    <button class="comm-btn" onclick="selectCommunication(this, 'Zoom')">
                        <img src="uploads/Zoom_Icon.png" alt="Zoom Icon">
                        Zoom
                    </button>
                    <button class="comm-btn" onclick="selectCommunication(this, 'Meet')">
                        <img src="uploads/Meet_Icon.png" alt="Meet Icon">
                        Meet
                    </button>
                </div>
                <hr class="divider">

                <div class="schedule-session">
                    <h2>Schedule a Session</h2>
                    <div class="date-time-option">
                        <button class="date-btn" onclick="showDatePicker()">
                            <img src="uploads/calendar-icon.png" alt="Calendar Icon">
                            Select Date
                        </button>
                        <button class="time-btn" onclick="showTimePicker()">
                            <img src="uploads/time-icon.png" alt="Clock Icon">
                            Select Time
                        </button>
                    </div>
                    <div id="selected-datetime" class="selected-datetime"></div>
                </div>

                <div id="date-picker" class="custom-picker">
                    <input type="date" id="selected-date">
                    <button class="date-btn" onclick="confirmDate()">Confirm Date</button>
                </div>

                <div id="time-picker" class="custom-picker">
                    <input type="time" id="selected-time">
                    <button class="time-btn" onclick="confirmTime()">Confirm Time</button>
                </div>
                <hr class="divider">
                <div>
                    <h2>Problem/Challenge</h2>
                </div>

                <form method="POST">
                    <!-- Hidden fields for the session price and communication method -->
                    <input type="hidden" id="session-price" name="session_price">
                    <input type="hidden" id="communication-method" name="communication_method">
                    <input type="hidden" id="selected-time-input" name="selected_time">
                    <input type="hidden" id="selected-date-input" name="selected_date">
                    <textarea rows="5" name="problem_description" placeholder="Describe your problem or challenge..."></textarea>
                    <div class="problem-section">
                        <div class="checkbox-container">
                            <input type="checkbox" id="mentor-checkbox">
                            <label for="mentor-checkbox" require>Are you confirm for session request</label>
                        </div>
                    </div>
                    <button type="submit" class="confirm-btn">Confirm Request</button>
                    <a href="contactmentor.php?mentor_id=<?php echo $mentor_id; ?>" class="nav-btn">View Mentor Profile</a>
                </form>

            </div>
        </div>
    </div>
    <script>
        function selectOption(button, rate) {
            // Remove the active class from all buttons
            let options = document.querySelectorAll('.time-price-option');
            options.forEach(option => option.classList.remove('selected'));

            // Add selected class to the clicked button
            button.classList.add('selected');

            // Set the session price
            document.getElementById('session-price').value = rate;
        }

        // Communication Method Selection
        function selectCommunication(button, method) {
            let methods = document.querySelectorAll('.comm-btn');
            methods.forEach(method => method.classList.remove('selected'));
            button.classList.add('selected');

            // Set the communication method
            document.getElementById('communication-method').value = method;
        }

        function showDateTime() {
            let currentDateTime = new Date().toLocaleString(); // Get current date and time
            document.getElementById('selected-date-time').innerHTML = "Selected Date & Time: " + currentDateTime;
        }

        // Function to display date picker
        function showDatePicker() {
            document.getElementById('date-picker').style.display = 'block';
            document.getElementById('time-picker').style.display = 'none';
        }

        // Function to display time picker
        function showTimePicker() {
            document.getElementById('time-picker').style.display = 'block';
            document.getElementById('date-picker').style.display = 'none';
        }

        function confirmDate() {
            const selectedDate = document.getElementById('selected-date').value;
            document.getElementById('selected-date-input').value = selectedDate;
            document.getElementById('selected-datetime').innerHTML = "Selected Date: " + selectedDate;
            document.getElementById('date-picker').style.display = 'none';
        }

        function confirmTime() {
            const selectedTime = document.getElementById('selected-time').value;
            document.getElementById('selected-time-input').value = selectedTime;
            document.getElementById('selected-datetime').innerHTML += " | Selected Time: " + selectedTime;
            document.getElementById('time-picker').style.display = 'none';
        }
    </script>
</body>
<footer class="footer">
    <div class="social-icons">
        <a href="https://www.facebook.com/sharif.me2018"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-google"></i></a>
        <a href="https://www.instagram.com/shariful_islam10"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-linkedin-in"></i></a>
        <a href="https://www.github.com/sharif2023"><i class="fab fa-github"></i></a>
    </div>
    <div class="copyright">
        &copy; 2020 Copyright: <a href="https://www.youtube.com/@SHARIFsCODECORNER">Sharif Code Corner</a>
    </div>
</footer>
<!--footer script-->
<script>
    window.addEventListener("scroll", function() {
        let nav = document.querySelector("nav");
        let footer = document.querySelector(".footer");
        let footerRect = footer.getBoundingClientRect();

        if (footerRect.top <= window.innerHeight) {
            nav.style.position = "absolute";
            nav.style.top = (window.scrollY + footerRect.top - nav.offsetHeight) + "px";
        } else {
            nav.style.position = "fixed";
            nav.style.top = "0";
        }
    });
</script>

</html>
