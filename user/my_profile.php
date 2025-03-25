<?php
include '../includes/connect_db.php';
include '../templates/header.php';

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "client") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch user details
$user_stmt = $conn->prepare("SELECT username, created_at FROM users WHERE user_id = ?");
$user_stmt->bind_param("s", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch user addresses
$address_stmt = $conn->prepare("SELECT address_id, address, postal_code FROM user_addresses WHERE user_id = ?");
$address_stmt->bind_param("s", $user_id);
$address_stmt->execute();
$addresses = $address_stmt->get_result();

// Handle username/password update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update"])) {
    $new_username = $_POST["username"];
    $old_password = $_POST["old_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    $pass_stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $pass_stmt->bind_param("s", $user_id);
    $pass_stmt->execute();
    $pass_result = $pass_stmt->get_result();
    $user_data = $pass_result->fetch_assoc();

    if (password_verify($old_password, $user_data["password"])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE user_id = ?");
            $update_stmt->bind_param("sss", $new_username, $hashed_password, $user_id);
            if ($update_stmt->execute()) {
                $_SESSION["username"] = $new_username;
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('Profile updated successfully!', 'success');
                    });
                </script>";
            }
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showNotification('New passwords do not match.', 'error');
                });
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotification('Incorrect old password.', 'error');
            });
        </script>";
    }
}

// Handle account deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_account"])) {
    $delete_password = $_POST["delete_confirmation_password"];
    
    $pass_stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $pass_stmt->bind_param("s", $user_id);
    $pass_stmt->execute();
    $pass_result = $pass_stmt->get_result();
    $user_data = $pass_result->fetch_assoc();
    
    if (password_verify($delete_password, $user_data["password"])) {
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $delete_stmt->bind_param("s", $user_id);
        if ($delete_stmt->execute()) {
            session_destroy();
            echo "<script>
                alert('Account deleted. Redirecting to home.'); 
                window.location='../auth/login.php';
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotification('Incorrect password. Account deletion failed.', 'error');
            });
        </script>";
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body {
        font-family: 'Lexend', sans-serif;
        background-color: #ffffff;
        color: #333;
        line-height: 1.6;
    }

    
    .container {
        display: flex;
        flex-direction: column;
        max-width: 900px;
        margin: 40px auto;
        gap: 24px;
    }
    
    
</style>
</head>
<body>
    <h1 class="page-title">My Account</h1>

    <div class="container">
        <!-- Profile Information and Update sections in one row -->
        <div class="section profile-section">
            <div>
                <h2 class="section-title">Profile Information</h2>
                <div class="profile-info">
                    <div class="profile-info-item">
                        <i class="fas fa-user"></i>
                        <span><strong>Username:</strong> <?= htmlspecialchars($user["username"]) ?></span>
                    </div>
                    <div class="profile-info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span><strong>Member since:</strong> <?= date('F d, Y', strtotime($user["created_at"])) ?></span>
                    </div>
                </div>
            </div>
            
            <div>
                <h2 class="section-title">Update Profile</h2>
                <form method="post">
                    <div class="input-group">
                        <label class="input-label" for="username">Username</label>
                        <input class="input-field" type="text" id="username" name="username" value="<?= htmlspecialchars($user["username"]) ?>" required>
                    </div>
                    
                    <div class="input-group">
                        <label class="input-label" for="old_password">Current Password</label>
                        <input class="input-field" type="password" id="old_password" name="old_password" required>
                    </div>
                    
                    <div class="input-group">
                        <label class="input-label" for="new_password">New Password</label>
                        <input class="input-field" type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="input-group">
                        <label class="input-label" for="confirm_password">Confirm New Password</label>
                        <input class="input-field" type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button class="button" type="submit" name="update">Update Profile</button>
                </form>
            </div>
        </div>
        
        <!-- Saved Addresses section -->
        <div class="section">
            <h2 class="section-title">Saved Addresses</h2>
            <?php if ($addresses->num_rows > 0): ?>
                <ul class="address-list">
                    <?php while ($address = $addresses->fetch_assoc()): ?>
                        <li class="address-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <?= htmlspecialchars($address["address"]) ?>
                                <div style="font-size: 14px; color: #666;">
                                    <?= htmlspecialchars($address["postal_code"]) ?>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You don't have any saved addresses yet.</p>
            <?php endif; ?>
        </div>
        
        <!-- Danger Zone section -->
        <div class="section">
            <h2 class="section-title">Danger Zone</h2>
            <p class="danger-zone-warning">Once you delete your account, there is no going back. Please be certain.</p>
            <button class="button button-danger" id="delete-button" type="button">Delete My Account</button>
        </div>
    </div>
    
    <!-- Delete Account Modal -->
    <div id="delete-modal" class="modal">
        <div class="modal-content">
            <h2 class="modal-title">Delete Account</h2>
            <p>Are you sure you want to delete your account? This action cannot be undone.</p>
            <p>All your data will be permanently removed from our system.</p>
            
            <form method="post">
                <div class="input-group">
                    <label class="input-label" for="delete_confirmation_password">Enter your password to confirm</label>
                    <input class="input-field" type="password" id="delete_confirmation_password" name="delete_confirmation_password" required>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" id="cancel-delete" class="button">Cancel</button>
                    <button type="submit" name="delete_account" class="button button-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Notification element -->
    <div id="notification" class="notification"></div>
    
    <script>
        // Modal functionality
        const deleteButton = document.getElementById('delete-button');
        const deleteModal = document.getElementById('delete-modal');
        const cancelDelete = document.getElementById('cancel-delete');
        
        deleteButton.addEventListener('click', function() {
            deleteModal.style.display = 'flex';
        });
        
        cancelDelete.addEventListener('click', function() {
            deleteModal.style.display = 'none';
        });
        
        window.addEventListener('click', function(event) {
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
        
        // Notification system
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = 'notification ' + type;
            notification.classList.add('visible');
            
            setTimeout(function() {
                notification.classList.remove('visible');
            }, 4000);
        }
    </script>
</body>
</html>