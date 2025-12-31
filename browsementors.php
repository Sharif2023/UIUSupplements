<?php
session_start();

// Authentication check - redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Mentors | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <link rel="stylesheet" href="assets/css/responsive-mobile.css?v=2.0" />
    <style>
        /* Page-specific styles for Browse Mentors */
        .center-title {
            flex: 1;
            text-align: center;
            font-size: 30px;
            color: #333;
        }

        /* Mentor Cards Grid */
        .mentor-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .mentor-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 100%;
            background-color: #f5f5f5;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            position: relative;
        }

        .mentor-card:hover {
            transform: scale(1.05);
        }

        .mentor-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        .mentor-card-content {
            flex-grow: 1;
        }

        .mentor-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .mentor-info h2 {
            font-size: 1.2rem;
            margin: 0;
        }

        .contact-button {
            background-color: #2196F3;
            color: white;
            padding: 5px 8px;
            font-size: 12px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .contact-button:hover {
            background-color: #0062b3;
        }

        .contact-button img {
            width: 12px;
            height: 12px;
            margin-right: 5px;
        }

        h3 {
            display: flex;
            align-items: center;
            font-size: 12px;
            margin: 5px 0;
        }

        .mentor-card-content h3 img {
            width: 12px;
            height: 12px;
        }

        .language img,
        .country img,
        .response-time img {
            width: 12px;
            height: 12px;
        }

        .language,
        .country,
        .response-time {
            font-size: 12px;
            margin: 0 5px;
            display: flex;
            align-items: center;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 6px 10px;
            font-size: 12px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block;
        }

        button.view-profile {
            background-color: #FF3300;
        }

        button.view-profile:hover {
            background-color: #1F1F1F;
        }

        .add-mentor-btn {
            background-color: #28a745;
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }

        .add-mentor-btn:hover {
            background-color: #218838;
        }

        @media (max-width: 480px) {
            .mentor-cards {
                grid-template-columns: 1fr;
            }
        }

        /* Page Header with Mentor Panel Button */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .mentor-panel-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .mentor-panel-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .mentor-panel-btn i {
            font-size: 16px;
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
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>

        <section class="main">
            <div class="page-header">
                <div class="main-top">
                    <button id="back" onclick="location.href='uiusupplementhomepage.php'" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <h1 class="center-title">Browse Mentors</h1>
                </div>
                <?php if (isset($_SESSION['is_mentor']) && $_SESSION['is_mentor']): ?>
                <a href="mentorpanel.php" class="mentor-panel-btn">
                    <i class="fas fa-chalkboard-teacher"></i> Mentor Panel
                </a>
                <?php endif; ?>
            </div>


            <div class="mentor-cards" id="mentor-list">
                <!-- Mentor cards will be dynamically inserted here from database-->
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch('api/mentors.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(mentors => {
                    const mentorListContainer = document.getElementById('mentor-list');
                    if (mentors.length === 0) {
                        mentorListContainer.innerHTML = "<p>No mentors found</p>";
                        return;
                    }
                    mentors.forEach(mentor => {
                        const mentorCard = document.createElement('div');
                        mentorCard.classList.add('mentor-card');

                        mentorCard.innerHTML = `
                            <img src="${mentor.photo}" alt="${mentor.name} Photo">
                            <div class="mentor-card-content">
                                <div class="mentor-info">
                                    <h2>${mentor.name}</h2>
                                    <a href="contactmentor.php?mentor_id=${mentor.id}" class="contact-button">
                                        <img src="https://img.icons8.com/ios-filled/30/000000/phone.png" alt="Contact Icon">
                                        Contact
                                    </a>
                                </div>
                                <h3>
                                    <img src="https://img.icons8.com/material-rounded/24/000000/language.png" alt="Language Icon"> ${mentor.language}
                                </h3>
                                <h3>
                                    <img src="https://img.icons8.com/material-rounded/24/000000/marker.png" alt="Location Icon"> ${mentor.country}
                                </h3>
                                <h3 class="response-time">
                                    <img src="https://img.icons8.com/emoji/24/4CAF50/high-voltage.png" alt="Response Time Icon"> ${mentor.response_time}
                                </h3>
                                <h3>
                                    Skills: ${mentor.skills}
                                </h3>
                                <button onclick="location.href='viewmentorprofile.php?id=${mentor.id}'" class="view-profile">View Profile</button>
                            </div>
                        `;
                        mentorListContainer.appendChild(mentorCard);
                    });
                })
                .catch(error => {
                    console.error('Error fetching mentors:', error);
                    document.getElementById('mentor-list').innerHTML = "<p>Error loading mentors.</p>";
                });
        });
    </script>
</body>
<footer class="footer">
    <div class="social-icons">
        <a href="https://www.facebook.com/sharif.me2018"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="http://uiusupplements.yzz.me/"><i class="fab fa-google"></i></a>
        <a href="https://www.instagram.com/shariful_islam10"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-linkedin-in"></i></a>
        <a href="https://www.github.com/sharif2023"><i class="fab fa-github"></i></a>
    </div>
    <div class="copyright">
        &copy; 2020 Copyright: <a href="https://www.youtube.com/@SHARIFsCODECORNER">Sharif Code Corner</a>
    </div>
</footer>
<script src="assets/js/index.js"></script>
<script src="assets/js/mobile-nav.js"></script>

</html>
