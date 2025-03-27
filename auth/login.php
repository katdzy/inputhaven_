<?php
include '../includes/connect_db.php';

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = ""; // Initialize error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row["password"])) {
            $_SESSION["user_id"] = $row["user_id"];
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $row["role"];

            if ($row["role"] === "admin") {
                header("Location: ../admin/admin.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../images/key_fav.png">
    <title>Login</title>
    <style>
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <img src="../images/logo.svg" alt="Logo" style="width: 100px; margin-bottom: 10px;">
            <h1>Welcome to input [haven]<span class="cursor">_</span></h1>
            <?php if (!empty($error)): ?>
                <p class="error" style="color: red;"> <?= htmlspecialchars($error) ?> </p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Log In</button>
            </form>
            <p class="or-text">or</p>
            <button onclick="window.location.href='signup.php'">Sign Up</button>
        </div>
        <div class="right-panel">
            <img src="../cover_photos/login_hero.webp" alt="Hero Image">
        </div>
    </div>
</body>
</html>