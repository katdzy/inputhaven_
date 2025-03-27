<?php
include '../includes/connect_db.php';
include '../templates/header.php';
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$selected_products = isset($_POST["selected_products"]) ? $_POST["selected_products"] : [];

if (empty($selected_products)) {
    echo "No items selected for checkout.";
    exit();
}

$product_ids = implode(",", array_fill(0, count($selected_products), "?"));
$sql = "SELECT cart.product_id, cart.quantity, products.name, products.price, products.image, products.stock 
        FROM cart 
        INNER JOIN products ON cart.product_id = products.product_id 
        WHERE cart.user_id = ?
        AND cart.product_id IN ($product_ids)";
$query = $conn->prepare($sql);

if (!$query) {
    die("Query preparation failed: " . $conn->error);
}

$params = array_merge([$user_id], $selected_products);
$types = "s" . str_repeat("i", count($selected_products));
$query->bind_param($types, ...$params);
if (!$query->execute()) {
    die("Query execution failed: " . $query->error);
}

$result = $query->get_result();
$query->close();
$shipping_fee = 65;
$total_price = $shipping_fee;
$cart_items = [];

while ($row = $result->fetch_assoc()) {
    $row["subtotal"] = $row["price"] * $row["quantity"];
    $total_price += $row["subtotal"];
    $cart_items[] = $row;
}

// Fetch shipping couriers
$shipping_options = [];
$shipping_query = $conn->query("SELECT courier_id, courier_name FROM shipping_couriers");
while ($row = $shipping_query->fetch_assoc()) {
    $shipping_options[] = $row;
}

// Fetch payment methods
$payment_methods = [];
$payment_query = $conn->query("SELECT payment_id, method_name FROM payment_methods");
while ($row = $payment_query->fetch_assoc()) {
    $payment_methods[] = $row;
}

// Fetch user's saved addresses
$saved_addresses = [];
$address_query = $conn->prepare("SELECT address_id, address, postal_code FROM user_addresses WHERE user_id = ?");
if ($address_query) {
    $address_query->bind_param("s", $user_id);
    $address_query->execute();
    $address_result = $address_query->get_result();
    while ($row = $address_result->fetch_assoc()) {
        $saved_addresses[] = $row;
    }
    $address_query->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/checkout.css">
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
    <script>
    function togglePaymentInput() {
        var paymentMethod = document.getElementById("payment_method").value;
        document.getElementById("payment_details").style.display = (paymentMethod !== "5") ? "block" : "none";
    }
    
    function toggleAddressForm() {
    const addressType = document.querySelector('input[name="address_option"]:checked').value;
    const savedSection = document.getElementById("saved_address_section");
    const newForm = document.getElementById("new_address_form");
    
    if (addressType === "saved") {
        // Show saved addresses with fade-in animation
        savedSection.style.display = "grid";
        newForm.style.display = "none";

        const selectedAddress = document.querySelector('.address-card.selected');
        if (!selectedAddress && document.querySelector('.address-card')) {
            selectAddress(document.querySelector('.address-card').getAttribute('id').replace('address-card-', ''));
        }
    } else {
        savedSection.style.display = "none";
        newForm.style.display = "block";
        
        setTimeout(() => {
            document.getElementById('postal_code').focus();
        }, 300);
    }
}

function selectAddress(addressId) {
    document.querySelectorAll('.address-card').forEach(card => {
        card.classList.remove('selected');
    });
    

    const selectedCard = document.getElementById('address-card-' + addressId);
    selectedCard.classList.add('selected');
    

    selectedCard.style.transform = 'scale(1.03)';
    setTimeout(() => {
        selectedCard.style.transform = '';
    }, 200);
    

    document.getElementById('selected_address_id').value = addressId;
}


window.onload = function() {
    toggleAddressForm();
    togglePaymentInput();
    

    document.querySelectorAll('.address-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = 'translateY(-3px)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = '';
            }
        });
    });
}
    </script>
