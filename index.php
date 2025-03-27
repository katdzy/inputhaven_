<?php
include 'includes/connect_db.php';


// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "client") {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
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


// Fetch products
$result = $conn->query("SELECT * FROM products");

// Fetch new arrivals (limit to 6 products)
$new_arrivals = $conn->query("SELECT * FROM products ORDER BY product_id DESC LIMIT 6");

// Fetch specific category products
$keyboards = $conn->query("SELECT * FROM products WHERE category = 'keyboard' LIMIT 3");
$mice = $conn->query("SELECT * FROM products WHERE category = 'mouse' LIMIT 3");
$accessories = $conn->query("SELECT * FROM products WHERE category = 'accessories' LIMIT 3");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>input haven_</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="images/key_fav.png">
    <style>
        /* Reset and General Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Lexend", sans-serif;
        }
        body {
            background-color: #ffffff;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

                /* Header and Navigation */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 10%;
    border-bottom: 0.8px solid #dddddd;
}

.logo {
    cursor: pointer;
    height: 40px;
}

.navlinks {
    list-style: none;
    display: flex;
}

.navlinks li {
    display: inline-block;
    padding: 0 30px;
}

.navlinks li a {
    font-family: "Lexend", sans-serif;
    font-weight: 500;
    font-size: 18px;
    color: #000000;
    text-decoration: none;
    transition: all 0.3s ease 0s;
}

.navlinks li a:hover {
    color: #0088a9;
}

/* Cart and Profile Section */
.cart-profile {
    display: flex;
    align-items: center;
    gap: 15px;
}

.cart-button {
    position: relative;
    display: inline-block;
}

.cart-image {
    width: 30px;
    height: auto;
}

.counter {
    position: absolute;
    top: -10px;
    right: -10px;
    background-color: #ff3333;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
}

/* Profile Dropdown */
.profile-dropdown {
    position: relative;
    display: inline-block;
}

.profile-btn {
    padding: 8px 12px;
    border: none;
    cursor: pointer;
    background-color: transparent;
}

.profile-btn img {
    width: 30px;
}

.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: white;
    min-width: 150px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    z-index: 10;
}

.dropdown-content a {
    display: block;
    text-decoration: none;
    color: black;
    padding: 10px;
    border-radius: 5px;
    background-color: white;
}

.dropdown-content a:hover {
    background-color: #ddd;
}

.profile-dropdown:hover .dropdown-content {
    display: block;
}

footer {
            background-color: #0a0a2e;
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }
        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
        }
        .footer-logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .footer-copyright {
            font-size: 14px;
            color: #bbb;
            max-width: 300px;
            line-height: 1.6;
        }
        .footer-links-column {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .footer-column-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .footer-links-column a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }
        .footer-links-column a:hover {
            color: white;
        }
        .shipping-icon {
            margin-right: 10px;
        }
    </style>
</head>
<body>
<header>
        <a href="index.php">
            <img src="images/logo.svg" class="logo" alt="logo">
        </a>    
        <nav>
            <ul class="navlinks">
                <li><a href="products/keyboards.php">Keyboards</a></li>
                <li><a href="products/mice.php">Mice</a></li>
                <li><a href="products/accessories.php">Accessories</a></li>
            </ul>
        </nav>
        <div class="cart-profile">
            <a href="cart/cart_view.php" class="cart-button">
                <img src="images/shopping-cart.png" class="cart-image" alt="Shopping Cart">
                <span class="counter"><?= $cart_count ?></span>
            </a>
            <div class="profile-dropdown">
                <button class="profile-btn"><img src="images/user.png" alt="User Profile"></button>
                <div class="dropdown-content">
                    <a href="user/my_profile.php">My Profile</a>
                    <a href="user/my_purchases.php">My Purchases</a>
                    <a href="auth/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    
    <div class="Hero1">
        <img src="cover_photos/hero_index.webp" class="hero-img">
    </div>

    <!-- New Arrivals Section -->
