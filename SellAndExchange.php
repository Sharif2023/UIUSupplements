<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, product_name, category, price, bargain_price, description, image_path FROM products";
$result = $conn->query($sql);

// Handle bargain submissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bargain_price'])) {
    $product_id = $_POST['product_id'];
    $email = $_POST['email'];
    $user_id = $_POST['user_id']; // Ideally, this should come from session data after login.
    $bargain_price = $_POST['bargain_price'];

    // Ensure bargain price is less than current price
    $check_price_sql = "SELECT price FROM products WHERE id = ?";
    $check_stmt = $conn->prepare($check_price_sql);
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $check_stmt->bind_result($current_price);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($bargain_price < $current_price) {
        // Insert the bargain into the database
        $insert_sql = "INSERT INTO bargains (product_id, email, user_id, bargain_price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("issi", $product_id, $email, $user_id, $bargain_price);

        if ($stmt->execute()) {
            echo '<div id="bargain-success" class="alert alert-success" role="alert">Bargain submitted successfully!</div>';
        } else {
            echo '<div class="alert alert-danger" role="alert">Error submitting bargain: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    } else {
        echo '<div class="alert alert-danger" role="alert">Bargain price must be less than the current price.</div>';
    }
}

// Fetch bargain list
$bargain_list_sql = "SELECT b.product_id, b.email, b.user_id, b.bargain_price 
                    FROM bargains b 
                    JOIN products 
                    p ON b.product_id = p.id";
$bargain_list_result = $conn->query($bargain_list_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Supplements - Product Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
            font-weight: bold;
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
            padding-left: 0px;
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

        .main-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .center-title {
            font-size: 24px;
            font-weight: bold;
            flex: 1;
            text-align: center;
        }

        .add-product-btn {
            margin-bottom: 20px;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            display: inline-block;
            font-size: 16px;
            text-decoration: none;
        }

        .add-product-btn:hover {
            background-color: #218838;
        }

        /* Cards Section */
        .product-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .product-cards .card {
            flex: 1 1 30%;
            max-width: 30%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-img-top {
            max-height: 150px;
            object-fit: cover;
        }

        .card-body {
            padding: 15px;
        }

        .card-title {
            font-size: 18px;
            font-weight: bold;
        }

        #bargain-success {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            font-size: 14px;
            display: none;
            /* Initially hidden */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #drop-area {
            border: 2px dashed #007bff;
            padding: 20px;
            text-align: center;
            cursor: pointer;
        }

        #drop-area.dragging {
            background-color: #f0f8ff;
        }

        .bargain-list-btn {
            font-size: 0.8rem;
            /* Smaller text */
            position: absolute;
            /* Positioning to the corner */
            bottom: 10px;
            /* Adjusts the vertical position */
            right: 10px;
            /* Adjusts the horizontal position */
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            .container {
                flex-direction: row;
            }

            nav {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main {
                margin-left: 0;
                padding: 20px;
            }
        }

        @media (max-width: 1200px) {
            .main {
                padding: 20px;
            }
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
                <li><a href="SellAndExchange.php" class="active">
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
                <button id="back" onclick="location.href='uiusupplementhomepage.php'" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Home
                </button>
                <h1 class="center-title">Sell and Exchange</h1>
            </div>
            <a href="add-product.php" class="add-product-btn">Add Product</a>
            <div class="product-cards">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="card">
                                    <img class="card-img-top" src="' . $row['image_path'] . '" alt="Product Image">
                                    <div class="card-body">
                                        <h5 class="card-title">' . $row['product_name'] . '</h5>
                                        <p class="card-text">Category: ' . $row['category'] . '</p>
                                        <p class="card-text">Price: ' . $row['price'] . '</p>
                                        <p class="card-text">Bargain Price: ' . $row['bargain_price'] . '</p>
                                        <p class="card-text">Description: ' . $row['description'] . '</p>
                                        <div class="card-buttons">
                                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#bargainModal" data-product-id="' . $row['id'] . '">Bargain</button>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bargainListModal">Bargain List</button>
                                        </div>
                                    </div>
                                </div>';
                    }
                } else {
                    echo "No products available.";
                }
                ?>
            </div>


            <!-- Modal for Bargain Submission -->
            <div class="modal fade" id="bargainModal" tabindex="-1" aria-labelledby="bargainModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bargainModalLabel">Submit Your Bargain</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" id="product_id" name="product_id">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="bargain_price" class="form-label">Bargain Price</label>
                                    <input type="number" class="form-control" id="bargain_price" name="bargain_price" required>
                                </div>
                                <input type="hidden" name="user_id" value="1"> <!-- Replace with dynamic user_id if available -->
                                <button type="submit" class="btn btn-primary">Submit Bargain</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Bargain List -->
            <div class="modal fade" id="bargainListModal" tabindex="-1" aria-labelledby="bargainListModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bargainListModalLabel">Bargain List</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Email</th>
                                        <th>User ID</th>
                                        <th>Bargain Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($bargain_list_result->num_rows > 0) {
                                        while ($bargain = $bargain_list_result->fetch_assoc()) {
                                            echo '<tr>';
                                            echo '<td>' . $bargain["product_id"] . '</td>'; // Adjust to fetch product name if available
                                            echo '<td>' . $bargain["email"] . '</td>';
                                            echo '<td>' . $bargain["user_id"] . '</td>';
                                            echo '<td>' . $bargain["bargain_price"] . '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="4">No bargains found.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>
        var bargainModal = document.getElementById('bargainModal');
        bargainModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var productId = button.getAttribute('data-product-id');
            var modalProductIdInput = document.getElementById('product_id');
            modalProductIdInput.value = productId;
        });
        document.addEventListener("DOMContentLoaded", function() {
            var successMessage = document.getElementById('bargain-success');

            if (successMessage) {
                successMessage.style.display = 'block'; // Show the message
                setTimeout(function() {
                    successMessage.style.display = 'none'; // Hide after 2 seconds
                }, 2000); // 2000 milliseconds = 2 seconds
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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