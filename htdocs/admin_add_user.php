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
    
    // Validation
    if (empty($name) || empty($surname) || empty($email) || empty($contact)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "A user with this email already exists.";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name, surname, email, contact, user_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $surname, $email, $contact, $user_type);
            
            if ($stmt->execute()) {
                $message = "User created successfully!";
                // Clear form fields
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
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        .back-link {
            display: inline-block;
            margin: 20px 0;
            color: #004080;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
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
                <li><a href="admin_view_products.php">Manage Products</a></li>
                <li><a href="index.php">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <a href="admin_view_users.php" class="back-link">&larr; Back to User Management</a>
        
        <div class="form-container">
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
                <label for="name">First Name:</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>

                <label for="surname">Surname:</label>
                <input type="text" name="surname" id="surname" value="<?php echo htmlspecialchars($surname ?? ''); ?>" required>

                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>

                <label for="contact">Contact Number:</label>
                <input type="text" name="contact" id="contact" value="<?php echo htmlspecialchars($contact ?? ''); ?>" required>

                <label for="user_type">User Type:</label>
                <select name="user_type" id="user_type" required>
                    <option value="customer" <?php echo (($user_type ?? 'customer') === 'customer') ? 'selected' : ''; ?>>Customer</option>
                    <option value="admin" <?php echo (($user_type ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>

                <input type="submit" value="Create User" class="btn">
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 PariahTech</p>
    </footer>
</body>
</html>