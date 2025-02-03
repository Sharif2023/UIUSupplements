<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

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

function resizeImage($file, $targetFile, $maxWidth, $maxHeight, $imageFileType)
{
    list($width, $height) = getimagesize($file);
    $ratio = $width / $height;

    if ($maxWidth / $maxHeight > $ratio) {
        $newWidth = $maxHeight * $ratio;
        $newHeight = $maxHeight;
    } else {
        $newHeight = $maxWidth / $ratio;
        $newWidth = $maxWidth;
    }

    $dst = imagecreatetruecolor($newWidth, $newHeight);

    if ($imageFileType == "jpg" || $imageFileType == "jpeg") {
        $src = imagecreatefromjpeg($file);
    } elseif ($imageFileType == "png") {
        $src = imagecreatefrompng($file);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save the resized image
    if ($imageFileType == "jpg" || $imageFileType == "jpeg") {
        imagejpeg($dst, $targetFile, 90);  // 90 is the quality percentage
    } elseif ($imageFileType == "png") {
        imagepng($dst, $targetFile, 9);  // 9 is the highest compression for PNG
    }

    imagedestroy($dst);
    imagedestroy($src);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $targetDir = "imgOfSell/";
    $targetFile = $targetDir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validate the image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        if ($_FILES["image"]["size"] > 500000) {  // If file size is larger than 500KB
            // Automatically resize the image
            resizeImage($_FILES["image"]["tmp_name"], $targetFile, 800, 800, $imageFileType);
        } else {
            // Move the uploaded image as it is
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                echo "Error uploading the image.";
                $uploadOk = 0;
            }
        }

        if ($uploadOk == 1) {
            // Insert product details into the database, including user_id
            $stmt = $conn->prepare("INSERT INTO products (product_name, category, price, description, image_path, user_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdssi", $productName, $category, $price, $description, $targetFile, $userId);

            // Fetch product details from the form
            $productName = $_POST["productName"];
            $category = $_POST["category"];
            $price = $_POST["price"];
            $description = $_POST["description"];
            $userId = $_SESSION['user_id'];  // Get user_id from session

            if ($stmt->execute()) {
                echo "Product added successfully.";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        echo "File is not an image.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
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
            background-color: #f0f0f5;
        }

        .container {
            display: flex;
            min-height: 100vh;
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

        /* Main Section */
        .main {
            flex: 1;
            margin-left: 250px;
            padding: 40px;
        }

        .main h1 {
            font-size: 30px;
            color: #333;
            text-align: center;
        }

        h2 {
            padding-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            padding: 5px;
        }


        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="file"] {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
            cursor: pointer;
        }

        .button-group {
            text-align: center;
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            background-color: #FF3300;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #1F1F1F;
        }
    </style>
</head>

<body>
    <div class="container">
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

        <section class="main">
            <button type="button" onclick="goBack()">Return Home</button>
            <h2>Add a New Product</h2>
            <form action="add-product.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="productName" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="productName" name="productName" required>
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-control" id="category" name="category">
                        <option value="book">Books</option>
                        <option value="gadget">Gadgets</option>
                        <option value="notebook">Notebooks</option>
                        <option value="bike">Bikes</option>
                        <option value="solution">Trimester Solution</option>
                        <option value="other">Others</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control" id="price" name="price" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <div id="drop-area">
                        <p>Drag and drop an image file here, or click to select a file.</p>
                        <input type="file" id="image" name="image" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </form>
        </section>

        <script>
            // Drag-and-drop functionality
            const dropArea = document.getElementById('drop-area');
            const fileInput = document.getElementById('image');

            dropArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropArea.classList.add('dragging');
            });

            dropArea.addEventListener('dragleave', () => {
                dropArea.classList.remove('dragging');
            });

            dropArea.addEventListener('drop', (e) => {
                e.preventDefault();
                fileInput.files = e.dataTransfer.files;
                dropArea.classList.remove('dragging');
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js">

        </script>
        <script>
            function goBack() {
                window.location.href = "SellAndExchange.php";
            }
        </script>
</body>

</html>