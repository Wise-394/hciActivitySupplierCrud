<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hciactivity";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $result = $conn->query("SELECT * FROM products WHERE id = $id AND supplier_id = " . $_SESSION['user_id']);
    $product = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE products SET product_name = ?, price = ?, description = ? WHERE id = ?");
    $stmt->bind_param("sdsi", $product_name, $price, $description, $id);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error updating product: " . $stmt->error;
    }
}

$conn->close();
?>

<!-- HTML Form for editing -->
<form method="POST">
    <input type="text" name="product_name" value="<?php echo $product['product_name']; ?>" required>
    <input type="number" name="price" value="<?php echo $product['price']; ?>" required>
    <textarea name="description"><?php echo $product['description']; ?></textarea>
    <input type="submit" value="Update Product">
</form>
