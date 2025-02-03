<?php
// Database connection
$servername = "localhost";
$username = "root";  // Replace with your database username
$password = "";  // Replace with your database password
$dbname = "uiusupplements";    // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $bio = trim($_POST["bio"]);
    $language = trim($_POST["language"]);
    $responseTime = trim($_POST["response_time"]);
    $industry = trim($_POST["industry"]);
    $company = trim($_POST["company"]);
    $country = trim($_POST["country"]);
    $email = trim($_POST["email"]);
    $whatsapp = trim($_POST["whatsapp"]);
    $linkedin = trim($_POST["linkedin"]);
    $facebook = trim($_POST["facebook"]);

    // Handle skills
    $skills = $_POST["skills"];
    $skills = array_map('trim', $skills); // Trim each skill
    $skills = implode(',', $skills); // Convert array to comma-separated string

    // Handle hourly rates (combine description and value)
    $hourlyRateDescriptions = $_POST["hourly-rate-descriptions"];
    $hourlyRateValues = $_POST["hourly-rate-values"];
    $hourlyRates = [];

    for ($i = 0; $i < count($hourlyRateDescriptions); $i++) {
        $description = trim($hourlyRateDescriptions[$i]);
        $value = trim($hourlyRateValues[$i]);
        if (!empty($description) && !empty($value)) {
            $hourlyRates[] = $description . ' - ' . $value . ' tk';
        }
    }

    $hourlyRate = implode(',', $hourlyRates);  // Comma-separated string of hourly rates

    // Handle photo upload
    $photo = $_FILES["photo"];
    $photoPath = '';

    if ($photo["error"] == UPLOAD_ERR_OK) {
        $photoName = basename($photo["name"]);
        $photoPath = 'uploads/' . $photoName;
        move_uploaded_file($photo["tmp_name"], $photoPath);
    }

    // Insert mentor data into database
    $sql = "INSERT INTO uiumentorlist (photo, name, bio, language, response_time, industry, hourly_rate, company, country, skills, email, whatsapp, linkedin, facebook) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssss", $photoPath, $name, $bio, $language, $responseTime, $industry, $hourlyRate, $company, $country, $skills, $email, $whatsapp, $linkedin, $facebook);

    if ($stmt->execute()) {
        echo "Mentor added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Mentor</title>
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

        .logout-btn:hover {
            background-color: #1F1F1F;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 1.1em;
            color: #666;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            font-size: 1em;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        .form-group select {
            cursor: pointer;
        }

        .form-group input[type="file"] {
            padding: 5px;
        }

        .button-group {
            text-align: center;
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            font-size: 1em;
            border: none;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .add-skill-btn {
            display: block;
            margin-top: 10px;
            color: #28a745;
            cursor: pointer;
            font-size: 0.9em;
        }

        .add-skill-btn:hover {
            text-decoration: underline;
        }

        .skill-entry {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .skill-entry input {
            flex-grow: 1;
            margin-right: 10px;
        }

        .skill-entry button {
            background-color: #dc3545;
        }

        .skill-entry button:hover {
            background-color: #c82333;
        }

        @media (max-width: 768px) {
            nav {
                width: 100%;
                height: auto;
                position: relative;
            }
        }
    </style>
</head>

<body>
    <div class="side-bar">
        <!-- Sidebar Navigation -->
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
                <li><a href="availablerooms.html">
                        <i class="fas fa-building"></i>
                        <span class="nav-item">Room Rent</span>
                    </a></li>
                <li><a href="browsementors.html">
                        <i class="fas fa-user"></i>
                        <span class="nav-item">Mentorship</span>
                    </a></li>
                <li><a href="#">
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
                Log Out
            </a>
        </nav>
    </div>
    <div class="container">
        <h1>Add New Mentor</h1>
        <form action="addnewmentor.php" method="POST" enctype="multipart/form-data">
            <!-- Mentor Photo -->
            <div class="form-group">
                <label for="photo">Upload Mentor Photo:</label>
                <input type="file" id="photo" name="photo" accept="image/*">
            </div>

            <!-- Mentor Name -->
            <div class="form-group">
                <label for="name">Mentor Name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <!-- Mentor Bio -->
            <div class="form-group">
                <label for="bio">Bio:</label>
                <textarea id="bio" name="bio" rows="4" required></textarea>
            </div>

            <!-- Language Dropdown -->
            <div class="form-group">
                <label for="language">Language:</label>
                <select id="language" name="language" required>
                    <option value="Bangla">Bangla</option>
                    <option value="English">English</option>
                </select>
            </div>

            <!-- Response Time Dropdown -->
            <div class="form-group">
                <label for="response-time">Response Time:</label>
                <select id="response-time" name="response_time" required>
                    <option value="6 hours">6 hours</option>
                    <option value="12 hours">12 hours</option>
                    <option value="24 hours">24 hours</option>
                    <option value="48 hours">48 hours</option>
                    <option value="72 hours">72 hours</option>
                </select>
            </div>

            <!-- Industry Dropdown -->
            <div class="form-group">
                <label for="industry">Industry:</label>
                <select id="industry" name="industry" required>
                    <option value="Tech">Tech</option>
                    <option value="Finance">Finance</option>
                    <option value="Healthcare">Healthcare</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- Hourly Rate Section -->
            <div class="form-group">
                <label for="hourly-rate">Hourly Rate:</label>
                <div id="hourly-rate-container">
                    <div class="hourly-rate-entry">
                        <input type="text" name="hourly-rate-descriptions[]" placeholder="Description (e.g., 1 hour)" required>
                        <input type="number" name="hourly-rate-values[]" placeholder="Price (tk)" required>
                        <button type="button" class="remove-rate-btn">Remove</button>
                    </div>
                </div>
                <button type="button" id="add-hourly-rate-btn">Add Rate</button>
            </div>

            <!-- Company and Country -->
            <div class="form-group">
                <label for="company">Company:</label>
                <input type="text" id="company" name="company">
            </div>
            <div class="form-group">
                <label for="country">Country:</label>
                <select id="country" name="country" required>
                    <option value="Bangladesh">Bangladesh</option>
                    <option value="USA">USA</option>
                    <option value="UK">UK</option>
                    <option value="India">India</option>
                    <option value="Canada">Canada</option>
                </select>
            </div>

            <!-- Skills Section -->
            <div class="form-group">
                <label for="skills">Skills</label>
                <div id="skills-container">
                    <div class="skill-entry">
                        <input type="text" name="skills[]" placeholder="Enter Skill">
                        <button type="button" class="remove-skill-btn">Remove</button>
                    </div>
                </div>
                <button type="button" id="add-skill-btn">Add Skill</button>
            </div>

            <!-- Contact Section -->
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="whatsapp">WhatsApp:</label>
                <input type="text" id="whatsapp" name="whatsapp">
            </div>
            <div class="form-group">
                <label for="linkedin">LinkedIn:</label>
                <input type="url" id="linkedin" name="linkedin">
            </div>
            <div class="form-group">
                <label for="facebook">Facebook:</label>
                <input type="url" id="facebook" name="facebook">
            </div>

            <button type="submit">Submit</button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const skillsContainer = document.getElementById('skills-container');
            const addSkillBtn = document.getElementById('add-skill-btn');

            // Function to add a new skill input field
            function addSkillInput() {
                const skillEntry = document.createElement('div');
                skillEntry.classList.add('skill-entry');

                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'skills[]';
                input.placeholder = 'Enter Skill';
                skillEntry.appendChild(input);

                const removeBtn = document.createElement('button');
                removeBtn.textContent = 'Remove';
                removeBtn.type = 'button';
                removeBtn.classList.add('remove-skill-btn');
                skillEntry.appendChild(removeBtn);

                skillsContainer.appendChild(skillEntry);

                // Remove previous 'Add Skill' button and add a new one below the current form
                addSkillBtn.remove();
                const newAddSkillBtn = document.createElement('button');
                newAddSkillBtn.textContent = 'Add Another Skill';
                newAddSkillBtn.id = 'add-skill-btn';
                newAddSkillBtn.type = 'button';
                skillsContainer.appendChild(newAddSkillBtn);

                // Add event listener to new 'Add Skill' button
                newAddSkillBtn.addEventListener('click', addSkillInput);
            }

            // Initial 'Add Skill' button event listener
            addSkillBtn.addEventListener('click', addSkillInput);

            // Handle removal of skill input fields
            skillsContainer.addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-skill-btn')) {
                    event.target.parentElement.remove();
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const hourlyRateContainer = document.getElementById('hourly-rate-container');
            const addHourlyRateBtn = document.getElementById('add-hourly-rate-btn');

            // Function to add a new hourly rate input field
            function addHourlyRateInput() {
                const rateEntry = document.createElement('div');
                rateEntry.classList.add('hourly-rate-entry');

                const descriptionInput = document.createElement('input');
                descriptionInput.type = 'text';
                descriptionInput.name = 'hourly-rate-descriptions[]';
                descriptionInput.placeholder = 'Description (e.g., 1 hour)';
                descriptionInput.required = true;
                rateEntry.appendChild(descriptionInput);

                const valueInput = document.createElement('input');
                valueInput.type = 'number';
                valueInput.name = 'hourly-rate-values[]';
                valueInput.placeholder = 'Price (tk)';
                valueInput.required = true;
                rateEntry.appendChild(valueInput);

                const removeBtn = document.createElement('button');
                removeBtn.textContent = 'Remove';
                removeBtn.type = 'button';
                removeBtn.classList.add('remove-rate-btn');
                rateEntry.appendChild(removeBtn);

                hourlyRateContainer.appendChild(rateEntry);

                // Add event listener for removing the rate entry
                removeBtn.addEventListener('click', function() {
                    rateEntry.remove();
                });
            }

            // Initial event listener for adding hourly rates
            addHourlyRateBtn.addEventListener('click', addHourlyRateInput);
        });
    </script>
</body>

</html>