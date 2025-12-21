<?php
session_start();

// Authentication check - redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'uiusupplements');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get mentor ID from URL
$mentor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Profile | UIU Supplement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700");

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
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

        /* Styling for profile page */
        .main {
            flex: 1;
            margin-left: 250px;
            padding: 40px;
            background-color: #f0f0f5;
        }

        .profile-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 20px auto;
            font-family: 'Poppins', sans-serif;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .profile-header h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .profile-bio {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .profile-details {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 30px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
            padding: 5px 0;
            /* Add padding for better spacing */
            overflow: hidden;
            /* Prevent overlap */
        }

        .detail-item img {
            width: 24px;
            height: 24px;
            margin-right: 10px;
        }

        .detail-item span {
            white-space: nowrap;
            /* Prevent wrapping if needed */
        }

        .back-button,
        .browse-mentors-button {
            background-color: #FF3300;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            /* Space below the back button */
        }

        .back-button:hover {
            background-color: #1F1F1F;
        }

        .browse-mentors-button {
            background-color: #FF3300;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin: 20px auto;
            /* Center the button */
            display: block;
            /* Make it a block-level element */
            max-width: 200px;
            /* Optional: limit the button width */
        }

        .browse-mentors-button:hover {
            background-color: #1F1F1F;
        }


        .contact-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            margin-top: 20px;
        }

        .contact-info a {
            text-decoration: none;
            color: #333;
            padding: 10px 15px;
            border-radius: 5px;
            background-color: #f0f0f5;
            display: flex;
            align-items: center;
        }

        .contact-info a img {
            width: 24px;
            height: 24px;
            margin-right: 10px;
        }

        .contact-info a:hover {
            background-color: #FF3300;
            color: white;
        }

        /* Media Queries */
        @media (max-width: 768px) {
            .profile-details {
                gap: 5px;
            }
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
                        <span class="nav-item">Sell or Exchange</span>
                    </a></li>
                <li><a href="roomrenthomepage.html">
                        <i class="fas fa-building"></i>
                        <span class="nav-item">Room Rent</span>
                    </a></li>
                <li><a href="browsementors.php">
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

            <!-- Log Out Button -->
            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>

        <section class="main">
            <div class="profile-container">
                <a href="browsementors.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <?php
                if ($mentor_id) {
                    // Fetch mentor details from database
                    $sql = "SELECT * FROM uiumentorlist WHERE id = $mentor_id";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $mentor = $result->fetch_assoc();
                ?>

                        <div class='profile-header'>
                            <img src='<?php echo $mentor["photo"]; ?>' alt='Mentor Photo' class='profile-photo'>
                            <h2><?php echo $mentor["name"]; ?></h2>
                            <p class='profile-bio'><?php echo $mentor["bio"]; ?></p>
                        </div>

                        <div class='profile-details'>
                            <div class='detail-item'>
                                <img src='https://img.icons8.com/material-rounded/24/000000/marker.png' alt='Location'>
                                <?php echo $mentor["country"]; ?>
                            </div>
                            <div class='detail-item'>
                                <img src='https://img.icons8.com/material-rounded/24/000000/language.png' alt='Language'>
                                <?php echo $mentor["language"]; ?>
                            </div>
                            <div class='detail-item'>
                                <img src='https://img.icons8.com/emoji/24/4CAF50/high-voltage.png' alt='Response Time'>
                                <?php echo $mentor["response_time"]; ?>
                            </div>
                            <div class='detail-item'>
                                <img src='https://img.icons8.com/material-outlined/24/000000/briefcase.png' alt='Industry'>
                                <?php echo $mentor["industry"]; ?>
                            </div>
                            <div class='detail-item'>
                                <img src='https://img.icons8.com/material-outlined/24/000000/money.png' alt='Hourly Rate'>
                                $<?php echo $mentor["hourly_rate"]; ?> per hour
                            </div>
                            <div class='detail-item'>
                                <img src='https://uxwing.com/wp-content/themes/uxwing/download/business-professional-services/professional-skills-icon.png' alt='Skills'>
                                <span><?php echo $mentor["skills"]; ?></span>
                            </div>

                        </div>

                        <div class='contact-info'>
                            <a href='mailto:<?php echo $mentor["email"]; ?>'>
                                <img src='https://img.icons8.com/ios-filled/50/000000/email.png'>Email
                            </a>
                            <?php if ($mentor["whatsapp"]) { ?>
                                <a href='https://wa.me/<?php echo $mentor["whatsapp"]; ?>'>
                                    <img src='https://img.icons8.com/ios-filled/50/000000/whatsapp.png'>WhatsApp
                                </a>
                            <?php } ?>
                            <?php if ($mentor["linkedin"]) { ?>
                                <a href='<?php echo $mentor["linkedin"]; ?>'>
                                    <img src='https://img.icons8.com/ios-filled/50/000000/linkedin.png'>LinkedIn
                                </a>
                            <?php } ?>
                            <?php if ($mentor["facebook"]) { ?>
                                <a href='<?php echo $mentor["facebook"]; ?>'>
                                    <img src='https://img.icons8.com/ios-filled/50/000000/facebook.png'>Facebook
                                </a>
                            <?php } ?>
                        </div>

                <?php
                    } else {
                        echo "<p>Mentor not found.</p>";
                    }
                } else {
                    echo "<p>No mentor selected.</p>";
                }
                ?>
            </div>
            <a href="browsementors.php" class="browse-mentors-button">
                Browse Mentors
            </a>
        </section>
    </div>
</body>

</html>

<?php
$conn->close();
?>
