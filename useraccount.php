<?php
session_start();
if (!isset($_SESSION['user_id'])) {
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

$userId = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, gender, mobilenumber FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = $_POST['user_bio'];
    $photoPath = '';

    // Handle photo upload
    if (isset($_FILES['upload-photo']) && $_FILES['upload-photo']['error'] == 0) {
        $targetDir = "uploads/";
        $photoPath = $targetDir . basename($_FILES['upload-photo']['name']);
        $imageFileType = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));

        // Validate file is an image
        $check = getimagesize($_FILES['upload-photo']['tmp_name']);
        if ($check !== false) {
            // Move the file to the target directory
            if (move_uploaded_file($_FILES['upload-photo']['tmp_name'], $photoPath)) {
                echo "The file " . htmlspecialchars(basename($_FILES['upload-photo']['name'])) . " has been uploaded.";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "File is not an image.";
            $photoPath = ''; // Reset if invalid
        }
    }

    // Check if user profile exists
    $stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing profile
        $stmt = $conn->prepare("UPDATE user_profiles SET user_bio = ?, user_photo = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $bio, $photoPath, $userId);
    } else {
        // Insert new profile
        $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, user_bio, user_photo) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $bio, $photoPath);
    }

    if ($stmt->execute()) {
        echo "Profile updated successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Fetch user profile (if exists) for display
$stmt = $conn->prepare("SELECT user_photo, user_bio FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
} else {
    $profile = ['user_photo' => 'https://via.placeholder.com/150', 'user_bio' => ''];
}

// Fetch appointed rooms data for the logged-in user
$stmt = $conn->prepare("SELECT appointed_room_id, appointed_user_id, appointed_user_name, appointed_user_email FROM appointedrooms WHERE appointed_user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$appointedRoomsResult = $stmt->get_result();

$appointedRooms = [];
if ($appointedRoomsResult->num_rows > 0) {
    while ($row = $appointedRoomsResult->fetch_assoc()) {
        $appointedRooms[] = $row;
    }
}

// Fetch mentorship session details for the logged-in user
$stmt = $conn->prepare("SELECT r.session_id, r.mentor_id, r.session_time, r.session_price, r.communication_method, r.session_date, r.problem_description, r.status, m.name AS mentor_name 
                        FROM request_mentorship_session r 
                        JOIN uiumentorlist m ON r.mentor_id = m.id 
                        WHERE r.user_id = ? 
                        ORDER BY r.session_date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$mentorSessionResult = $stmt->get_result();

$mentorSessions = [];
if ($mentorSessionResult->num_rows > 0) {
    while ($row = $mentorSessionResult->fetch_assoc()) {
        $mentorSessions[] = $row;
    }
}

// Fetch products for the logged-in user
$stmt = $conn->prepare("SELECT product_name, price, status FROM products WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$productsResult = $stmt->get_result();

$products = [];
if ($productsResult->num_rows > 0) {
    while ($row = $productsResult->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<script>
    // Display profile photo if available
    document.getElementById('profile-photo').src = '<?php echo $profile['user_photo']; ?>';
    document.querySelector('.bio-section textarea').textContent = '<?php echo $profile['user_bio']; ?>';
</script>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Account | UIU Supplement</title>
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
            background-color: #f0f0f5;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            min-height: 100vh;
        }

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
            font-size: 22px;
            font-weight: bold;
            color: #333;
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

        nav ul li a.active,
        nav ul li a:hover {
            background-color: #f0f0f5;
            border-radius: 10px;
        }

        nav ul li a .nav-item {
            margin-left: 15px;
        }

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

        .main {
            flex: 1;
            margin-left: 250px;
            padding: 40px;
        }

        .profile-container {
            max-width: 900px;
            background-color: white;
            width: 800px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .profile-header img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            margin-right: 20px;
            object-fit: cover;
            position: relative;
        }

        .profile-header .upload-icon {
            position: absolute;
            right: 22px;
            bottom: 5px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            border-radius: 50%;
            padding: 5px;
            cursor: pointer;
        }

        .profile-header input[type="file"] {
            display: none;
        }

        .profile-info h1 {
            font-size: 32px;
            color: #333;
        }

        .profile-info h3 {
            color: #666;
        }

        .bio-section {
            margin-top: 20px;
        }

        .bio-section textarea {
            width: 100%;
            height: 80px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none;
        }

        .save-bio-button {
            background-color: #FF3300;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .save-bio-button:hover {
            background-color: #1F1F1F;
        }

        /* Initially hide save bio button */
        .save-bio-button {
            display: none;
        }

        .contact-button {
            background-color: #FF3300;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .contact-button:hover {
            background-color: #1F1F1F;
        }

        .profile-section h2 {
            font-size: medium;
            color: #FF3300;
            margin-bottom: 10px;
        }

        .profile-section p {
            color: #666;
        }

        .session-details {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .session-details p {
            font-size: 14px;
            color: #333;
        }

        /* Adding status styles for On Hold and Sold */
        .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .status.on-hold {
            color: darkorange;
        }

        .status.sold {
            color: lightgreen;
        }

        ul {
            list-style-type: none;
        }
    </style>
</head>

<body>
    <<div class="container">
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
                <li><a href="browsementors.php">
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

            <!-- Log Out Button -->
            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>

        <div class="main">
            <div class="container">
                <div class="profile-container">
                    <form action="useraccount.php" method="POST" enctype="multipart/form-data">
                        <div class="profile-header">
                            <div style="position: relative;">
                                <img src="<?php echo $profile['user_photo']; ?>" alt="User Photo" id="profile-photo" width="150" height="150">
                                <label for="upload-photo" class="upload-icon" id="photo-upload-icon">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="upload-photo" name="upload-photo">
                            </div>
                            <div class="profile-info">
                                <h1><?php echo $user['username']; ?></h1>
                                <h3><i class="fas fa-envelope"></i> <?php echo $user['email']; ?></h3>
                                <h3><i class="fas fa-user"></i> <?php echo $user['gender']; ?></h3>
                                <h3><i class="fas fa-phone"></i> <?php echo $user['mobilenumber']; ?></h3>
                            </div>
                        </div>

                        <div class="bio-section">
                            <h2>Bio</h2>
                            <textarea name="user_bio" id="user-bio" placeholder="Write a short bio..."><?php echo $profile['user_bio']; ?></textarea>
                            <button type="submit" class="save-bio-button" id="save-bio-button">Save Bio</button>
                        </div>
                    </form>

                    <div class="profile-section">
                        <h2>My Sell List</h2>
                        <?php if (!empty($products)): ?>
                            <ul>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                    // Determine the CSS class for the product status
                                    $statusClass = '';
                                    if ($product['status'] == 'On Hold') {
                                        $statusClass = 'on-hold';
                                    } elseif ($product['status'] == 'Sold') {
                                        $statusClass = 'sold';
                                    }
                                    ?>
                                    <li>
                                        <strong>Product Name:</strong> <?php echo $product['product_name']; ?><br>
                                        <strong>Price:</strong> $<?php echo $product['price']; ?><br>
                                        <strong>Status:</strong> <span class="status <?php echo $statusClass; ?>"><?php echo $product['status']; ?></span><br>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No products listed for sale.</p>
                        <?php endif; ?>

                        <h2>My Buying List</h2>
                        <p>No details available.</p>

                        <h2>Lost Product update</h2>
                        <p>No details available.</p>

                        <h2>My Rented Rooms</h2>
                        <?php if (!empty($appointedRooms)): ?>
                            <?php foreach ($appointedRooms as $room): ?>
                                <p>
                                    <strong>Room ID:</strong> <?php echo $room['appointed_room_id']; ?><br>
                                    <strong>User ID:</strong> <?php echo $room['appointed_user_id']; ?><br>
                                    <strong>User Name:</strong> <?php echo $room['appointed_user_name']; ?><br>
                                    <strong>User Email:</strong> <?php echo $room['appointed_user_email']; ?><br>
                                </p>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No rented rooms available.</p>
                        <?php endif; ?>

                        <h2>My Mentors Request Session</h2>
                        <?php if (!empty($mentorSessions)): ?>
                            <?php foreach ($mentorSessions as $session): ?>
                                <div class="session-details">
                                    <p>
                                        <strong>Session ID:</strong> <?php echo $session['session_id']; ?><br>
                                        <strong>Mentor Name:</strong> <?php echo $session['mentor_name']; ?><br>
                                        <strong>Session Time:</strong> <?php echo $session['session_time']; ?><br>
                                        <strong>Session Price:</strong> <?php echo $session['session_price']; ?><br>
                                        <strong>Communication Method:</strong> <?php echo $session['communication_method']; ?><br>
                                        <strong>Session Date:</strong> <?php echo $session['session_date']; ?><br>
                                        <strong>Problem Description:</strong> <?php echo $session['problem_description']; ?><br>
                                        <strong>Status:</strong> <?php echo $session['status']; ?><br>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No mentorship sessions available.</p>
                        <?php endif; ?>
                    </div>

                    <button class="contact-button" onclick="location.href='uiusupplementhomepage.php'">Back to Homepage</button>
                </div>
            </div>
        </div>
        <script>
            document.getElementById('upload-photo').addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.getElementById('profile-photo');
                        img.src = e.target.result;

                        // Hide upload icon after successful upload
                        document.getElementById('photo-upload-icon').classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Show 'Save Bio' button only when the bio is changed
            const bioTextArea = document.getElementById('user-bio');
            const saveBioButton = document.getElementById('save-bio-button');
            const originalBio = bioTextArea.value;

            bioTextArea.addEventListener('input', function() {
                if (bioTextArea.value !== originalBio) {
                    saveBioButton.style.display = 'block';
                } else {
                    saveBioButton.style.display = 'none';
                }
            });
        </script>
</body>

</html>