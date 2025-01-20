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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $targetDir = "imgOfSell/";
    $targetFile = $targetDir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validate the image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        if ($_FILES["image"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            echo "Sorry, only JPG, JPEG, PNG files are allowed.";
            $uploadOk = 0;
        }
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                // Insert product details into the database
                $stmt = $conn->prepare("INSERT INTO products (product_name, category, price, description, image_path) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdss", $productName, $category, $price, $description, $targetFile);

                $productName = $_POST["productName"];
                $category = $_POST["category"];
                $price = $_POST["price"];
                $description = $_POST["description"];

                if ($stmt->execute()) {
                    echo "Product added successfully.";
                } else {
                    echo "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Error uploading the image.";
            }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Swap Bangladesh</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="add-product.php">Add Product</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
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
    </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
