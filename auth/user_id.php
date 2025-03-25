<?php
include '../includes/connect_db.php';

// Function to generate a unique 8-digit user ID
function generateUserId($conn) {
    do {
        $user_id = strval(rand(10000000, 99999999)); // Generate 8-digit number
        $result = $conn->query("SELECT user_id FROM users WHERE user_id = '$user_id'");
    } while ($result->num_rows > 0); // Ensure it's unique
    return $user_id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT); // Hash password
    $role = $_POST["role"];

    $user_id = generateUserId($conn); // Generate unique user_id

    // Insert user into database
    $query = "INSERT INTO users (user_id, username, password, role) VALUES ('$user_id', '$username', '$password', '$role')";
    
    if ($conn->query($query)) {
        echo "Account created! Your User ID is: $user_id";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
