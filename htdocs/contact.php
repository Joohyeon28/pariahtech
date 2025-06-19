<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && ($_SESSION['user_type'] ?? '') === 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PariahTech - Contact Us</title>
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
            font-size: 3rem;
            margin-bottom: 15px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .page-hero p {
            font-size: 1.3rem;
            opacity: 0.9;
        }

        .contact-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin: 50px 0;
        }

        .contact-info {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .contact-info h3 {
            font-size: 2rem;
            margin-bottom: 30px;
            color: #2c3e50;
            text-align: center;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.8);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            flex-shrink: 0;
        }

        .contact-details h4 {
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 1.2rem;
        }

        .contact-details p {
            color: #7f8c8d;
            font-size: 1rem;
        }

        .contact-details a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .contact-details a:hover {
            color: #764ba2;
        }

        .contact-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .contact-form h3 {
            font-size: 2rem;
            margin-bottom: 30px;
            color: #2c3e50;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 12px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.2);
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 15px 30px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        .map-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            margin: 50px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .map-section h3 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .map-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 600;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
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
                font-size: 2.5rem;
            }

            .page-hero p {
                font-size: 1.2rem;
            }

            .contact-section {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .contact-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .contact-info h3,
            .contact-form h3 {
                font-size: 1.8rem;
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
                        <li><a href="buy.php">Buy</a></li>
                        <li><a href="sell.php">Sell</a></li>
                        <?php if ($isLoggedIn): ?>
    <li><a href="logout.php">Logout</a></li>
<?php else: ?>
    <li><a href="login.php">Sign Up / Log In</a></li>
<?php endif; ?>

                        <li><a href="contact.php" class="active">Contact Us</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="page-hero">
                <h2>Contact Us</h2>
                <p>Get in touch with the PariahTech team - we're here to help!</p>
            </section>

            <section class="contact-section">
                <div class="contact-info">
                    <h3>Get In Touch</h3>
                    
                    <div class="contact-item">
                        <div class="contact-icon">👤</div>
                        <div class="contact-details">
                            <h4>Creator</h4>
                            <p>Joohyeon Stemmer</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">📧</div>
                        <div class="contact-details">
                            <h4>Email Address</h4>
                            <p><a href="mailto:EDUV4845884@vossie.net">EDUV4845884@vossie.net</a></p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">📱</div>
                        <div class="contact-details">
                            <h4>Phone Number</h4>
                            <p><a href="tel:0630841172">063 084 1172</a></p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">🏢</div>
                        <div class="contact-details">
                            <h4>Headquarters</h4>
                            <p>Eduvos Midrand<br>Johannesburg, South Africa</p>
                        </div>
                    </div>
                </div>

                <div class="contact-form">
                    <h3>Send us a Message</h3>
                    <form>
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" placeholder="Tell us how we can help you..." required></textarea>
                        </div>

                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </section>

            <section class="map-section">
                <h3>Find Us</h3>
                <div class="map-placeholder">
                    📍 Eduvos Midrand Campus Map
                </div>
            </section>
                    <?php if ($isAdmin): ?>
<div style="text-align: center; margin: 30px 0;">
    <a href="admin_dashboard.php" class="admin-btn" style="display: inline-block; padding: 15px 30px; background: linear-gradient(45deg, #e74c3c, #c0392b); color: white; text-decoration: none; border-radius: 50px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);">
        Access Admin Dashboard
    </a>
</div>
<?php endif; ?>
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