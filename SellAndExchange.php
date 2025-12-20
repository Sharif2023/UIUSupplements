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
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        /* Page-specific styles for Sell and Exchange */
        .main {
            background-color: #f0f0f5;
        }

        /* Cards Section */
        .product-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .product-cards .card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
        }

        .product-cards .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .card-img-top {
            height: 180px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-cards .card:hover .card-img-top {
            transform: scale(1.05);
        }

        .card-body {
            padding: 20px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .card-text {
            color: #666;
            font-size: 14px;
        }

        /* Price Badge */
        .price-badge {
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 16px;
            display: inline-block;
            margin-top: 10px;
        }

        /* Category Tag */
        .category-tag {
            background: #e9ecef;
            color: #495057;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 8px;
        }

        /* Button group in cards */
        .card-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-bargain {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }

        .btn-bargain:hover {
            background: linear-gradient(135deg, #5a6268 0%, #4e555b 100%);
            color: white;
        }

        #bargain-success {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 15px 25px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            display: none;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        #drop-area {
            border: 2px dashed #2196F3;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            border-radius: 12px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        #drop-area:hover {
            border-color: #FF3300;
            background: #fff5f2;
        }

        #drop-area.dragging {
            background-color: #e3f2fd;
            border-color: #1976D2;
        }

        .bargain-list-btn {
            font-size: 0.8rem;
            position: absolute;
            bottom: 10px;
            right: 10px;
        }

        /* Modal styling */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid #f0f0f5;
            padding: 20px 25px;
        }

        .modal-body {
            padding: 25px;
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
<script src="assets/js/index.js"></script>

</html>