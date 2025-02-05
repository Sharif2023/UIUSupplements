<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements"; // Change this to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_found'])) {
        // Capture inputs for found items
        $user_id = $_POST['user_id'];
        $email = $_POST['email'];
        $category = $_POST['category'];
        $place = $_POST['foundPlace'];
        $time = $_POST['date_time'];
        $contact_info = $_POST['contact_info'];

        // Handle image upload
        $target_dir = "imgOfLost/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

        // Insert found item into the database
        $sql = "INSERT INTO lost_and_found (user_id, email, category, image_path, foundPlace, date_time, contact_info) 
                VALUES ('$user_id', '$email', '$category', '$target_file', '$place', '$time', '$contact_info')";
        $conn->query($sql);
    }

    if (isset($_POST['submit_claim'])) {
        // Capture claim inputs
        $item_id = $_POST['item_id'];
        $user_id = $_POST['user_id'];
        $email = $_POST['email'];
        $identification_info = $_POST['identification_info'];

        // Insert claim into the database
        $sql = "INSERT INTO claims ( user_id, email, identification_info) 
                VALUES ('$user_id', '$email', '$identification_info')";
        $conn->query($sql);

        // Update claim status
        $conn->query("UPDATE lost_and_found SET claim_status = 1 WHERE id = '$item_id'");
    }
}

