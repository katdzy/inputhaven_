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

// Fetch keyboard products only
$result = $conn->query("SELECT * FROM products WHERE category = 'keyboard'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Keyboards</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/products.css">
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
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
    </style>
</head>
<body>
    

    <img src="../cover_photos/keyboard-header.webp" class="cover-photo" alt="Keyboards" />

    <h1>Keyboards</h1>

    <div class="product-container">
        <?php while ($row = $result->fetch_assoc()): 
            $show_from = strpos(strtolower($row['name']), 'keyboard one') !== false;
        ?>
            <div class="product-card">
                <a href="item.php?product_id=<?= $row['product_id'] ?>">
                    <div class="product-image-container">
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    </div>
                    <div class="product-info">
                        <h2><?= htmlspecialchars($row['name']) ?></h2>
                        <p><?php if ($show_from): ?><span class="from-price">From</span><?php endif; ?>₱<?= number_format($row['price'], 2) ?></p>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>

    <?php include '../templates/footer.php';?>
</body>
</html>