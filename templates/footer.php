<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <div class="footer-logo">input haven_</div>
                    <p class="footer-copyright">© Created by Karl Andrei Dungca and 6DWEB Group Members<br>All photos and videos used in this website are intended for placeholders. Copyright reserved to their respective owners.</p>
                </div>
               
                <div class="footer-links-column">
                    <h3 class="footer-column-title">Products</h3>
                    <a href="../products/accessories.php">Accessories</a>
                    <a href="../products/keyboards.php">Keyboards</a>
                    <a href="../products/mice.php">Mice</a>
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

    <style>
        /* Footer */
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
</body>
</html>