<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PariahTech - Your Tech Marketplace</title>
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

        .hero {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin: 30px 0;
            padding: 60px 40px;
            text-align: center;
            color: white;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .hero h2 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.4rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .feature-card p {
            color: #7f8c8d;
            line-height: 1.6;
        }

        .tech-showcase {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 50px 40px;
            margin: 50px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .tech-showcase h3 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 40px;
            color: #2c3e50;
        }

        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .tech-item {
            text-align: center;
            padding: 20px;
            border-radius: 15px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.5);
        }

        .tech-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.8);
        }

        .tech-image {
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }

        .tech-item h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .tech-item p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
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

            .hero h2 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .tech-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .tech-image {
                width: 100px;
                height: 100px;
                font-size: 36px;
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
                        <li><a href="index.php" class="active">Home</a></li>
                        <li><a href="buy.php">Buy</a></li>
                        <li><a href="sell.php">Sell</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
    <li><a href="logout.php">Logout</a></li>
<?php else: ?>
    <li><a href="login.php">Sign Up/Login</a></li>
<?php endif; ?>

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
            <section class="hero">
                <h2>Welcome to PariahTech</h2>
                <p>Your premium C2C marketplace for cutting-edge technology products</p>
                <div class="cta-buttons">
                    <a href="buy.php" class="btn btn-primary">Start Shopping</a>
                    <a href="sell.php" class="btn btn-secondary">Sell Your Tech</a>
                </div>
            </section>

            <section class="stats">
                <div class="stat-card">
                    <div class="stat-number">10K+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">50K+</div>
                    <div class="stat-label">Products Sold</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">99%</div>
                    <div class="stat-label">Satisfaction Rate</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Customer Support</div>
                </div>
            </section>

            <section class="features">
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Secure Transactions</h3>
                    <p>Advanced encryption and secure payment processing ensure your transactions are always protected. Shop with confidence knowing your data is safe.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Lightning Fast</h3>
                    <p>Quick listing process, instant messaging, and rapid checkout. Get your tech faster than ever with our optimized platform.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌟</div>
                    <h3>Quality Assured</h3>
                    <p>Every product is verified and quality-checked. Our rating system ensures you're buying from trusted sellers with excellent reputations.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>Direct Communication</h3>
                    <p>Chat directly with buyers and sellers through our built-in messaging system. Ask questions, negotiate prices, and build relationships.</p>
                </div>
            </section>

            <section class="tech-showcase">
                <h3>Shop by Category</h3>
                <div class="tech-grid">
                    <div class="tech-item">
                        <div class="tech-image">💻</div>
                        <h4>Laptops</h4>
                        <p>Gaming, business, and everyday laptops from top brands</p>
                    </div>
                    <div class="tech-item">
                        <div class="tech-image">📱</div>
                        <h4>Smartphones</h4>
                        <p>Latest iPhones, Samsung, and Android devices</p>
                    </div>
                    <div class="tech-item">
                        <div class="tech-image">🎮</div>
                        <h4>Gaming</h4>
                        <p>Consoles, games, and gaming accessories</p>
                    </div>
                    <div class="tech-item">
                        <div class="tech-image">📷</div>
                        <h4>Cameras</h4>
                        <p>DSLRs, mirrorless, and action cameras</p>
                    </div>
                    <div class="tech-item">
                        <div class="tech-image">🖥️</div>
                        <h4>Monitors</h4>
                        <p>4K, gaming, and professional displays</p>
                    </div>
                    <div class="tech-item">
                        <div class="tech-image">🎧</div>
                        <h4>Audio</h4>
                        <p>Headphones, speakers, and audio equipment</p>
                    </div>
                </div>
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