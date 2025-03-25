<?php
include '../includes/connect_db.php';
include '../templates/admin_header.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Fetch products categorized
$categories = ["keyboard", "mouse", "accessories", "miscellaneous"];
$product_data = [];

foreach ($categories as $category) {
    $query = $conn->prepare("SELECT * FROM products WHERE category = ?");
    $query->bind_param("s", $category);
    $query->execute();
    $result = $query->get_result();
    $product_data[$category] = $result->fetch_all(MYSQLI_ASSOC);
    $query->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="../css/admin.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<head>
    <title>Admin Panel - Manage Products</title>
    <style>
        * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: "Lexend", sans-serif;
}

body {
    background-color: #f0f2f5;
    color: #333;
}
    </style>
    <script>
        function showTab(category) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(button => button.classList.remove('active'));

            document.getElementById(category).classList.add('active');
            document.getElementById(category + "-btn").classList.add('active');
        }
    </script>
</head>
<body>

    <h1>Welcome, Admin <?= $_SESSION["username"]; ?>!</h1>

    <h2>Manage Products</h2>

    <!-- Tabs -->
    <div class="tabs">
        <?php foreach ($categories as $category): ?>
            <button class="tab-button" id="<?= $category ?>-btn" onclick="showTab('<?= $category ?>')">
                <?= ucfirst($category) ?> (<?= count($product_data[$category]) ?>)
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Tab Content for Each Category -->
    <?php foreach ($categories as $category): ?>
        <div class="tab-content" id="<?= $category ?>">
            <?php foreach ($product_data[$category] as $product): ?>
                <div class="pill-container">
        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <div class="pill-content">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <div class="stock-info">Stocks: <?= (int)$product['stock'] ?></div>
            <div class="price">₱ <?= number_format($product['price'], 2) ?></div>
        </div>
        <div class="actions">
            <a href="edit_product.php?product_id=<?= $product['product_id'] ?>" class="edit-icon" title="Edit">
                <i class="fas fa-pen"></i>
            </a>
            <a href="process.php?delete=<?= $product['product_id'] ?>" class="delete-icon" title="Delete" onclick="return confirm('Delete this product?')">
                <i class="fas fa-trash"></i>
            </a>
    </div>
    </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <script>
        // Show the "keyboard" tab by default
        showTab('keyboard');
    </script>

</body>
</html>

<?php $conn->close(); ?>
