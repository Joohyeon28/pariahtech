<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $user_type = $_POST['user_type'] ?? 'customer';
    
    if (empty($name) || empty($surname) || empty($email) || empty($contact)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "A user with this email already exists.";
        } else {

            $stmt = $conn->prepare("INSERT INTO users (name, surname, email, contact, user_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $surname, $email, $contact, $user_type);
            
            if ($stmt->execute()) {
                $message = "User created successfully!";

                $name = $surname = $email = $contact = '';
                $user_type = 'customer';
            } else {
                $error = "Error creating user: " . $conn->error;
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
    <title>Add User - PariahTech Admin</title>
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
            margin: 30px auto;
            max-width: 800px;
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

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
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

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 20px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #667eea;
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
            .header-content {
                flex-direction: column;
                gap: 20px;
            }

            nav ul {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }

            .admin-container {
                padding: 25px;
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
                        <li><a href="admin_view_products.php">Manage Products</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <a href="admin_view_users.php" class="back-link">&larr; Back to User Management</a>
        
        <div class="admin-container">
            <h2>Add New User</h2>
            
            <?php if (!empty($message)): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">First Name:</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="surname">Surname:</label>
                    <input type="text" name="surname" id="surname" value="<?php echo htmlspecialchars($surname ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="contact">Contact Number:</label>
                    <input type="text" name="contact" id="contact" value="<?php echo htmlspecialchars($contact ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="user_type">User Type:</label>
                    <select name="user_type" id="user_type" required>
                        <option value="customer" <?php echo (($user_type ?? 'customer') === 'customer') ? 'selected' : ''; ?>>Customer</option>
                        <option value="admin" <?php echo (($user_type ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <input type="submit" value="Create User" class="btn btn-primary">
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 PariahTech. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>