</head>
<body>

    <h1>Checkout</h1>

    <div class="checkout-container">
        <h3>Order Summary</h3>
        
        <div class="product-container">
            <?php foreach ($cart_items as $item): ?>
            <div class="product-pill">
                <img class="product-image" src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <div class="product-details">
                    <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="product-price">₱<?= number_format($item['price'], 2) ?></div>
                    <div class="product-quantity"><?= (int)$item['quantity'] ?></div>
                    <div class="product-subtotal">₱<?= number_format($item['subtotal'], 2) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="order-summary">
            <div class="summary-row">
                <span>Shipping Fee</span>
                <span>₱<?= number_format($shipping_fee, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Total Price</span>
                <span>₱<?= number_format($total_price, 2) ?></span>
            </div>
        </div>
        
        <h3>Shipping & Payment Details</h3>
<form action="process_order.php" method="post">
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
    </div>


    <div class="address-selection full-width">
        <h4 class="shipping-address-title">Shipping Address</h4>
        
        <div class="address-options">
            <div class="address-option">
                <input type="radio" id="saved_address" name="address_option" value="saved" <?= (!empty($saved_addresses)) ? 'checked' : 'disabled' ?> onchange="toggleAddressForm()">
                <label for="saved_address">Use a saved address</label>
            </div>
            
            <div class="address-option">
                <input type="radio" id="new_address" name="address_option" value="new" <?= (empty($saved_addresses)) ? 'checked' : '' ?> onchange="toggleAddressForm()">
                <label for="new_address">Use a new address</label>
            </div>
        </div>
        

        <div id="saved_address_section" class="saved-addresses">
            <?php if (empty($saved_addresses)): ?>
                <p>You don't have any saved addresses yet.</p>
            <?php else: ?>
                <?php foreach ($saved_addresses as $index => $address): ?>
                    <div id="address-card-<?= $address['address_id'] ?>" class="address-card <?= ($index === 0) ? 'selected' : '' ?>" onclick="selectAddress(<?= $address['address_id'] ?>)">
                        <p><?= htmlspecialchars($address['address']) ?></p>
                        <p>Postal Code: <?= htmlspecialchars($address['postal_code']) ?></p>
                    </div>
                <?php endforeach; ?>
                <input type="hidden" id="selected_address_id" name="selected_address_id" value="<?= $saved_addresses[0]['address_id'] ?? '' ?>">
            <?php endif; ?>
        </div>
        
        <div id="new_address_form" class="new-address-form">
            <div class="form-group">
                <label for="postal_code">Postal Code:</label>
                <input type="text" id="postal_code" name="postal_code">
            </div>
            
            <div class="form-group full-width">
                <label for="address">Address:</label>
                <textarea id="address" name="address"></textarea>
            </div>
            
            <div class="save-address-checkbox">
                <input type="checkbox" id="save_address" name="save_address" value="1">
                <label for="save_address">Save this address for future use</label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="shipping_courier">Shipping Courier:</label>
        <select id="shipping_courier" name="shipping_courier">
            <?php foreach ($shipping_options as $option): ?>
                <option value="<?= $option['courier_id'] ?>"><?= $option['courier_name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="payment_method">Payment Method:</label>
        <select id="payment_method" name="payment_method" onchange="togglePaymentInput()">
            <?php foreach ($payment_methods as $method): ?>
                <option value="<?= $method['payment_id'] ?>"><?= $method['method_name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="payment_details" class="form-group full-width" style="display: none;">
        <label for="payment_details_input">Card/Mobile Number:</label>
        <input type="text" id="payment_details_input" name="payment_details">
    </div>

    <?php foreach ($selected_products as $product_id): ?>
        <input type="hidden" name="selected_products[]" value="<?= htmlspecialchars($product_id) ?>">
    <?php endforeach; ?>
    
    <input type="hidden" name="total_price" value="<?= $total_price ?>">

    <button type="submit" name="confirm_order" class="checkout-button">Confirm Order</button>
</form>
    </div>

</body>
</html>