<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle product deletion
if (isset($_GET['delete_product']) && is_numeric($_GET['delete_product'])) {
    $product_id = intval($_GET['delete_product']);
    
    // Get product image to delete it
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
        // Delete the image file
        if (!empty($product['image']) && file_exists('images/' . $product['image'])) {
            unlink('images/' . $product['image']);
        }
        
        // Delete the product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $message = "Product deleted successfully.";
        } else {
            $message = "Error deleting product: " . $conn->error;
        }
    } else {
        $message = "Product not found.";
    }
}

// Get all products with seller information
$sql = "SELECT p.*, u.name as seller_name, u.surname as seller_surname, u.email as seller_email 
        FROM products p 
        LEFT JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - PariahTech Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        .products-table th, .products-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .products-table th {
            background-color: #004080;
            color: white;
        }
        .products-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .product-image {
            max-width: 80px;
            max-height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
            margin: 0;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #004080;
        }
        .description-cell {
            max-width: 200px;
            word-break: break-word;
        }
        .price-cell {
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>
<body>
    <header>
        <h1>PariahTech Admin</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_view_users.php">Manage Users</a></li>
                <li><a href="admin_view_products.php" class="active">Manage Products</a></li>
                <li><a href="index.php">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Product Management</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php
        // Get product statistics
        $total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
        $total_purchases = $conn->query("SELECT COUNT(*) as count FROM purchases")->fetch_assoc()['count'];
        $avg_price = $conn->query("SELECT AVG(price) as avg_price FROM products")->fetch_assoc()['avg_price'];
        ?>

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_products; ?></div>
                <div>Total Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_purchases; ?></div>
                <div>Total Sales</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">R<?php echo number_format($avg_price ?? 0, 2); ?></div>
                <div>Average Price</div>
            </div>
        </div>

        <div style="margin: 20px 0;">
            <a href="admin_add_product.php" class="btn">Add New Product</a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Condition</th>
                        <th>Description</th>
                        <th>Seller</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($product = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img src="images/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="Product Image" class="product-image">
                                <?php else: ?>
                                    <em>No image</em>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="price-cell">R<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($product['condition']); ?></td>
                            <td class="description-cell">
                                <?php 
                                $desc = htmlspecialchars($product['description']);
                                echo (strlen($desc) > 100) ? substr($desc, 0, 100) . '...' : $desc;
                                ?>
                            </td>
                            <td>
                                <?php if ($product['seller_name']): ?>
                                    <?php echo htmlspecialchars($product['seller_name'] . ' ' . $product['seller_surname']); ?><br>
                                    <small><?php echo htmlspecialchars($product['seller_email']); ?></small>
                                <?php else: ?>
                                    <em>User deleted</em>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($product['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="admin_edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-small">Edit</a>
                                    <a href="?delete_product=<?php echo $product['id']; ?>" 
                                       class="btn btn-small btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this product?')">
                                       Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 PariahTech</p>
    </footer>
</body>
</html>