<?php
include '../includes/connect_db.php';
include '../templates/header.php';

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "client") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Check for add to cart action
$cart_message = "";
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Validate input
    if ($product_id <= 0 || $quantity <= 0) {
        $cart_message = "Invalid product or quantity";
    } else {
        // First, check if the item already exists in the cart
        $check_sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $check_cart = $conn->prepare($check_sql);
        
        if ($check_cart === false) {
            $cart_message = "Database error (check cart): " . $conn->error;
        } else {
            $check_cart->bind_param("si", $user_id, $product_id);
            $check_cart->execute();
            $cart_result = $check_cart->get_result();
            
            if ($cart_result->num_rows > 0) {
                // Update existing cart item
                $update_sql = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
                $update_cart = $conn->prepare($update_sql);
                
                if ($update_cart === false) {
                    $cart_message = "Database error (update cart): " . $conn->error;
                } else {
                    $update_cart->bind_param("isi", $quantity, $user_id, $product_id);
                    if ($update_cart->execute()) {
                        if ($conn->affected_rows > 0) {
                            $cart_message = "Added to Cart!"; // Simplified message
                        } else {
                            $cart_message = "No changes made.";
                        }
                    } else {
                        $cart_message = "Failed to update cart: " . $update_cart->error;
                    }
                    $update_cart->close();
                }
            } else {
                // Add new cart item
                $add_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
                $add_cart = $conn->prepare($add_sql);
                
                if ($add_cart === false) {
                    $cart_message = "Database error (add to cart): " . $conn->error;
                } else {
                    $add_cart->bind_param("sii", $user_id, $product_id, $quantity);
                    
                    if ($add_cart->execute()) {
                        if ($conn->affected_rows > 0) {
                            $cart_message = "success"; // Changed to "success" for page reload
                        } else {
                            $cart_message = "Failed to add item.";
                        }
                    } else {
                        $cart_message = "Failed to add to cart: " . $add_cart->error;
                    }
                    $add_cart->close();
                }
            }
            $check_cart->close();
        }
    }
}

// Get product ID from URL
$product_id = $_GET['product_id'] ?? null;
if (!$product_id && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
}
if (!$product_id) {
    die("Product not found.");
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Product not found.");
}

// Fetch similar products
$similar_products = [];
$category = $product['category'] ?? '';
if ($category) {
    $similar_query = $conn->prepare("SELECT * FROM products WHERE category = ? AND product_id != ? LIMIT 4");
    $similar_query->bind_param("si", $category, $product_id);
    $similar_query->execute();
    $similar_result = $similar_query->get_result();
    while ($row = $similar_result->fetch_assoc()) {
        $similar_products[] = $row;
    }
    $similar_query->close();
}

$cart_count = 0;
$cart_count_query = $conn->prepare("SELECT COUNT(DISTINCT product_id) AS total FROM cart WHERE user_id = ?");
if ($cart_count_query) {
    $cart_count_query->bind_param("s", $user_id);
    $cart_count_query->execute();
    $cart_count_result = $cart_count_query->get_result();
    if ($cart_count_result) {
        $cart_count_row = $cart_count_result->fetch_assoc();
        $cart_count = $cart_count_row['total'] ?? 0;
    }
    $cart_count_query->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= htmlspecialchars($product['name']) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/item.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
    <script>
        // Auto-reload script when item is added to cart
        document.addEventListener('DOMContentLoaded', function() {
            var cartMessage = "<?= $cart_message ?>";
            if (cartMessage === "success") {
                window.location.reload();
            }
        });
    </script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Lexend", sans-serif;
        }

        a {
            text-decoration: none;
        }

        body {
            background-color: #ffffff;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

    </style>
</head>
<body>
 
    <div class="container">
    <section class="product-section">
        <div class="product-header">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
        </div>
        
        <div class="product-price">
            ₱<?= number_format($product['price'], 2) ?>
            <?php if (isset($product['original_price']) && $product['original_price'] > 0 && $product['original_price'] > $product['price']): ?>
            <span class="original-price">₱<?= number_format($product['original_price'], 2) ?></span>
            <?php endif; ?>
        </div>
        
        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
            
            <div class="product-details">
                <div class="product-description">
                    <h2 class="section-title">Overview</h2>
                    <p class="description-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                
                <div class="checkout-section">
                    <div class="shipping-info">
                        <h3 class="section-title">Shipping <span class="shipping-icon">📦</span></h3>
                        <ul>
                            <li>LBC, J&T, Flash Express, and Ninja Van</li>
                            <li>Price includes shipping fee</li>
                        </ul>
                    </div>
                    
                    <div class="stock-info">
                        Stocks: <?= (int)$product['stock'] ?>
                    </div>
                    
                    <form action="item.php?product_id=<?= $product['product_id'] ?>" method="post" class="add-to-cart">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <input type="number" name="quantity" min="1" max="<?= (int)$product['stock'] ?>" value="1" required>
                        <button type="submit" name="add_to_cart" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
                        <?php if (!empty($cart_message) && $cart_message !== "success"): ?>
                            <div class="simple-cart-message"><?= $cart_message ?></div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </section>
        
        <?php if (!empty($similar_products)): ?>
        <section class="similar-products">
            <h2 class="similar-title">You May Also Like:</h2>
            <div class="products-grid">
                <?php foreach ($similar_products as $similar): ?>
                <div class="product-card">
                    <a href="item.php?product_id=<?= $similar['product_id'] ?>">
                        <img src="<?= htmlspecialchars($similar['image']) ?>" alt="<?= htmlspecialchars($similar['name']) ?>">
                        <div class="product-card-content">
                            <h3 class="product-card-title"><?= htmlspecialchars($similar['name']) ?></h3>
                            <div class="product-card-price">₱<?= number_format($similar['price'], 2) ?></div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
    
    <?php include '../templates/footer.php';?>
</body>
</html>