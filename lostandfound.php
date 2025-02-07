<?php

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements"; // Change this to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the user is logged in by checking if 'user_id' is set in the session
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = $_SESSION['user_id']; // Set user_id from session

    if (isset($_POST['submit_found'])) {
        // Capture inputs for found items
        $user_id = $_POST['user_id'];
        $email = $_POST['email'];
        $category = $_POST['category'];
        $place = $_POST['foundPlace'];
        $time = $_POST['date_time'];
        $where_now = $_POST['where_now'];

        // Handle image upload
        $target_dir = "imgOfLost/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

        // Insert found item into the database
        $sql = "INSERT INTO lost_and_found (user_id, email, category, image_path, foundPlace, date_time, where_now) 
                VALUES ('$user_id', '$email', '$category', '$target_file', '$place', '$time', '$where_now')";
        $conn->query($sql);
    }

    // Handling claim submissions
    if (isset($_POST['submit_claim'])) {
        $item_id = $_POST['item_id'];
        $email = $_POST['claimant_email'];
        $identification_info = $_POST['identification_info'];
        $file_name = basename($_FILES["id_upload"]["name"]);
        $upload_dir = "uploads/";
        $upload_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES["id_upload"]["tmp_name"], $upload_file)) {
            $sql = "INSERT INTO claims (item_id, user_id, email, identification_info) 
                    VALUES ('$item_id', '$user_id', '$email', '$identification_info')";
            $conn->query($sql);

            $conn->query("UPDATE lost_and_found SET claim_status = 1 WHERE id = '$item_id'");
        }
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
    <title>Lost and Found | UIU Supplement</title>
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

        nav ul li a.active,
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

        .logout-btn:hover {
            background-color: #1F1F1F;
        }

        .main {
            flex: 1;
            margin-left: 250px;
            padding: 40px;
        }

        .main-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .add-listing-btn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .add-listing-btn:hover {
            background-color: #218838;
        }

        .submit-claim-btn,
        .add-lost-item-btn {
            background-color: #FF3300;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .submit-claim-btn:hover,
        .add-lost-item-btn:hover {
            background-color: #1F1F1F;
        }

        .main-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-gap: 20px;
            margin-top: 20px;
        }

        .card {
            display: flex;
            flex-direction: column;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
            background-color: #fff;
        }

        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .item-details {
            padding: 10px;
            font-size: 16px;
        }

        .card-btn {
            background-color: #FF3300;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            width: 100px;
            align-self: flex-end;
        }

        .card-btn:hover {
            background-color: #1F1F1F;
        }

        /* Modal styles */
        .modal {
            position: fixed;
            top: 0;
            margin-left: 250px;
            padding: 40px;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 100%;
            max-width: 400px;
            /* Set a max-width to keep it centered and sized nicely */
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
            /* Add a subtle shadow */
        }

        .close-button {
            font-size: 20px;
            font-weight: bold;
            float: right;
            cursor: pointer;
        }

        .modal-content label,
        .modal-content input,
        .modal-content select {
            display: block;
            width: 100%;
            margin-bottom: 10px;
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
                <li><a href="lostandfound.php">
                        <i class="fas fa-dumpster"></i>
                        <span class="nav-item" class="active">Lost and Found</span>
                    </a></li>
                <li><a href="shuttle_tracking_system.php">
                        <i class="fas fa-bus"></i>
                        <span class="nav-item">Shuttle Services</span>
                    </a></li>
            </ul>
            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>

        <section class="main">
            <div class="main-top">
                <h1 class="center-title">Lost and Found Items</h1>
                <a href="">
                    <button class="add-listing-btn"><i class="fa fa-plus"></i> Add Listing</button></a>
            </div>

            <div class="main-items">
                <?php
                if ($items->num_rows > 0) {
                    while ($row = $items->fetch_assoc()) {
                        echo '<div class="card">';
                        echo '<img class="item-image" src="' . $row["image_path"] . '" alt="Lost item">';
                        echo '<div class="item-details">';
                        echo '<p><strong>Item:</strong> ' . $row["category"] . '</p>';
                        echo '<p><strong>Found at:</strong> ' . $row["foundPlace"] . '</p>';
                        echo '<p><strong>Date:</strong> ' . $row["date_time"] . '</p>';
                        echo '<p><strong>Where now:</strong> ' . $row["where_now"] . '</p>';
                        echo '</div>';
                        echo '<button class="card-btn" onclick="openClaimModal(' . $row['id'] . ')">Claim</button>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No lost and found items available.</p>';
                }
                ?>
            </div>
        </section>
        <!-- Add Listing Modal Popup -->
        <div id="addListingModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-button" onclick="closeModal()">&times;</span>
                <form action="lostandfound.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>"> <!-- Auto-fill with session user ID -->

                    <label for="email">Email (optional):</label>
                    <input type="email" id="email" name="email">

                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="notebook">Notebook</option>
                        <option value="gadgets">Gadgets</option>
                        <option value="wallet">Wallet</option>
                        <option value="id_card">ID Card</option>
                        <option value="others">Others</option>
                    </select>

                    <label for="image">Upload Photo:</label>
                    <input type="file" id="image" name="image" accept="image/*">

                    <label for="foundPlace">Found Place:</label>
                    <input type="text" id="foundPlace" name="foundPlace" required>

                    <label for="date_time">Date and Time:</label>
                    <input type="datetime-local" id="date_time" name="date_time" required>

                    <label for="contact_info">Contact Info:</label>
                    <input type="text" id="contact_info" name="contact_info" required>

                    <label for="where_now">Where Now:</label>
                    <input type="text" id="where_now" name="where_now" required>

                    <button type="submit" name="submit_found" class="add-lost-item-btn">Submit</button>
                </form>
            </div>
        </div>

        <!-- Claim Modal -->
        <div id="claimModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-button" onclick="closeClaimModal()">&times;</span>
                <form action="lostandfound.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="claim_item_id" name="item_id">

                    <label for="claimant_email">Email Address:</label>
                    <input type="email" id="claimant_email" name="claimant_email" required>

                    <label for="id_upload">Upload Your ID:</label>
                    <input type="file" id="id_upload" name="id_upload" accept="image/*" required>

                    <label for="identification_info">Describe the Item (unique features, color, markings):</label>
                    <textarea id="identification_info" name="identification_info" required></textarea>

                    <button type="submit" name="submit_claim" class="submit-claim-btn">Submit Claim</button>
                </form>
            </div>
        </div>

        <script>
            // JavaScript for searching and sorting lost and found items
            function filterItems() {
                let searchInput = document.getElementById('search-item').value.toLowerCase();
                let items = document.querySelectorAll('.card');
                items.forEach(item => {
                    let itemName = item.querySelector('.item-details').innerText.toLowerCase();
                    if (itemName.includes(searchInput)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }

            function sortItems() {
                let sortOption = document.getElementById('sort-options').value;
                let itemsContainer = document.getElementById('items-container');
                let items = Array.from(itemsContainer.children);

                items.sort((a, b) => {
                    let dateA = new Date(a.getAttribute('data-date'));
                    let dateB = new Date(b.getAttribute('data-date'));
                    return sortOption === 'asc' ? dateA - dateB : dateB - dateA;
                });

                items.forEach(item => itemsContainer.appendChild(item));
            }

            // Sample function to dynamically add lost and found items (this should be replaced with server data fetching)
            function loadItems() {
                let itemsContainer = document.getElementById('items-container');
                for (let i = 0; i < 10; i++) {
                    let card = document.createElement('div');
                    card.classList.add('card');
                    card.setAttribute('data-date', `2024-10-${10 - i}`); // Random dates for testing

                    let details = document.createElement('div');
                    details.classList.add('item-details');
                    details.innerText = `Item ${i + 1} - Lost on 2024-10-${10 - i}`;

                    let button = document.createElement('button');
                    button.classList.add('card-btn');
                    button.innerText = 'Claim';

                    card.appendChild(details);
                    card.appendChild(button);
                    itemsContainer.appendChild(card);
                }
            }

            window.onload = loadItems;

            // JavaScript for opening and closing the modal
            function openModal() {
                document.getElementById('addListingModal').style.display = 'block';
            }

            function closeModal() {
                document.getElementById('addListingModal').style.display = 'none';
            }

            // Attach event listener to "Add Listing" button
            document.querySelector('.add-listing-btn').addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default anchor behavior
                openModal();
            });

            function openClaimModal(itemId) {
                document.getElementById('claim_item_id').value = itemId;
                document.getElementById('claimModal').style.display = 'block';
            }

            function closeClaimModal() {
                document.getElementById('claimModal').style.display = 'none';
            }
        </script>
    </div>
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