<section class="arrivals-section" id="arrivals">
    <h2 class="section-header">This Month's Arrivals</h2>

    <div class="arrivals-categories">
        <div class="category-tab active" data-category="keyboards">Keyboards</div>
        <div class="category-tab" data-category="mice">Mouse</div>
        <div class="category-tab" data-category="accessories">Accessories</div>
    </div>

    <div class="arrivals-container">
    <?php while ($keyboard = $keyboards->fetch_assoc()): ?>
    <div class="arrival-card" data-category="keyboards">
        <a href="products/item.php?product_id=<?= $keyboard['product_id'] ?>">
            <img src="uploads/<?= htmlspecialchars($keyboard['image']) ?>" alt="<?= htmlspecialchars($keyboard['name']) ?>">
            <div class="arrival-info">
                <h3><?= htmlspecialchars($keyboard['name']) ?></h3>
                <p>₱ <?= number_format($keyboard['price'], 2) ?></p>
            </div>
        </a>
    </div>
<?php endwhile; ?>

<?php while ($mouse = $mice->fetch_assoc()): ?>
    <div class="arrival-card" data-category="mice">
        <a href="products/item.php?product_id=<?= $mouse['product_id'] ?>">
            <img src="uploads/<?= htmlspecialchars($mouse['image']) ?>" alt="<?= htmlspecialchars($mouse['name']) ?>">
            <div class="arrival-info">
                <h3><?= htmlspecialchars($mouse['name']) ?></h3>
                <p>₱ <?= number_format($mouse['price'], 2) ?></p>
            </div>
        </a>
    </div>
<?php endwhile; ?>

<?php while ($accessory = $accessories->fetch_assoc()): ?>
    <div class="arrival-card" data-category="accessories">
        <a href="products/item.php?product_id=<?= $accessory['product_id'] ?>">
            <img src="uploads/<?= htmlspecialchars($accessory['image']) ?>" alt="<?= htmlspecialchars($accessory['name']) ?>">
            <div class="arrival-info">
                <h3><?= htmlspecialchars($accessory['name']) ?></h3>
                <p>₱ <?= number_format($accessory['price'], 2) ?></p>
            </div>
        </a>
    </div>
<?php endwhile; ?>

    </div>
</section>

    <!-- welcome Hero Section -->
    <section class="hero-section">
        <div class="tagline-backdrop"></div>
        <div class="hero-content">
            <h1 class="hero-title">WELCOME<br>TO<br>input haven_</h1>
            <h2 class="hero-subtitle">Your Keyboard and Mouse One-Stop Shop.</h2>
            <p class="hero-text">Level up your gear and choose from a high selection of premium keyboards and mice, recommended by our community.</p>
            <a href="#products" class="cta-button">Buy Now</a>
        </div>
    </section>

    <!-- Products Section (All Products) -->
    <section id="products">
        <h2 class="section-header">All Products</h2>
        <div class="product-container">
        <?php 
$result->data_seek(0);
while ($row = $result->fetch_assoc()): 
?>
    <div class="product-card">
        <a href="products/item.php?product_id=<?= $row['product_id'] ?>">
            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <div class="product-info">
                <h2><?= htmlspecialchars($row['name']) ?></h2>
                <p>₱<?= number_format($row['price'], 2) ?></p>
            </div>
        </a>
    </div>
<?php endwhile; ?>
        </div>
    </section>
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <div class="footer-logo">input haven_</div>
                    <p class="footer-copyright">© Created by Karl Andrei Dungca and 6DWEB Group Members<br>All photos and videos used in this website are intended for placeholders. Copyright reserved to their respective owners.</p>
                </div>
               
                <div class="footer-links-column">
                    <h3 class="footer-column-title">Products</h3>
                    <a href="products/accessories.php">Accessories</a>
                    <a href="products/keyboards.php">Keyboards</a>
                    <a href="products/mice.php">Mice</a>
                </div>
               
                <div class="footer-links-column">
                    <h3 class="footer-column-title">Contact Us</h3>
                    <a href="#">About Us</a>
                    <a href="mailto:support@int_haven.com">support@int_haven.com</a>
                    <a href="mailto:business@int_haven.com">business@int_haven.com</a>
                    <a href="tel:+639999999">(045) 9999-9999</a>
                </div>
            </div>
        </div>
    </footer>


    <script>
        document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        let selectedCategory = tab.getAttribute('data-category');

        document.querySelectorAll('.arrival-card').forEach(card => {
            card.style.display = card.getAttribute('data-category') === selectedCategory ? 'block' : 'none';
        });
    });
});
    
    </script>
</body>
</html>