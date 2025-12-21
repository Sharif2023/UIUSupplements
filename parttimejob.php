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
  <title>Part-Time Jobs</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/index.css" />
  <style>
    /* Page-specific styles for Part-Time Jobs */
    .main h1 {
      font-size: 28px;
      margin-bottom: 20px;
    }

    .job-card {
      background-color: #fff;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      border-radius: 10px;
    }

    .job-card h3 {
      font-size: 20px;
      margin-bottom: 10px;
    }

    .job-card p {
      font-size: 16px;
      margin-bottom: 5px;
    }

    .accept-btn {
      background-color: #FF3300;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .accept-btn:hover {
      background-color: #1F1F1F;
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
        <i class="fas fa-sign-out-alt"></i>
        Log Out
      </a>
    </nav>

    <!-- Job Listings -->
    <div class="main">
      <h1>Available Part-Time Jobs</h1>
      <div class="job-card">
        <h3>Job Title 1</h3>
        <p>Days per week: 5</p>
        <p>Salary: $500/week</p>
        <p>Category: Teaching Assistant</p>
        <p>Location: UIU</p>
        <button class="accept-btn" onclick="acceptJob(1)">Accept Job</button>
      </div>

      <div class="job-card">
        <h3>Job Title 2</h3>
        <p>Days per week: 3</p>
        <p>Salary: $300/week</p>
        <p>Category: Research Assistant</p>
        <p>Location: Remote</p>
        <button class="accept-btn" onclick="acceptJob(2)">Accept Job</button>
      </div>
    </div>
  </div>

  <script>
    function acceptJob(jobId) {
      var userId = 1; // Replace with dynamic user ID from session

      // Use AJAX to send the job acceptance request to the server
      var xhr = new XMLHttpRequest();
      xhr.open("POST", "accept_job.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

      xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
          alert(xhr.responseText); // Show the response from the server
        }
      };

      xhr.send("job_id=" + jobId + "&user_id=" + userId);
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
<script src="assets/js/index.js"></script>

</html>
