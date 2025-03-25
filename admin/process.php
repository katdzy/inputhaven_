<?php
session_start();
$conn = new mysqli("localhost", "root", "", "shop");

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = (int) $_GET['delete'];
    
    // First, retrieve the image path to delete the file
    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    // If the product exists and has an image, try to delete it from the server
    if ($product && !empty($product['image']) && file_exists($product['image'])) {
        // Only try to delete if it's not a placeholder or external URL
        if (strpos($product['image'], 'http') !== 0) {
            @unlink($product['image']);
        }
    }
    
    // Delete the product from the database
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Product successfully deleted!";
    } else {
        $_SESSION['error_message'] = "Error deleting product: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Function to get category folder
function getCategoryFolder($category) {
    // Normalize category to lowercase and remove spaces
    $category = strtolower(trim($category));
    
    // Map category to folder
    switch ($category) {
        case 'keyboard':
            return 'keyboard';
        case 'mouse':
            return 'mouse';
        case 'accessories':
            return 'accessories';
        default:
            return 'miscellaneous';
    }
}

// Handle adding new product
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    
    // Validate inputs
    if (empty($name) || empty($description) || empty($category) || $price <= 0 || $stock < 0) {
        $_SESSION['error_message'] = "All fields are required and values must be valid.";
        header("Location: add_product.php");
        exit();
    }
    
    // Get category folder
    $category_folder = getCategoryFolder($category);
    
    // Define base uploads directory and category subdirectory
    $base_dir = "../uploads/";
    $target_dir = $base_dir . $category_folder . "/";
    
    // Create base directory if it doesn't exist
    if (!file_exists($base_dir)) {
        mkdir($base_dir, 0777, true);
    }
    
    // Create category directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $image = "";
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $file_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        // Check if image file is an actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['error_message'] = "File is not an image.";
            header("Location: add_product.php");
            exit();
        }
        
        // Move uploaded file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        } else {
            $_SESSION['error_message'] = "Error uploading image.";
            header("Location: add_product.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Image is required.";
        header("Location: add_product.php");
        exit();
    }
    
    // Insert product into database
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image, category) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsss", $name, $description, $price, $stock, $image, $category);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Product added successfully!";
        header("Location: add_product.php");
    } else {
        $_SESSION['error_message'] = "Error adding product: " . $conn->error;
        header("Location: add_product.php");
    }
    
    $stmt->close();
    exit();
}

// Handle updating existing product
if (isset($_POST['update_product'])) {
    $product_id = (int) $_POST['product_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    
    // Validate inputs
    if (empty($name) || empty($description) || empty($category) || $price <= 0 || $stock < 0) {
        $_SESSION['error_message'] = "All fields are required and values must be valid.";
        header("Location: edit_product.php?product_id=" . $product_id);
        exit();
    }
    
    // Get current product info
    $stmt = $conn->prepare("SELECT image, category FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    $image = $product['image'];
    $old_category = $product['category'];
    
    // Handle image upload if a new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Get category folder
        $category_folder = getCategoryFolder($category);
        
        // Define base uploads directory and category subdirectory
        $base_dir = "../uploads/";
        $target_dir = $base_dir . $category_folder . "/";
        
        // Create base directory if it doesn't exist
        if (!file_exists($base_dir)) {
            mkdir($base_dir, 0777, true);
        }
        
        // Create category directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        // Check if image file is an actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['error_message'] = "File is not an image.";
            header("Location: edit_product.php?product_id=" . $product_id);
            exit();
        }
        
        // Move uploaded file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Delete old image if it exists
            if (!empty($product['image']) && file_exists($product['image']) && $product['image'] != $target_file) {
                // Only try to delete if it's not a placeholder or external URL
                if (strpos($product['image'], 'http') !== 0) {
                    @unlink($product['image']);
                }
            }
            
            $image = $target_file;
        } else {
            $_SESSION['error_message'] = "Error uploading image.";
            header("Location: edit_product.php?product_id=" . $product_id);
            exit();
        }
    } else if ($category != $old_category && !empty($image) && file_exists($image)) {
        // If category changed but no new image was uploaded, move the existing image to the new category folder
        
        // Get new category folder
        $category_folder = getCategoryFolder($category);
        
        // Define base uploads directory and category subdirectory
        $base_dir = "../uploads/";
        $target_dir = $base_dir . $category_folder . "/";
        
        // Create base directory if it doesn't exist
        if (!file_exists($base_dir)) {
            mkdir($base_dir, 0777, true);
        }
        
        // Create category directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Get file name from old path
        $file_name = basename($image);
        $target_file = $target_dir . $file_name;
        
        // Copy file to new location
        if (copy($image, $target_file)) {
            // Delete old file
            @unlink($image);
            $image = $target_file;
        }
    }
    
    // Update product in database
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ?, category = ? WHERE product_id = ?");
    $stmt->bind_param("ssdsssi", $name, $description, $price, $stock, $image, $category, $product_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Product updated successfully!";
        header("Location: edit_product.php?product_id=" . $product_id);
    } else {
        $_SESSION['error_message'] = "Error updating product: " . $conn->error;
        header("Location: edit_product.php?product_id=" . $product_id);
    }
    
    $stmt->close();
    exit();
}

// Redirect to admin panel if no action was taken
header("Location: admin.php");
exit();
?>