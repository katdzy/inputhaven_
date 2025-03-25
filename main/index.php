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
    <link rel="stylesheet" href="../css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
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
    </style>
</head>
<body>
    
    <div class="Hero1">
        <img src="../cover_photos/hero_index.webp" class="hero-img">
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
                <a href="../products/item.php?product_id=<?= $keyboard['product_id'] ?>">
                    <img src="<?= htmlspecialchars($keyboard['image']) ?>" alt="<?= htmlspecialchars($keyboard['name']) ?>">
                    <div class="arrival-info">
                        <h3><?= htmlspecialchars($keyboard['name']) ?></h3>
                        <p>₱ <?= number_format($keyboard['price'], 2) ?></p>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>

        <?php while ($mouse = $mice->fetch_assoc()): ?>
            <div class="arrival-card" data-category="mice">
                <a href="../products/item.php?product_id=<?= $mouse['product_id'] ?>">
                    <img src="<?= htmlspecialchars($mouse['image']) ?>" alt="<?= htmlspecialchars($mouse['name']) ?>">
                    <div class="arrival-info">
                        <h3><?= htmlspecialchars($mouse['name']) ?></h3>
                        <p>₱ <?= number_format($mouse['price'], 2) ?></p>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>

        <?php while ($accessory = $accessories->fetch_assoc()): ?>
            <div class="arrival-card" data-category="accessories">
                <a href="../products/item.php?product_id=<?= $accessory['product_id'] ?>">
                    <img src="<?= htmlspecialchars($accessory['image']) ?>" alt="<?= htmlspecialchars($accessory['name']) ?>">
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
            // Reset the result pointer
            $result->data_seek(0);
            while ($row = $result->fetch_assoc()): 
            ?>
                <div class="product-card">
                    <a href="../products/item.php?product_id=<?= $row['product_id'] ?>">
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <div class="product-info">
                            <h2><?= htmlspecialchars($row['name']) ?></h2>
                            <p>₱<?= number_format($row['price'], 2) ?></p>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    
    <?php include '../templates/footer.php';?>

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