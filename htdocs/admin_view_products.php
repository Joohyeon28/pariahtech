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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #2c3e50;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 30px;
        }

        nav ul li a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        main {
            padding: 40px 0;
        }

        .admin-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            margin: 30px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        h1, h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-danger {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 14px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .products-table th, .products-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .products-table th {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
        }

        .products-table tr:hover {
            background: rgba(102, 126, 234, 0.1);
        }

        .product-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 10px;
            font-weight: 500;
        }

        .message.success {
            background: rgba(40, 167, 69, 0.2);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .message.error {
            background: rgba(220, 53, 69, 0.2);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .price-cell {
            font-weight: bold;
            color: #28a745;
        }

        .description-cell {
            max-width: 200px;
            word-break: break-word;
        }

        footer {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            color: white;
            text-align: center;
            padding: 30px 0;
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .products-table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <div class="logo-icon">PT</div>
                    <h1>PariahTech Admin</h1>
                </a>
                <nav>
                    <ul>
                        <li><a href="admin_dashboard.php">Dashboard</a></li>
                        <li><a href="admin_view_users.php">Manage Users</a></li>
                        <li><a href="admin_view_products.php" class="active">Manage Products</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="admin-container">
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
                <a href="admin_add_product.php" class="btn btn-primary">Add New Product</a>
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
                                        <a href="admin_edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-small">Edit</a>
                                        <a href="?delete_product=<?php echo $product['id']; ?>" 
                                           class="btn btn-danger btn-small" 
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
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 PariahTech. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>