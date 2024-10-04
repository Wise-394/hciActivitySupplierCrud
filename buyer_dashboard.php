<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");  // Redirect to login if not logged in
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();  // Destroy the session
    header("Location: login.html");  // Redirect to login page
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hciactivity";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for adding products (only for suppliers)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $supplier_id = $_SESSION['user_id'];  // Use the logged-in user's ID as the supplier_id

    $stmt = $conn->prepare("INSERT INTO products (product_name, price, description, supplier_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdsi", $product_name, $price, $description, $supplier_id);
    
    if ($stmt->execute()) {
        echo "Product added successfully!";
    } else {
        echo "Error adding product: " . $stmt->error;
    }
}

// Fetch products from the database for the logged-in supplier
$supplier_id = $_SESSION['user_id'];
$products = $conn->query("SELECT * FROM products WHERE supplier_id = $supplier_id");

// Fetch all products for buyers
$all_products = $conn->query("SELECT * FROM products");

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .form-container, .products-container, .buyer-products-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 20px auto;
        }
        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .product-item {
            border-bottom: 1px solid #ccc;
            padding: 10px 0;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .buyer-product-item {
            margin: 10px 0;
        }
        .logout {
            text-align: right;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>

<!-- Logout Link -->
<div class="logout">
    <a href="?logout=true">Logout</a>
</div>

<!-- Conditional rendering based on user type -->
<?php if ($_SESSION['user_type'] == 'supplier'): ?>
    <h2>Add a Product</h2>
    <div class="form-container">
        <form action="dashboard.php" method="POST">
            <input type="text" name="product_name" placeholder="Product Name" required>
            <input type="number" name="price" placeholder="Price" required>
            <textarea name="description" placeholder="Product Description"></textarea>
            <input type="submit" name="add_product" value="Add Product">
        </form>
    </div>

    <h2>Your Products</h2>
    <div class="products-container">
        <?php while ($row = $products->fetch_assoc()): ?>
        <div class="product-item">
            <h3><?php echo $row['product_name']; ?></h3>
            <p>Price: $<?php echo $row['price']; ?></p>
            <p><?php echo $row['description']; ?></p>
            <div class="actions">
                <a href="edit_product.php?id=<?php echo $row['id']; ?>">Edit</a>
                <a href="delete_product.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

<?php elseif ($_SESSION['user_type'] == 'buyer'): ?>
    <h2>Available Products</h2>
    <div class="buyer-products-container">
        <?php while ($row = $all_products->fetch_assoc()): ?>
        <div class="buyer-product-item">
            <h3><?php echo $row['product_name']; ?></h3>
            <p>Price: $<?php echo $row['price']; ?></p>
            <p><?php echo $row['description']; ?></p>
            <form action="purchase.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" min="1" required>
                <input type="submit" value="Buy">
            </form>
        </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

</body>
</html>
