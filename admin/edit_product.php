<?php
include '../includes/connect_db.php';
include '../templates/admin_header.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Validate and get product_id
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    die("Invalid product ID");
}

$product_id = (int) $_GET['product_id'];

// Use prepared statements to fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// Categories
$categories = ["keyboard", "mouse", "accessories", "miscellaneous"];
$current_category = $product['category'] ?? ''; // Default to empty string if NULL

// Show any success or error messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../css/add-edit.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Lexend", sans-serif;
        }
        
        body {
            background-color: #f6f6f6;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        
       
    </style>
</head>
<body>
    
    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <section class="product-section">
            <div class="product-header">
                <h1 class="product-title">Edit Product</h1>
            </div>
            
            <div class="product-image-preview" id="preview-container">
                <?php if (!empty($product['image'])): ?>
                    <img id="image-preview" src="<?= htmlspecialchars($product['image']) ?>" alt="Product preview">
                <?php else: ?>
                    <img id="image-preview" src="images/placeholder.png" alt="Product preview" style="display: none;">
                    <p id="upload-text">Product image preview will appear here</p>
                <?php endif; ?>
            </div>
            
            <!-- Important: Use a single form that wraps both sections -->
            <form action="process.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">
                
                <div class="product-details">
                    <div class="product-description">
                        <h2 class="section-title">Product Information</h2>
                        
                        <div class="form-group">
                            <label for="name">Product Name:</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Product Description:</label>
                            <textarea id="description" name="description" required><?= htmlspecialchars($product['description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category:</label>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category) ?>" <?= ($current_category === $category) ? 'selected' : '' ?>>
                                        <?= ucfirst(htmlspecialchars($category)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <a href="admin.php" class="back-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 12L6 8L10 4" stroke="#012a57" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg> Back to The Admin Panel</a>
                    </div>
                    
                    <div class="checkout-section">
                        <h2 class="section-title">Product Details</h2>
                        
                        <div class="file-upload">
                            <label for="image">Product Image:</label>
                            <input type="file" id="image" name="image" accept="image/*">
                            <small>Leave empty to keep current image</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price (₱):</label>
                            <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($product['price']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock">Stock Quantity:</label>
                            <input type="number" id="stock" name="stock" min="0" value="<?= (int)$product['stock'] ?>" required>
                        </div>
                        
                        <div class="add-to-cart">
                            <button type="submit" name="update_product" class="submit-button">Update Product</button>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
    
    <script>
        // Preview uploaded image
        document.getElementById('image').addEventListener('change', function(e) {
            const previewImage = document.getElementById('image-preview');
            const uploadText = document.getElementById('upload-text');
            
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    previewImage.style.display = 'block';
                    if (uploadText) {
                        uploadText.style.display = 'none';
                    }
                };
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html>