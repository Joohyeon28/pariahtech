<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'db_connect.php';

$error_message = "";
$success_message = "";

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $admin_id = trim($_POST['admin_id'] ?? '');
    
    if (empty($email) || empty($password) || empty($admin_id)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif ($admin_id !== '1234') {
        $error_message = "Invalid admin ID.";
    } else {
        try {
            // Check if user exists and is an admin
            $stmt = $conn->prepare("SELECT id, name, surname, email, password, user_type, is_verified FROM users WHERE email = ? AND user_type = 'admin'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                if (!$user['is_verified']) {
                    $error_message = "Admin account must be verified before logging in. Please contact system administrator.";
                } elseif (password_verify($password, $user['password'])) {
                    // Set session variables for admin
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $email;
                    
                    $success_message = "Admin login successful! Redirecting...";
                    header("refresh:2;url=admin_dashboard.php");
                } else {
                    $error_message = "Invalid email, password, or admin credentials.";
                }
            } else {
                $error_message = "Invalid email, password, or admin credentials.";
            }
        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PariahTech</title>
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
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .admin-login-container {
            max-width: 500px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-header .admin-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            color: white;
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }

        .admin-header h2 {
            font-size: 2.2rem;
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .admin-header p {
            color: #7f8c8d;
            font-size: 1rem;
            font-weight: 500;
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

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid rgba(231, 76, 60, 0.2);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
        }

        .form-group input:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 20px rgba(231, 76, 60, 0.2);
            transform: translateY(-2px);
        }

        .admin-id-field {
            background: rgba(231, 76, 60, 0.1);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        .admin-id-field .form-group {
            margin-bottom: 0;
        }

        .admin-id-field label {
            color: #c0392b;
            font-weight: 700;
        }

        .admin-id-field input {
            border-color: rgba(231, 76, 60, 0.4);
            background: rgba(255, 255, 255, 0.9);
        }

        .admin-id-field input:focus {
            border-color: #e74c3c;
            box-shadow: 0 0 20px rgba(231, 76, 60, 0.3);
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
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            box-shadow: 0 5px 20px rgba(231, 76, 60, 0.4);
            margin-top: 20px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(231, 76, 60, 0.6);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #7f8c8d;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #e74c3c;
        }

        .security-notice {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }

        .security-notice h4 {
            color: #c0392b;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .security-notice p {
            color: #7f8c8d;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .admin-login-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .admin-header h2 {
                font-size: 1.8rem;
            }

            .admin-header .admin-icon {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-login-container">
            <div class="admin-header">
                <div class="admin-icon">🔐</div>
                <h2>Admin Access</h2>
                <p>Authorized personnel only</p>
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
            
            <form action="admin_login.php" method="POST">
                <div class="form-group">
                    <label for="email">Admin Email Address</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="Enter your admin email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required placeholder="Enter your password">
                </div>

                <div class="admin-id-field">
                    <div class="form-group">
                        <label for="admin_id">Admin Verification ID</label>
                        <input type="text" name="admin_id" id="admin_id" value="<?php echo htmlspecialchars($_POST['admin_id'] ?? ''); ?>" required placeholder="Enter admin verification ID">
                    </div>
                </div>

                <button type="submit" class="btn">Access Admin Dashboard</button>
            </form>
            
            <div class="security-notice">
                <h4>🛡️ Security Notice</h4>
                <p>This area is restricted to authorized administrators only. All login attempts are logged and monitored for security purposes.</p>
            </div>
            
            <div class="back-link">
                <a href="login.php">← Back to Regular Login</a>
            </div>
        </div>
    </div>
</body>
</html>