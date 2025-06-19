<?php
session_start();
require_once "db_connect.php"; // Fixed path

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect'] = 'buy.php';
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: buy.php");
    exit();
}

$product_id = intval($_GET['id']);
$sql = "SELECT * FROM products WHERE id = ?"; // Using prepared statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: buy.php");
    exit();
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PariahTech - Checkout</title>
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
            padding: 30px 0;
        }

        .page-title {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .page-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 10px;
        }

        .page-title p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin-bottom: 50px;
        }

        .product-details {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .product-image {
            width: 100%;
            max-width: 400px;
            height: 300px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .product-details h3 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #2c3e50;
            font-weight: 700;
        }

        .product-info {
            display: grid;
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-label {
            font-weight: 600;
            color: #667eea;
            min-width: 100px;
            font-size: 1.1rem;
        }

        .info-value {
            color: #2c3e50;
            flex: 1;
            font-size: 1.1rem;
        }

        .price-highlight {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .condition-badge {
            display: inline-block;
            padding: 6px 16px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .checkout-sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            height: fit-content;
            position: sticky;
            top: 120px;
        }

        .order-summary {
            margin-bottom: 25px;
        }

        .order-summary h4 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #2c3e50;
            font-weight: 700;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-item:last-child {
            border-bottom: none;
            margin-top: 15px;
            padding-top: 20px;
            border-top: 2px solid #667eea;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .summary-label {
            color: #7f8c8d;
        }

        .summary-value {
            color: #2c3e50;
            font-weight: 600;
        }

        .security-info {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        .security-info h5 {
            color: #667eea;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
        }

        .security-info p {
            color: #7f8c8d;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.6);
        }

        .breadcrumb {
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb a:hover {
            color: white;
        }

        .breadcrumb span {
            color: rgba(255, 255, 255, 0.6);
            margin: 0 10px;
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

            .checkout-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .checkout-sidebar {
                position: static;
            }

            .page-title h2 {
                font-size: 2rem;
            }

            .product-details h3 {
                font-size: 1.5rem;
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
                        <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>)</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="page-title">
                <div class="breadcrumb">
                    <a href="index.php">Home</a>
                    <span>></span>
                    <a href="buy.php">Buy</a>
                    <span>></span>
                    <span>Checkout</span>
                </div>
                <h2>Secure Checkout</h2>
                <p>Complete your purchase safely and securely</p>
            </div>

            <div class="checkout-container">
                <div class="product-details">
                    <img src="images/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image" />
                    
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    
                    <div class="product-info">
                        <div class="info-item">
                            <span class="info-label">Price:</span>
                            <span class="info-value price-highlight">R<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Condition:</span>
                            <span class="info-value">
                                <span class="condition-badge"><?php echo htmlspecialchars($product['condition']); ?></span>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Description:</span>
                            <span class="info-value"><?php echo nl2br(htmlspecialchars($product['description'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="checkout-sidebar">
                    <div class="order-summary">
                        <h4>Order Summary</h4>
                        <div class="summary-item">
                            <span class="summary-label">Item Price:</span>
                            <span class="summary-value">R<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Processing Fee:</span>
                            <span class="summary-value">R0.00</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Total:</span>
                            <span class="summary-value price-highlight">R<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                    </div>

                    <div class="security-info">
                        <h5>🔒 Secure Payment</h5>
                        <p>Your payment information is encrypted and protected. We use industry-standard security measures to keep your data safe.</p>
                    </div>

                    <form action="payment.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>" />
                        <input type="submit" value="Proceed to Payment" class="btn" />
                    </form>
                </div>
            </div>
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