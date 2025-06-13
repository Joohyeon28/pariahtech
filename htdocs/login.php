<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'db_connect.php';

$error_message = "";
$success_message = "";

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'seller') {
        header("Location: seller_dashboard.php");
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
}

// Simple email verification function 
function sendVerificationEmail($email, $verification_code) {
    
    $subject = "PariahTech - Email Verification";
    $message = "Your verification code is: " . $verification_code;
    $headers = "From: noreply@pariahtech.com";
    
    return mail($email, $subject, $message, $headers);
}

function generateVerificationCode() {
    return sprintf("%06d", mt_rand(1, 999999));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? 'register';
    
    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $user_type = $_POST['user_type'] ?? 'buyer';
        
        // Validation
        if (empty($name) || empty($surname) || empty($email) || empty($contact) || empty($password) || empty($confirm_password)) {
            $error_message = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $password)) {
            $error_message = "Password must be at least 6 characters long and contain both letters and numbers.";
        } else {
            try {
                // Check if email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error_message = "An account with this email already exists.";
                } else {
                    // Generate verification code and hash password
                    $verification_code = generateVerificationCode();
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user (unverified)
                    $stmt = $conn->prepare("INSERT INTO users (name, surname, email, contact, password, user_type, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
                    $stmt->bind_param("sssssss", $name, $surname, $email, $contact, $hashed_password, $user_type, $verification_code);
                    
                    if ($stmt->execute()) {
                        $_SESSION['pending_verification_email'] = $email;
                        
                        // Send verification email
                        if (sendVerificationEmail($email, $verification_code)) {
                            $success_message = "Account created! Please check your email for the verification code.";
                            $_SESSION['show_verification'] = true;
                        } else {
                            $success_message = "Account created! Your verification code is: " . $verification_code . " (Email service unavailable)";
                            $_SESSION['show_verification'] = true;
                        }
                    } else {
                        $error_message = "Error creating account: " . $conn->error;
                    }
                }
            } catch (Exception $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'verify') {
        $verification_code = trim($_POST['verification_code'] ?? '');
        $email = $_SESSION['pending_verification_email'] ?? '';
        
        if (empty($verification_code)) {
            $error_message = "Please enter the verification code.";
        } elseif (empty($email)) {
            $error_message = "Verification session expired. Please register again.";
        } else {
            try {
                $stmt = $conn->prepare("SELECT id, name, user_type FROM users WHERE email = ? AND verification_code = ? AND is_verified = 0");
                $stmt->bind_param("ss", $email, $verification_code);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($user = $result->fetch_assoc()) {
                    // Verify the user
                    $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?");
                    $stmt->bind_param("i", $user['id']);
                    
                    if ($stmt->execute()) {
                        // Log the user in
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_type'] = $user['user_type'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $email;
                        
                        unset($_SESSION['pending_verification_email']);
                        unset($_SESSION['show_verification']);
                        
                        $success_message = "Email verified successfully! Welcome to PariahTech.";
                        
                        // Redirect after a delay
                        if ($user['user_type'] === 'seller') {
                            header("refresh:2;url=seller_dashboard.php");
                        } else {
                            $redirect_page = $_SESSION['redirect'] ?? 'index.php';
                            unset($_SESSION['redirect']);
                            header("refresh:2;url=$redirect_page");
                        }
                    } else {
                        $error_message = "Error verifying account.";
                    }
                } else {
                    $error_message = "Invalid verification code.";
                }
            } catch (Exception $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'login') {
        $email = trim($_POST['login_email'] ?? '');
        $password = $_POST['login_password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error_message = "Email and password are required.";
        } else {
            try {
                $stmt = $conn->prepare("SELECT id, name, surname, password, user_type, is_verified FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($user = $result->fetch_assoc()) {
                    if (!$user['is_verified']) {
                        $error_message = "Please verify your email address before logging in.";
                    } elseif (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_type'] = $user['user_type'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $email;
                        
                        if ($user['user_type'] === 'seller') {
                            header("Location: seller_dashboard.php");
                            exit();
                        } else {
                            $redirect_page = $_SESSION['redirect'] ?? 'index.php';
                            unset($_SESSION['redirect']);
                            header("Location: $redirect_page");
                            exit();
                        }
                    } else {
                        $error_message = "Invalid email or password.";
                    }
                } else {
                    $error_message = "Invalid email or password.";
                }
            } catch (Exception $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Signup - PariahTech</title>
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

        .auth-container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h2 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .auth-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .auth-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
            padding: 5px;
        }

        .auth-tab {
            flex: 1;
            padding: 12px 20px;
            text-align: center;
            background: transparent;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #667eea;
            transition: all 0.3s ease;
        }

        .auth-tab.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        }

        .auth-form {
            display: none;
        }

        .auth-form.active {
            display: block;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .alert-error {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .alert-success {
            background: linear-gradient(45deg, #00b894, #00a085);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 184, 148, 0.3);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .password-requirement {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-top: 5px;
            font-style: italic;
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
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.6);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .verification-container {
            text-align: center;
            padding: 30px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            margin-top: 20px;
        }

        .verification-code-input {
            font-size: 24px;
            text-align: center;
            letter-spacing: 10px;
            font-weight: bold;
            margin: 20px 0;
        }

        .admin-login-link {
            text-align: center;
            margin-bottom: 20px;
        }

        .admin-btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
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

            .auth-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .auth-header h2 {
                font-size: 2rem;
            }

            .container {
                padding: 0 10px;
            }
        }
    </style>
    <script>
        function showTab(tabName) {
            // Hide all forms
            document.querySelectorAll('.auth-form').forEach(form => {
                form.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.auth-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected form and activate tab
            document.getElementById(tabName + 'Form').classList.add('active');
            document.getElementById(tabName + 'Tab').classList.add('active');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['show_verification']) && $_SESSION['show_verification']): ?>
                showTab('verify');
            <?php else: ?>
                showTab('register');
            <?php endif; ?>
        });

        function validatePassword() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const requirement = document.querySelector('.password-requirement');
            
            const isValid = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/.test(password);
            
            if (password.length > 0) {
                if (isValid) {
                    requirement.style.color = '#00b894';
                    requirement.textContent = '✓ Password meets requirements';
                } else {
                    requirement.style.color = '#ff6b6b';
                    requirement.textContent = '✗ Password must be at least 6 characters with letters and numbers';
                }
            } else {
                requirement.style.color = '#7f8c8d';
                requirement.textContent = 'Password must contain both letters and numbers (minimum 6 characters)';
            }
            
            // Check confirm password
            const confirmRequirement = document.querySelector('.confirm-password-requirement');
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    confirmRequirement.style.color = '#00b894';
                    confirmRequirement.textContent = '✓ Passwords match';
                } else {
                    confirmRequirement.style.color = '#ff6b6b';
                    confirmRequirement.textContent = '✗ Passwords do not match';
                }
            } else {
                confirmRequirement.textContent = '';
            }
        }
    </script>
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
                        <li><a href="login.php" class="active">Sign Up / Log In</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h2>Welcome to PariahTech</h2>
                    <p>Join our community of tech enthusiasts</p>
                </div>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <strong>✅ Success:</strong> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="auth-tabs">
                    <button class="auth-tab" id="registerTab" onclick="showTab('register')">Sign Up</button>
                    <button class="auth-tab" id="loginTab" onclick="showTab('login')">Sign In</button>
                    <?php if (isset($_SESSION['show_verification']) && $_SESSION['show_verification']): ?>
                        <button class="auth-tab" id="verifyTab" onclick="showTab('verify')">Verify Email</button>
                    <?php endif; ?>
                </div>

                <div class="admin-login-link">
                    <a href="admin_login.php" class="admin-btn">Admin Login</a>
                </div>

                <!-- Registration Form -->
                <form class="auth-form" id="registerForm" action="login.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <label for="name">First Name</label>
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required placeholder="Enter your first name">
                    </div>

                    <div class="form-group">
                        <label for="surname">Surname</label>
                        <input type="text" name="surname" id="surname" value="<?php echo htmlspecialchars($_POST['surname'] ?? ''); ?>" required placeholder="Enter your surname">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="Enter your email address">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required placeholder="Create a password" oninput="validatePassword()">
                        <div class="password-requirement">Password must contain both letters and numbers (minimum 6 characters)</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirm your password" oninput="validatePassword()">
                        <div class="confirm-password-requirement"></div>
                    </div>

                    <div class="form-group">
                        <label for="contact">Contact Number</label>
                        <input type="text" name="contact" id="contact" value="<?php echo htmlspecialchars($_POST['contact'] ?? ''); ?>" required placeholder="Enter your contact number">
                    </div>

                    <button type="submit" class="btn">Create Account</button>
                </form>

                <!-- Login Form -->
                <form class="auth-form" id="loginForm" action="login.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="login_email">Email Address</label>
                        <input type="email" name="login_email" id="login_email" required placeholder="Enter your email address">
                    </div>

                    <div class="form-group">
                        <label for="login_password">Password</label>
                        <input type="password" name="login_password" id="login_password" required placeholder="Enter your password">
                    </div>

                    <button type="submit" class="btn">Sign In</button>
                </form>

                <!-- Email Verification Form -->
                <?php if (isset($_SESSION['show_verification']) && $_SESSION['show_verification']): ?>
                <form class="auth-form" id="verifyForm" action="login.php" method="POST">
                    <input type="hidden" name="action" value="verify">
                    
                    <div class="verification-container">
                        <h3>Email Verification</h3>
                        <p>We've sent a 6-digit verification code to your email address.</p>
                        
                        <div class="form-group">
                            <label for="verification_code">Verification Code</label>
                            <input type="text" name="verification_code" id="verification_code" class="verification-code-input" maxlength="6" required placeholder="000000">
                        </div>

                        <button type="submit" class="btn">Verify Email</button>
                        
                        <p style="margin-top: 15px; font-size: 0.9rem; color: #7f8c8d;">
                            Didn't receive the code? Check your spam folder or contact support.
                        </p>
                    </div>
                </form>
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