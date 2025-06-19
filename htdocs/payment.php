<?php
session_start();
require_once "includes/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = false;
$error_message = "";
$product_name = "";
$product_price = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $buyer_id = $_SESSION['user_id'];

    // Get product details first
    $product_sql = "SELECT name, price FROM products WHERE id = ?";
    $stmt = $conn->prepare($product_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    
    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
        $product_name = $product['name'];
        $product_price = $product['price'];
    }

    // Insert purchase record using prepared statement
    $sql = "INSERT INTO purchases (product_id, buyer_id, purchase_date) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $product_id, $buyer_id);
    
    if ($stmt->execute()) {
        $success = true;
    } else {
        $error_message = "Error processing payment. Please try again.";
    }
} else {
    header("Location: buy.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PariahTech - Payment <?php echo $success ? 'Successful' : 'Failed'; ?></title>
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
            padding: 50px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 200px);
        }

        .payment-status {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 60px 50px;
            text-align: center;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 600px;
            width: 100%;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .status-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
            position: relative;
            animation: bounce 0.8s ease-out 0.3s;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .status-icon.success {
            background: linear-gradient(45deg, #00b894, #00a085);
            box-shadow: 0 8px 30px rgba(0, 184, 148, 0.4);
        }

        .status-icon.error {
            background: linear-gradient(45deg, #e17055, #d63031);
            box-shadow: 0 8px 30px rgba(225, 112, 85, 0.4);
        }

        .status-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .status-title.success {
            color: #00b894;
        }

        .status-title.error {
            color: #e17055;
        }

        .status-message {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .purchase-details {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        .purchase-details h4 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #7f8c8d;
            font-weight: 600;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 700;
        }

        .detail-value.price {
            font-size: 1.2rem;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            min-width: 150px;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.8);
            color: #2c3e50;
            border: 2px solid rgba(102, 126, 234, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .breadcrumb {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
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
            margin: 0 8px;
        }

        .next-steps {
            background: rgba(0, 184, 148, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid rgba(0, 184, 148, 0.2);
            text-align: left;
        }

        .next-steps h5 {
            color: #00b894;
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .next-steps ul {
            list-style: none;
            padding: 0;
        }

        .next-steps li {
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
            color: #7f8c8d;
            font-size: 0.95rem;
        }

        .next-steps li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #00b894;
            font-weight: bold;
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

            .payment-status {
                padding: 40px 30px;
                margin: 20px;
            }

            .status-title {
                font-size: 2rem;
            }

            .status-icon {
                width: 100px;
                height: 100px;
                font-size: 50px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 250px;
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
            <div class="payment-status">
                <div class="breadcrumb">
                    <a href="index.php">Home</a>
                    <span>></span>
                    <a href="buy.php">Buy</a>
                    <span>></span>
                    <span>Payment Status</span>
                </div>

                <?php if ($success): ?>
                    <div class="status-icon success">✓</div>
                    <h2 class="status-title success">Payment Successful!</h2>
                    <p class="status-message">Thank you for your purchase. Your order has been processed successfully.</p>
                    
                    <?php if ($product_name): ?>
                    <div class="purchase-details">
                        <h4>Purchase Details</h4>
                        <div class="detail-item">
                            <span class="detail-label">Product:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($product_name); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Amount Paid:</span>
                            <span class="detail-value price">R<?php echo number_format($product_price, 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Order Date:</span>
                            <span class="detail-value"><?php echo date('M j, Y - g:i A'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">#PT<?php echo str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="next-steps">
                        <h5>📋 What happens next?</h5>
                        <ul>
                            <li>You'll receive an email confirmation shortly</li>
                            <li>The seller will be notified of your purchase</li>
                            <li>You can track your order in your account dashboard</li>
                            <li>Contact details will be shared for pickup/delivery coordination</li>
                        </ul>
                    </div>

                    <div class="action-buttons">
                        <a href="buy.php" class="btn btn-primary">Continue Shopping</a>
                        <a href="index.php" class="btn btn-secondary">Back to Home</a>
                    </div>

                <?php else: ?>
                    <div class="status-icon error">✕</div>
                    <h2 class="status-title error">Payment Failed</h2>
                    <p class="status-message">
                        <?php echo $error_message ? htmlspecialchars($error_message) : "We're sorry, but there was an issue processing your payment. Please try again."; ?>
                    </p>

                    <div class="action-buttons">
                        <a href="buy.php" class="btn btn-primary">Try Again</a>
                        <a href="contact.html" class="btn btn-secondary">Contact Support</a>
                    </div>
                <?php endif; ?>
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