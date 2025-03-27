<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/key_fav.png">
    <title>Admin Dashboard</title>
    <style>
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            background-color: #012a57;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 2px 8px rgba(1, 42, 87, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
            color: white;
        }

        .logo {
            cursor: pointer;
            height: 40px;
            filter: brightness(0) invert(1);
        }

        .navlinks {
            list-style: none;
            display: flex;
        }

        .navlinks li {
            display: inline-block;
            padding: 0 20px;
        }

        .navlinks li a {
            font-weight: 500;
            font-size: 16px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease 0s;
            padding: 8px 12px;
            border-radius: 4px;
        }

        .navlinks li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        header a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        header a:hover {
            color: rgba(255, 255, 255, 0.8);
        }
    </style>
</head>
<body>
    <header>
        <a href="../admin/admin.php">
            <img src="../images/logo.svg" class="logo" alt="logo">
        </a>
        <nav>
            <ul class="navlinks">
                <li><a href="../admin/add_product.php">Create Product</a></li>
                <li><a href="../admin/admin_orders.php">Manage Orders</a></li>
            </ul>
        </nav>
        <a href="../auth/logout.php">Logout</a>
    </header>
</body>
</html>