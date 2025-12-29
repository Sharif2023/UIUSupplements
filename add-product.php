<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Include centralized configuration
require_once 'config.php';

// Get database connection
$conn = getDbConnection();

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

$successMessage = '';
$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $targetDir = "imgOfSell/";
    
    // Create the upload directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
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
                $errorMessage = "Error uploading the image.";
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
                $successMessage = "Product added successfully!";
            } else {
                $errorMessage = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $errorMessage = "File is not an image.";
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
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        /* Page-specific styles for Add Product form */
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

        /* Category select styling */
        #category {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            font-weight: 500;
            border: 2px solid #1F1F1F;
            border-radius: 5px;
            background-color: #f8f9fa;
            color: #333;
            outline: none;
            transition: all 0.3s ease-in-out;
            appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24'%3E%3Cpath d='M7 10l5 5 5-5z' fill='%233498db'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 15px;
        }

        #category:hover {
            border-color: #FF3300;
        }

        #category:focus {
            border-color: #2c3e50;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }

        #category option {
            background: #ffffff;
            color: #333;
            padding: 10px;
        }

        #category:disabled {
            background: #e9ecef;
            color: #999;
            cursor: not-allowed;
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

        textarea {
            width: 100%;
            height: 100px;
            padding: 12px 20px;
            box-sizing: border-box;
            border: 2px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            resize: none;
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

        /* Success/Error Modal Styles */
        .result-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
        }

        .result-modal.active {
            display: flex;
        }

        .result-modal-content {
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            color: white;
            text-align: center;
            animation: modalSlideIn 0.3s ease;
        }

        .result-modal-content.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .result-modal-content.error {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px) scale(0.9);
                opacity: 0;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .result-modal-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .result-modal h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .result-modal p {
            margin: 0 0 25px 0;
            opacity: 0.9;
            font-size: 16px;
        }

        .result-modal-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
            background: white;
            color: #333;
        }

        .result-modal-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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
                    <textarea class="form-control" id="description" name="description" required></textarea>
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

        <!-- Success/Error Modal -->
        <div id="result-modal" class="result-modal">
            <div id="result-modal-content" class="result-modal-content">
                <div id="result-modal-icon" class="result-modal-icon"></div>
                <h2 id="result-modal-title"></h2>
                <p id="result-modal-message"></p>
                <button class="result-modal-btn" onclick="closeResultModal()">OK</button>
            </div>
        </div>

        <script>
            // Check for success or error messages from PHP
            const successMessage = <?php echo json_encode($successMessage); ?>;
            const errorMessage = <?php echo json_encode($errorMessage); ?>;

            function showResultModal(type, title, message) {
                const modal = document.getElementById('result-modal');
                const content = document.getElementById('result-modal-content');
                const icon = document.getElementById('result-modal-icon');
                const titleEl = document.getElementById('result-modal-title');
                const messageEl = document.getElementById('result-modal-message');

                content.classList.remove('success', 'error');
                content.classList.add(type);

                if (type === 'success') {
                    icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                    titleEl.textContent = title || 'Success!';
                } else {
                    icon.innerHTML = '<i class="fas fa-times-circle"></i>';
                    titleEl.textContent = title || 'Error!';
                }

                messageEl.textContent = message;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeResultModal() {
                document.getElementById('result-modal').classList.remove('active');
                document.body.style.overflow = '';
                
                // Redirect to Sell page on success
                if (successMessage) {
                    window.location.href = 'SellAndExchange.php';
                }
            }

            // Show modal on page load if there's a message
            document.addEventListener('DOMContentLoaded', function() {
                if (successMessage) {
                    showResultModal('success', 'Product Added!', successMessage);
                } else if (errorMessage) {
                    showResultModal('error', 'Oops!', errorMessage);
                }
            });

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && document.getElementById('result-modal').classList.contains('active')) {
                    closeResultModal();
                }
            });
        </script>
    </div>
    <footer class="footer">
        <div class="social-icons">
            <a href="https://www.facebook.com/sharif.me2018"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="http://uiusupplements.yzz.me/"><i class="fab fa-google"></i></a>
            <a href="https://www.instagram.com/shariful_islam10"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="https://www.github.com/sharif2023"><i class="fab fa-github"></i></a>
        </div>
        <div class="copyright">
            &copy; 2020 Copyright: <a href="https://www.youtube.com/@SHARIFsCODECORNER">Sharif Code Corner</a>
        </div>
    </footer>
    <script src="assets/js/index.js"></script>
</body>

</html>
