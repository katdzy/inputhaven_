<?php
include '../includes/connect_db.php';
function generateUserId($conn) {
    do {
        $user_id = strval(rand(10000000, 99999999));
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    $stmt->close();
    return $user_id;
}
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $role = ($_POST["role"] === "admin") ? "admin" : "client";
    
    // Check admin password if admin role is selected
    if ($role === "admin") {
        $admin_password = trim($_POST["admin_password"] ?? "");
        if ($admin_password !== "switch") {
            $error = "Invalid admin password!";
        }
    }
    
    if (empty($error)) {
        if (empty($username) || empty($password) || empty($confirm_password)) {
            $error = "All fields are required!";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match!";
        } else {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = "Username already taken!";
            } else {
                $stmt->close();
                $user_id = generateUserId($conn);
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
               
                $stmt = $conn->prepare("INSERT INTO users (user_id, username, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $user_id, $username, $hashed_password, $role);
               
                if ($stmt->execute()) {
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Sign-up failed. Try again!";
                }
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/signup.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
    <title>Sign Up</title>
    <style>
        .admin-password-field {
            display: none;
            margin-top: 10px;
        }
        .admin-password-message {
            display: none;
            font-size: 14px;
            color: #333;
            margin-top: 5px;
            margin-bottom: 10px;
            font-style: italic;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role-select');
            const adminPasswordField = document.getElementById('admin-password-field');
            const adminPasswordMessage = document.getElementById('admin-password-message');
            
            roleSelect.addEventListener('change', function() {
                if (this.value === 'admin') {
                    adminPasswordField.style.display = 'block';
                    adminPasswordMessage.style.display = 'block';
                    document.getElementById('admin-password').required = true;
                } else {
                    adminPasswordField.style.display = 'none';
                    adminPasswordMessage.style.display = 'none';
                    document.getElementById('admin-password').required = false;
                }
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <img src="../images/logo.svg" alt="Logo" style="width: 100px; margin-bottom: 10px;">
            <h1>Join input [haven]_</h1>
            <?php if (!empty($error)): ?>
                <p class="error" style="color: red;"> <?= htmlspecialchars($error) ?> </p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <select name="role" id="role-select">
                    <option value="client">Client</option>
                    <option value="admin">Admin</option>
                </select>
                <p id="admin-password-message" class="admin-password-message">Please enter admin authorization password to proceed.</p>
                <div id="admin-password-field" class="admin-password-field">
                    <input type="password" name="admin_password" id="admin-password" placeholder="Admin Password">
                </div>
                <button type="submit">Sign Up</button>
            </form>
            <p class="or-text">or</p>
            <button onclick="window.location.href='login.php'">Log In</button>
        </div>
        <div class="right-panel">
            <img src="../cover_photos/signup_hero.webp" alt="Hero Image">
        </div>
    </div>
</body>
</html>