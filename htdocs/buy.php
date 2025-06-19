<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect'] = 'buy.php';
    header("Location: login.php");
    exit();
}

$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PariahTech - Buy Tech Products</title>
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

        .page-hero {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin: 30px 0;
            padding: 40px;
            text-align: center;
            color: white;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .page-hero h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .page-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .products-section {
            margin: 30px 0;
        }

        .section-title {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .section-title h3 {
            color: #2c3e50;
            font-size: 1.8rem;
            text-align: center;
            margin: 0;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .product-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #6c757d;
        }

        .product-info {
            padding: 25px;
        }

        .product-info h4 {
            font-size: 1.3rem;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .product-details {
            margin-bottom: 20px;
        }

        .product-details p {
            margin-bottom: 8px;
            color: #5a6c7d;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-details strong {
            color: #2c3e50;
            font-weight: 600;
        }

        .price {
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .condition-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .condition-new {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .condition-excellent {
            background: rgba(23, 162, 184, 0.2);
            color: #17a2b8;
        }

        .condition-good {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .condition-fair {
            background: rgba(255, 133, 27, 0.2);
            color: #fd851b;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
            border: none;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.6);
        }

        .no-products {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            color: #2c3e50;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .no-products h3 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #6c757d;
        }

        .no-products p {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .user-welcome {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 15px;
            color: white;
            text-align: right;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-welcome span {
            font-weight: 600;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        footer {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            color: white;
            text-align: center;
            padding: 30px 0;
            margin-top: 50px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-section h4 {
            margin-bottom: 15px;
            color: #667eea;
        }

        .footer-section p,
        .footer-section ul {
            opacity: 0.8;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 8px;
        }

        .footer-section ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: #667eea;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
            }

            nav ul {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }

            .page-hero h2 {
                font-size: 2rem;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .user-welcome {
                text-align: center;
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
                    <h1>PariahTech</h1>
                </a>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="buy.php" class="active">Buy</a></li>
                        <li><a href="sell.php">Sell</a></li>
                        <li><a href="logout.php">Logout</a></li>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin'): ?>
    <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
<?php else: ?>
    <li><a href="contact.php">Contact Us</a></li>
<?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if (isset($_SESSION['user_name'])): ?>
                <div class="user-welcome">
                    Welcome back, <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>!
                </div>
            <?php endif; ?>

            <section class="page-hero">
                <h2>Shop Premium Tech</h2>
                <p>Discover amazing deals on verified technology products from trusted sellers</p>
            </section>

            <section class="products-section">
                <div class="section-title">
                    <h3>Available Products</h3>
                </div>

                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="products-grid">
                        <?php while($product = $result->fetch_assoc()): ?>
                            <div class="product-card">
                                <?php if (!empty($product['image']) && file_exists("images/" . $product['image'])): ?>
                                    <img src="images/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-image">
                                <?php else: ?>
                                    <div class="product-image">📱</div>
                                <?php endif; ?>
                                
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                    
                                    <div class="product-details">
                                        <p>
                                            <strong>Condition:</strong>
                                            <span class="condition-badge condition-<?php echo strtolower(str_replace(' ', '-', $product['condition'])); ?>">
                                                <?php echo htmlspecialchars($product['condition']); ?>
                                            </span>
                                        </p>
                                        <p>
                                            <strong>Price:</strong>
                                            <span class="price">R<?php echo number_format($product['price'], 2); ?></span>
                                        </p>
                                        <?php if (!empty($product['description'])): ?>
                                            <p style="margin-top: 15px; color: #7f8c8d; font-style: italic;">
                                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : ''); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <a href="checkout.php?id=<?php echo $product['id']; ?>" class="btn">Buy Now</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-products">
                        <h3>🛍️ No Products Available</h3>
                        <p>There are currently no products listed for sale. Check back soon or be the first to list your tech!</p>
                        <a href="sell.php" class="btn" style="width: auto; display: inline-block;">Start Selling</a>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>About PariahTech</h4>
                    <p>We're the leading C2C marketplace for technology enthusiasts. Founded in 2025, we've helped thousands of people buy and sell their tech safely and efficiently.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#">How to Buy</a></li>
                        <li><a href="#">How to Sell</a></li>
                        <li><a href="#">Safety Tips</a></li>
                        <li><a href="#">Payment Methods</a></li>
                        <li><a href="#">Shipping Info</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Support</a></li>
                        <li><a href="#">Report an Issue</a></li>
                        <li><a href="#">Community Guidelines</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Connect With Us</h4>
                    <ul>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Instagram</a></li>
                        <li><a href="#">LinkedIn</a></li>
                        <li><a href="#">Newsletter</a></li>
                    </ul>
                </div>
            </div>
            <p>&copy; 2025 PariahTech. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>