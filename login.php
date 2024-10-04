<?php
session_start();  // Start the session to manage logged-in state

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hciactivity";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Using prepared statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            // Set session variable to track login status
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_type'] = $row['user_type']; // Store user type in session

            // Redirect to appropriate dashboard based on user type
            if ($row['user_type'] == 'supplier') {
                header("Location: dashboard.php");
            } else {
                header("Location: buyer_dashboard.php"); // Create this file for buyers
            }
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "User not found!";
    }
}

$conn->close();
?>
