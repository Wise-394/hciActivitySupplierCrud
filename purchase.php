<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");  // Redirect to login if not logged in
    exit();
}

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
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $buyer_id = $_SESSION['user_id']; // Get logged in buyer's ID

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO sales (product_id, buyer_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $product_id, $buyer_id, $quantity);

    if ($stmt->execute()) {
        echo "Purchase successful!";
        header("Location: buyer_dashboard.php"); // Redirect back to buyer dashboard
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

$conn->close();
?>