// Retrieve all found items
$items = $conn->query("SELECT * FROM lost_and_found");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost and Found</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function openClaimModal(itemId) {
            document.getElementById('item_id').value = itemId;
            openModal('claimModal');
        }
    </script>

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
            display: flex;
            /* Use flexbox to align side by side */
            margin: 0;
            /* Remove default margin */
        }

        .left-side {
            display: flex;
            flex-wrap: wrap;
            min-height: 100vh;
        }

        /* Fixed Left Navigation */
        nav {
            width: 100%;
            max-width: 250px;
            background-color: #fff;
            padding: 10px;
            height: 100vh;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            overflow: auto;
            /* Allow scrolling within the nav if necessary */
        }

        .styled-title {
            font-size: 21px;
            font-weight: bold;
            color: #333;
        }

        nav ul {
            list-style-type: none;
            padding-top: 20px;
            margin-left: 0;
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

        nav ul li a:hover {
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

        .logout-btn i {
            margin-right: 10px;
        }

        .logout-btn:hover {
            background-color: #1F1F1F;
        }

        a {
            text-decoration: none;
            /* Removes underline */
            color: inherit;
            /* Keeps the color the same as non-hovered */
        }

        a:hover {
            text-decoration: none;
            /* Ensures no underline on hover */
            color: inherit;
            /* Prevents color change on hover */
        }

        .header-icons {
            display: flex;
            align-items: center;
            position: absolute;
            right: 40px;
            top: 20px;
        }

        .icon {
            margin: 0 15px;
            font-size: 25px;
            color: #555;
            cursor: pointer;
        }

        .icon:hover {
            color: #FF3300;
            /* Change color on hover */
        }

        /* Profile Icon Dropdown */
        .profile-icon {
            position: relative;
            cursor: pointer;
            color: #555;
            font-size: 25px;
            margin-left: 15px;
        }

        .profile-icon:hover {
            color: #FF3300;
        }

        .dropdown {
            display: none;
            /* Hidden by default */
            position: absolute;
            right: 0;
            background-color: #fff;
            font-size: medium;
            min-width: 220px;
            /* Slightly wider for aesthetics */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            z-index: 1;
            margin-top: 5px;
            /* Add space between the profile icon and dropdown */
        }

        .dropdown a {
            color: #555;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
            /* Smooth background color change */
        }

        .dropdown a:hover {
            background-color: #f0f0f5;
            /* Highlight on hover */
        }

        .dropdown.show {
            display: block;
            /* Show dropdown when it has the "show" class */
        }

        /* Main Content */
        .main {
            margin-left: 250px;
            /* Keep space for fixed nav */
            padding: 40px;
        }

        .main-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
            justify-content: center;
        }

        nav {
            width: 100%;
            height: auto;
            position: relative;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            overflow: auto;
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            font-family: "Poppins", sans-serif;
        }

        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: red;
            cursor: pointer;
        }

        .container {
            flex: 1;
            padding: 40px;
            margin-left: 250px;
            /* Adjust the margin to accommodate the fixed nav */
        }

        .card {
            font-family: "Poppins", sans-serif;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .card img {
            max-width: 100%;
            height: auto;
        }

        /* Styling for grid of cards */
        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            /* Adds gap between the cards */
            justify-content: space-between;
            /* Aligns items properly */
        }

        .item {
            flex: 1 1 30%;
            /* Adjust for 3 cards per row */
            max-width: 30%;
            /* Ensure cards don't exceed 3 per row */
            margin-bottom: 20px;
            /* Add space below each card */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            /* Shadow for card */
            transition: transform 0.3s;
            /* Smooth hover effect */
        }

        /* For screens smaller than 768px */
        @media (max-width: 768px) {
            .item {
                flex: 1 1 45%;
                /* 2 cards per row */
                max-width: 48%;
            }
        }

        /* For very small screens */
        @media (max-width: 576px) {
            .item {
                flex: 1 1 100%;
                /* Stack cards vertically */
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="left-side">
        <nav>
            <ul>
                <li><a href="#" class="logo">
                        <h1 class="styled-title">UIU Supplement</h1>
                    </a></li>
                <li><a href="#">
                        <i class="fas fa-home"></i>
                        <span class="nav-item">Home</span>
                    </a></li>
                <li><a href="SellandExchange/index.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span class="nav-item">Sell or Exchange</span>
                    </a></li>
                <li><a href="availablerooms.html">
                        <i class="fas fa-building"></i>
                        <span class="nav-item">Room Rent</span>
                    </a></li>
                <li><a href="browsementors.html">
                        <i class="fas fa-user"></i>
                        <span class="nav-item">Mentorship</span>
                    </a></li>
                <li><a href="parttimejob.html">
                        <i class="fas fa-briefcase"></i>
                        <span class="nav-item">Jobs</span>
                    </a></li>
                <li><a href="LostandFound/lostandfound.php">
                        <i class="fas fa-dumpster"></i>
                        <span class="nav-item">Lost and Found</span>
                    </a></li>
                <li><a href="shuttle_service.php">
                        <i class="fas fa-bus"></i>
                        <span class="nav-item">Shuttle Services</span>
                    </a></li>
                <li><a href="#">
                        <i class="fas fa-ad"></i>
                        <span class="nav-item">Promotions</span>
                    </a></li>
            </ul>

            <!-- Log Out Button -->
            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Log Out
            </a>
        </nav>
    </div>
    <div class="container">
        <h1 class="mt-4">Lost and Found Items</h1>

        <!-- Add Found Item Button -->
        <button class="btn btn-primary mb-4" onclick="openModal('foundItemModal')">Add Found Item</button>

        <!-- Found Item Modal -->
        <div id="foundItemModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('foundItemModal')">&times;</span>
                <form method="POST" enctype="multipart/form-data">
                    <h2>Add Found Item</h2>
                    <input type="text" name="user_id" class="form-control mb-2" placeholder="ID" required>
                    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                    <select name="category" class="form-control mb-2" required>
                        <option value="ID card">ID Card</option>
                        <option value="Cash">Cash</option>
                        <option value="Ornaments">Ornaments</option>
                        <option value="Notebook">Notebook</option>
                        <option value="Wallet">Wallet</option>
                        <option value="Waterbottle">Waterbottle</option>
                        <option value="Others">Others</option>
                    </select>
                    <input type="file" name="image" class="form-control mb-2" required>
                    <input type="text" name="foundPlace" class="form-control mb-2" placeholder="Found Place" required>
                    <input type="datetime-local" name="date_time" class="form-control mb-2" required>
                    <input type="text" name="contact_info" class="form-control mb-2" placeholder="Contact Information" required>
                    <button type="submit" name="submit_found" class="btn btn-success">Submit</button>
                </form>
            </div>
        </div>

        <!-- Display Found Items -->
        <?php while ($item = $items->fetch_assoc()) { ?>
            <div class="row">
                <div class="item col-md-3">
                    <div class="card">
                        <img src="<?= $item['image_path'] ?>" alt="Item Image" class="card-img-top">
                        <div class="card-body">
                            <p><strong>Category:</strong> <?= $item['category'] ?></p>
                            <p><strong>Found at:</strong> <?= $item['foundPlace'] ?></p>
                            <p><strong>Date and Time:</strong> <?= $item['date_time'] ?></p>
                            <button class="btn btn-warning" onclick="openClaimModal(<?= $item['id'] ?>)">
                                <i class="fa-solid fa-hand"></i> Claim
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Claim Modal -->
    <div id="claimModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('claimModal')">&times;</span>
            <form method="POST">
                <input type="hidden" name="item_id" id="item_id">
                <h2>Claim Item</h2>
                <input type="text" name="user_id" class="form-control mb-2" placeholder="ID" required>
                <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                <textarea name="identification_info" class="form-control mb-2" placeholder="Identification Information" required></textarea>
                <button type="submit" name="submit_claim" class="btn btn-success">Submit Claim</button>
            </form>
        </div>
    </div>

</body>

</html>