<?php

session_start();

// Authentication check - redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements"; // Change this to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set user_id from session globally
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

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

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM lost_and_found WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $deleteId, $user_id);
    if ($stmt->execute()) {
        header("Location: lostandfound.php?my=1&deleted=1");
        exit();
    }
}

// Check if showing user's own items
$showMyItems = isset($_GET['my']) && $_GET['my'] == '1';

// Retrieve items - either all or just user's
if ($showMyItems) {
    $stmt = $conn->prepare("SELECT * FROM lost_and_found WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $items = $stmt->get_result();
} else {
    $items = $conn->query("SELECT * FROM lost_and_found ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost and Found | UIU Supplement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        /* Page-specific styles for Lost and Found */
        .main-top {
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

        /* Modal styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
            margin: 0;
            padding: 20px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: slideUp 0.4s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #FF3300, #ff6b4a);
            padding: 25px 30px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-header h2 i {
            font-size: 28px;
        }

        .close-button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 24px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .close-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 25px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-content form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .modal-content label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-content label i {
            color: #FF3300;
            font-size: 16px;
        }

        .modal-content input[type="email"],
        .modal-content input[type="file"],
        .modal-content textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .modal-content input[type="email"]:focus,
        .modal-content textarea:focus {
            outline: none;
            border-color: #FF3300;
            box-shadow: 0 0 0 3px rgba(255, 51, 0, 0.1);
        }

        .modal-content textarea {
            min-height: 80px;
            resize: vertical;
        }

        .modal-content input[type="file"] {
            padding: 10px;
            cursor: pointer;
        }

        .modal-content input[type="file"]::file-selector-button {
            background: linear-gradient(135deg, #FF3300, #ff6b4a);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 10px;
            transition: all 0.3s;
        }

        .modal-content input[type="file"]::file-selector-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 51, 0, 0.3);
        }

        .submit-claim-btn {
            background: linear-gradient(135deg, #FF3300, #ff6b4a);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .submit-claim-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 51, 0, 0.4);
        }

        .submit-claim-btn i {
            font-size: 18px;
        }

        .info-text {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 13px;
            color: #1565c0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .info-text i {
            margin-top: 2px;
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
                <li><a href="browsementors.php">
                        <i class="fas fa-user"></i>
                        <span class="nav-item">Mentorship</span>
                    </a></li>
                <li><a href="parttimejob.php">
                        <i class="fas fa-briefcase"></i>
                        <span class="nav-item">Jobs</span>
                    </a></li>
                <li><a href="lostandfound.php" class="active">
                        <i class="fas fa-dumpster"></i>
                        <span class="nav-item">Lost and Found</span>
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
                <h1 class="center-title"><?php echo $showMyItems ? 'My Lost Item Listings' : 'Lost and Found Items'; ?></h1>
                <a href="#" onclick="openModal(); return false;">
                    <button class="add-listing-btn"><i class="fa fa-plus"></i> Add Listing</button></a>
            </div>

            <!-- Tabs for All Items / My Items -->
            <div style="display: flex; gap: 15px; margin-bottom: 25px;">
                <a href="lostandfound.php" 
                   style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; <?php echo !$showMyItems ? 'background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%); color: white;' : 'background: #f0f0f5; color: #555;'; ?>">
                    <i class="fas fa-list"></i> All Items
                </a>
                <a href="lostandfound.php?my=1" 
                   style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; <?php echo $showMyItems ? 'background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%); color: white;' : 'background: #f0f0f5; color: #555;'; ?>">
                    <i class="fas fa-user"></i> My Listings
                </a>
            </div>

            <?php if (isset($_GET['deleted'])): ?>
                <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle"></i> Item deleted successfully!
                </div>
            <?php endif; ?>

            <div class="main-items">
                <?php
                if ($items->num_rows > 0) {
                    while ($row = $items->fetch_assoc()) {
                        $isOwner = ($row['user_id'] == $user_id);
                        echo '<div class="card">';
                        echo '<img class="item-image" src="' . htmlspecialchars($row["image_path"] ?? 'https://via.placeholder.com/150') . '" alt="Lost item">';
                        echo '<div class="item-details">';
                        echo '<p><strong>Item:</strong> ' . htmlspecialchars($row["category"] ?? 'N/A') . '</p>';
                        echo '<p><strong>Found at:</strong> ' . htmlspecialchars($row["foundPlace"] ?? 'N/A') . '</p>';
                        echo '<p><strong>Date:</strong> ' . htmlspecialchars($row["date_time"] ?? 'N/A') . '</p>';
                        echo '<p><strong>Where now:</strong> ' . htmlspecialchars($row["where_now"] ?? 'Not specified') . '</p>';
                        echo '</div>';
                        echo '<div style="display: flex; gap: 10px; margin-top: 10px;">';
                        if (!$isOwner) {
                            echo '<button class="card-btn" onclick="openClaimModal(' . $row['id'] . ')">Claim</button>';
                        } else {
                            echo '<a href="lostandfound.php?my=1&delete=' . $row['id'] . '" class="card-btn" style="background: #dc3545; text-decoration: none;" onclick="return confirm(\'Delete this listing?\')"><i class="fas fa-trash"></i> Delete</a>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    if ($showMyItems) {
                        echo '<div style="text-align: center; padding: 40px; background: white; border-radius: 12px;">
                            <i class="fas fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                            <h3>No Listings Yet</h3>
                            <p style="color: #666;">You haven\'t posted any lost item listings.</p>
                            <a href="#" onclick="openModal(); return false;" class="add-listing-btn" style="display: inline-block; margin-top: 15px;"><i class="fa fa-plus"></i> Add Listing</a>
                        </div>';
                    } else {
                        echo '<p>No lost and found items available.</p>';
                    }
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

    <!-- Claim Modal (Outside Container for Full Screen Centering) -->
    <div id="claimModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-hand-holding"></i>
                    Claim This Item
                </h2>
                <button class="close-button" onclick="closeClaimModal()" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    <span>Please provide accurate information to verify your claim. All details will be reviewed before approval.</span>
                </div>
                <form action="lostandfound.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="claim_item_id" name="item_id">

                    <div class="form-group">
                        <label for="claimant_email">
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </label>
                        <input type="email" id="claimant_email" name="claimant_email" placeholder="your.email@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="id_upload">
                            <i class="fas fa-id-card"></i>
                            Upload Your ID
                        </label>
                        <input type="file" id="id_upload" name="id_upload" accept="image/*" required>
                    </div>

                    <div class="form-group">
                        <label for="identification_info">
                            <i class="fas fa-clipboard-list"></i>
                            Describe the Item
                        </label>
                        <textarea id="identification_info" name="identification_info" placeholder="Describe unique features, color, markings, or any identifying details..." required></textarea>
                    </div>

                    <button type="submit" name="submit_claim" class="submit-claim-btn">
                        <i class="fas fa-paper-plane"></i>
                        Submit Claim
                    </button>
                </form>
            </div>
        </div>
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
<script src="assets/js/index.js"></script>

</html>
