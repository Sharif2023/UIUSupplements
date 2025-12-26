<?php
session_start();

// Admin authentication check - redirect to login if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = '';
$errorMessage = '';
$studentInfo = null;

// Handle form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentId = isset($_POST["student_id"]) ? trim($_POST["student_id"]) : null;
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
    $skills = array_map('trim', $skills);
    $skills = implode(',', $skills);

    // Handle hourly rates
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

    $hourlyRate = implode(',', $hourlyRates);

    // Handle photo upload
    $photo = $_FILES["photo"];
    $photoPath = '';

    if ($photo["error"] == UPLOAD_ERR_OK) {
        $photoName = 'mentor' . time() . '_' . basename($photo["name"]);
        $photoPath = 'uploads/' . $photoName;
        move_uploaded_file($photo["tmp_name"], $photoPath);
    }

    // Validate student ID if provided
    $linkedUserId = null;
    if (!empty($studentId)) {
        $checkUser = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
        $checkUser->bind_param("i", $studentId);
        $checkUser->execute();
        $userResult = $checkUser->get_result();
        
        if ($userResult->num_rows > 0) {
            $linkedUserId = $studentId;
            $studentInfo = $userResult->fetch_assoc();
        } else {
            $errorMessage = "Student ID not found in the system. Mentor created without user link.";
        }
        $checkUser->close();
    }

    // Insert mentor data into database with linked_user_id
    $sql = "INSERT INTO uiumentorlist (photo, name, bio, language, response_time, industry, hourly_rate, company, country, skills, email, whatsapp, linkedin, facebook, linked_user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssssi", $photoPath, $name, $bio, $language, $responseTime, $industry, $hourlyRate, $company, $country, $skills, $email, $whatsapp, $linkedin, $facebook, $linkedUserId);

    if ($stmt->execute()) {
        $mentorId = $conn->insert_id;
        
        // If student ID provided, update user's is_mentor flag
        if ($linkedUserId) {
            $updateUser = $conn->prepare("UPDATE users SET is_mentor = 1 WHERE id = ?");
            $updateUser->bind_param("i", $linkedUserId);
            $updateUser->execute();
            $updateUser->close();
            $successMessage = "Mentor added successfully and linked to user ID: $linkedUserId!";
        } else {
            $successMessage = "Mentor added successfully!";
        }
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Mentor - Admin Panel</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary-color: #ec4899;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-bg: #0f172a;
            --dark-secondary: #1e293b;
            --dark-tertiary: #334155;
            --light-bg: #f8fafc;
            --light-secondary: #f1f5f9;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-light: #94a3b8;
            --border-color: #e2e8f0;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--dark-secondary) 100%);
            padding: 30px 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--dark-tertiary);
            border-radius: 3px;
        }

        .sidebar-brand {
            padding: 0 30px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-brand h1 {
            font-family: "Montserrat", sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            text-transform: uppercase;
        }

        .title-word {
            animation: color-animation 4s linear infinite;
        }

        .title-word-1 { --color-1: #DF8453; --color-2: #3D8DAE; --color-3: #E4A9A8; }
        .title-word-2 { --color-1: #DBAD4A; --color-2: #ACCFCB; --color-3: #17494D; }

        @keyframes color-animation {
            0% { color: var(--color-1) }
            32% { color: var(--color-1) }
            33% { color: var(--color-2) }
            65% { color: var(--color-2) }
            66% { color: var(--color-3) }
            99% { color: var(--color-3) }
            100% { color: var(--color-1) }
        }

        .sidebar-menu {
            flex: 1;
            padding: 20px 0;
        }

        .menu-section {
            margin-bottom: 30px;
        }

        .menu-section-title {
            color: var(--text-light);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 30px 10px;
        }

        .menu-item {
            padding: 14px 30px;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
            font-size: 15px;
            position: relative;
            text-decoration: none;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(135deg, var(--primary-light), var(--secondary-color));
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .menu-item.active::before {
            transform: scaleY(1);
        }

        .menu-item i {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .logout-btn {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
            padding: 14px 30px;
            margin: 20px 30px 0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            text-decoration: none;
            display: block;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 40px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h1 i {
            color: var(--primary-color);
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .form-card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            padding: 25px 30px;
            color: white;
        }

        .form-card-header h2 {
            font-size: 20px;
            font-weight: 600;
        }

        .form-card-header p {
            opacity: 0.9;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-card-body {
            padding: 30px;
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .form-section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section-title i {
            color: var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-group label .required {
            color: var(--danger-color);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            font-size: 14px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            background-color: var(--light-bg);
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group .hint {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 6px;
        }

        /* Student ID Link Section */
        .student-link-section {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(236, 72, 153, 0.05));
            border: 2px dashed var(--primary-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .student-link-section .form-group {
            margin-bottom: 0;
        }

        .student-link-section label {
            color: var(--primary-color);
        }

        /* Skills & Rates Container */
        .dynamic-container {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 15px;
        }

        .dynamic-entry {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }

        .dynamic-entry:last-child {
            margin-bottom: 0;
        }

        .dynamic-entry input {
            flex: 1;
        }

        .btn-remove {
            padding: 8px 12px;
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-add {
            margin-top: 10px;
            padding: 10px 20px;
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-add:hover {
            background: #059669;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 16px 32px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Admin Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h1>
                <span class="title-word title-word-1">UIU</span>
                <span class="title-word title-word-2">Supplements</span>
            </h1>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-section-title">Main</div>
                <a href="adminpanel.php" class="menu-item">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-section-title">Management</div>
                <a href="adminpanel.php#users" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="addnewmentor.php" class="menu-item active">
                    <i class="fas fa-user-graduate"></i>
                    <span>Add Mentor</span>
                </a>
                <a href="adminpanel.php#mentors" class="menu-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Mentors</span>
                </a>
                <a href="adminpanel.php#sessions" class="menu-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Sessions</span>
                </a>
                <a href="addnewroom.php" class="menu-item">
                    <i class="fas fa-door-open"></i>
                    <span>Add Room</span>
                </a>
                <a href="adminpanel.php#rooms" class="menu-item">
                    <i class="fas fa-building"></i>
                    <span>Rooms</span>
                </a>
                <a href="adminpanel.php#products" class="menu-item">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Products</span>
                </a>
                <a href="adminpanel.php#lostandfound" class="menu-item">
                    <i class="fas fa-search"></i>
                    <span>Lost & Found</span>
                </a>
                <a href="adminpanel.php#shuttle" class="menu-item">
                    <i class="fas fa-bus"></i>
                    <span>Shuttle</span>
                </a>
            </div>
        </div>
        
        <a href="uiusupplementlogin.html" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Log Out
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-user-plus"></i> Add New Mentor</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <div class="form-card-header">
                <h2>Mentor Information</h2>
                <p>Fill in the details to add a new mentor to the platform</p>
            </div>
            
            <div class="form-card-body">
                <form action="addnewmentor.php" method="POST" enctype="multipart/form-data">
                    <!-- Student ID Link Section -->
                    <div class="student-link-section">
                        <div class="form-section-title">
                            <i class="fas fa-link"></i> Link to Student Account (Optional)
                        </div>
                        <div class="form-group">
                            <label for="student_id">Student ID (Graduated Student)</label>
                            <input type="text" id="student_id" name="student_id" 
                                   placeholder="Enter Student ID to link this mentor to their account (e.g., 011221078)"
                                   pattern="[0-9]{8,11}">
                            <p class="hint">
                                <i class="fas fa-info-circle"></i> 
                                If provided, this student will be able to access the Mentor Panel after logging in.
                            </p>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-user"></i> Basic Information
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name <span class="required">*</span></label>
                                <input type="text" id="name" name="name" required placeholder="Enter mentor's full name">
                            </div>
                            <div class="form-group">
                                <label for="photo">Profile Photo</label>
                                <input type="file" id="photo" name="photo" accept="image/*">
                            </div>
                            <div class="form-group full-width">
                                <label for="bio">Bio <span class="required">*</span></label>
                                <textarea id="bio" name="bio" required placeholder="Brief description about the mentor..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Details -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-briefcase"></i> Professional Details
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="language">Language <span class="required">*</span></label>
                                <select id="language" name="language" required>
                                    <option value="Bangla">Bangla</option>
                                    <option value="English">English</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="response_time">Response Time <span class="required">*</span></label>
                                <select id="response_time" name="response_time" required>
                                    <option value="6 hours">6 hours</option>
                                    <option value="12 hours">12 hours</option>
                                    <option value="24 hours">24 hours</option>
                                    <option value="48 hours">48 hours</option>
                                    <option value="72 hours">72 hours</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="industry">Industry <span class="required">*</span></label>
                                <select id="industry" name="industry" required>
                                    <option value="Tech">Tech</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Healthcare">Healthcare</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="company">Company</label>
                                <input type="text" id="company" name="company" placeholder="Current company or organization">
                            </div>
                            <div class="form-group">
                                <label for="country">Country <span class="required">*</span></label>
                                <select id="country" name="country" required>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="USA">USA</option>
                                    <option value="UK">UK</option>
                                    <option value="India">India</option>
                                    <option value="Canada">Canada</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Hourly Rates -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-money-bill-wave"></i> Hourly Rates
                        </div>
                        <div class="dynamic-container" id="hourly-rate-container">
                            <div class="dynamic-entry">
                                <input type="text" name="hourly-rate-descriptions[]" placeholder="Duration (e.g., 30 min)" required>
                                <input type="number" name="hourly-rate-values[]" placeholder="Price (tk)" required>
                                <button type="button" class="btn-remove" onclick="this.parentElement.remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addHourlyRate()">
                            <i class="fas fa-plus"></i> Add Rate
                        </button>
                    </div>

                    <!-- Skills -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-cogs"></i> Skills
                        </div>
                        <div class="dynamic-container" id="skills-container">
                            <div class="dynamic-entry">
                                <input type="text" name="skills[]" placeholder="Enter a skill">
                                <button type="button" class="btn-remove" onclick="this.parentElement.remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addSkill()">
                            <i class="fas fa-plus"></i> Add Skill
                        </button>
                    </div>

                    <!-- Contact Information -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-address-book"></i> Contact Information
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email <span class="required">*</span></label>
                                <input type="email" id="email" name="email" required placeholder="mentor@example.com">
                            </div>
                            <div class="form-group">
                                <label for="whatsapp">WhatsApp</label>
                                <input type="text" id="whatsapp" name="whatsapp" placeholder="01XXXXXXXXX">
                            </div>
                            <div class="form-group">
                                <label for="linkedin">LinkedIn</label>
                                <input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/username">
                            </div>
                            <div class="form-group">
                                <label for="facebook">Facebook</label>
                                <input type="url" id="facebook" name="facebook" placeholder="https://facebook.com/username">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus"></i> Add Mentor
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function addHourlyRate() {
            const container = document.getElementById('hourly-rate-container');
            const entry = document.createElement('div');
            entry.className = 'dynamic-entry';
            entry.innerHTML = `
                <input type="text" name="hourly-rate-descriptions[]" placeholder="Duration (e.g., 1 hour)" required>
                <input type="number" name="hourly-rate-values[]" placeholder="Price (tk)" required>
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(entry);
        }

        function addSkill() {
            const container = document.getElementById('skills-container');
            const entry = document.createElement('div');
            entry.className = 'dynamic-entry';
            entry.innerHTML = `
                <input type="text" name="skills[]" placeholder="Enter a skill">
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(entry);
        }
    </script>
</body>

</html>
