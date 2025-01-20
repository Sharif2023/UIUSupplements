<?php
session_start(); // Start the session

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
    $bargain_price = $_POST['bargain_price'];

    // Check if user_id is set in session
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Insert the bargain into the database
        $insert_sql = "INSERT INTO bargains (product_id, email, user_id, bargain_price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("issi", $product_id, $email, $user_id, $bargain_price);
        
        // Check if the bargain price is less than the current price
        $check_price_sql = "SELECT price FROM products WHERE id = ?";
        $check_stmt = $conn->prepare($check_price_sql);
        $check_stmt->bind_param("i", $product_id);
        $check_stmt->execute();
        $check_stmt->bind_result($current_price);
        $check_stmt->fetch();

        if ($bargain_price < $current_price) {
            if ($stmt->execute()) {
                echo '<div class="alert alert-success" role="alert">Bargain submitted successfully!</div>';
            } else {
                echo '<div class="alert alert-danger" role="alert">Error submitting bargain.</div>';
            }
        } else {
            echo '<div class="alert alert-danger" role="alert">Bargain price must be less than the current price.</div>';
        }
    } else {
        echo '<div class="alert alert-danger" role="alert">User not logged in. Please log in to submit a bargain.</div>';
    }
}

// Fetch bargain list
$bargain_list_sql = "SELECT b.product_id, b.email, b.user_id, b.bargain_price FROM bargains b JOIN products p ON b.product_id = p.id";
$bargain_list_result = $conn->query($bargain_list_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell or Exchange - UIU Supplements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
            min-height: 100vh;
        }

        /* Left Navigation */
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

        nav ul li a:hover {
            background-color: #f0f0f5;
            border-radius: 10px;
        }

        /* Log Out Button */
        .logout-btn {
            background-color: #ff5722;
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
            background-color: #e64a19;
        }

        /* Main Content */
        .main {
            flex: 1;
            margin-left: 250px;
            padding: 40px;
        }

        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .card {
            width: 100%;
            max-width: 300px; /* Set a max width for cards */
            margin-bottom: 20px;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
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
    </style>
</head>
<body>
    <div class="container">
        <nav>
            <ul>
                <li><a href="#" class="logo">
                    <h1 class="styled-title">UIU Supplement</h1>
                </a></li>
                <li><a href="uiusupplementhomepage.html">
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

        <section class="main">
            <div class="main-header">
                <h2>Available Products</h2>
                <div>
                    <a href="add-product.php" class="btn btn-success">Add Product</a>
                </div>
            </div>
            <div class="product-list">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="col mb-4">';
                        echo '<div class="card">';
                        echo '<img src="' . $row["image_path"] . '" class="card-img-top" alt="Product Image">';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . $row["product_name"] . '</h5>';
                        echo '<p class="card-text"><strong>Category:</strong> ' . ucfirst($row["category"]) . '</p>';
                        echo '<p class="card-text"><strong>Price:</strong> $' . $row["price"] . '</p>';
                        echo '<p class="card-text"><strong>Bargain Price:</strong> $' . $row["bargain_price"] . '</p>';
                        echo '<p class="card-text"><strong>Description:</strong> ' . $row["description"] . '</p>';
                        echo '<form method="post" action="">';
                        echo '<input type="hidden" name="product_id" value="' . $row["id"] . '">';
                        echo '<input type="text" name="bargain_price" placeholder="Enter your bargain price" required>';
                        echo '<input type="email" name="email" placeholder="Your email" required>';
                        echo '<input type="hidden" name="user_id" value="' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '') . '">'; // Ensure session user_id is set
                        echo '<button type="submit" class="btn btn-primary">Submit Bargain</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No products available.</p>';
                }
                ?>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
