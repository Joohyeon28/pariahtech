<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$error_message = "";
$success_message = "";
$user_data = null;

// Get user ID from URL parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_view_users.php");
    exit();
}

$user_id = intval($_GET['id']);

// Prevent admin from editing their own account
if ($user_id == $_SESSION['user_id']) {
    $error_message = "You cannot edit your own account from this interface.";
}

// Fetch user data
if (empty($error_message)) {
    $stmt = $conn->prepare("SELECT id, name, surname, email, contact, user_type FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: admin_view_users.php");
        exit();
    }
    
    $user_data = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message)) {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $user_type = $_POST['user_type'] ?? 'customer';
    
    // Validation
    if (empty($name) || empty($surname) || empty($email) || empty($contact)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "This email is already registered to another user.";
        } else {
            // Update user data
            $stmt = $conn->prepare("UPDATE users SET name = ?, surname = ?, email = ?, contact = ?, user_type = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $name, $surname, $email, $contact, $user_type, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "User information updated successfully.";
                // Refresh user data
                $user_data['name'] = $name;
                $user_data['surname'] = $surname;
                $user_data['email'] = $email;
                $user_data['contact'] = $contact;
                $user_data['user_type'] = $user_type;
            } else {
                $error_message = "Error updating user: " . $conn->error;
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
    <title>Edit User - Admin Dashboard - PariahTech</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #003366;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid;
        }
        
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
        }
        
        .user-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #004080;
        }
    </style>
</head>
<body>
    <header>
        <h1>PariahTech - Admin Panel</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_view_users.php">View Users</a></li>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="admin-container">
            <h2>Edit User</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <strong>Success:</strong> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($user_data && empty($error_message)): ?>
                <div class="user-info">
                    <strong>Editing User ID:</strong> <?php echo $user_data['id']; ?>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">First Name:</label>
                        <input type="text" name="name" id="name" 
                               value="<?php echo htmlspecialchars($user_data['name']); ?>" 
                               required maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="surname">Surname:</label>
                        <input type="text" name="surname" id="surname" 
                               value="<?php echo htmlspecialchars($user_data['surname']); ?>" 
                               required maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" 
                               value="<?php echo htmlspecialchars($user_data['email']); ?>" 
                               required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="contact">Contact Number:</label>
                        <input type="text" name="contact" id="contact" 
                               value="<?php echo htmlspecialchars($user_data['contact']); ?>" 
                               required maxlength="20">
                    </div>

                    <div class="form-group">
                        <label for="user_type">User Type:</label>
                        <select name="user_type" id="user_type" required>
                            <option value="customer" <?php echo ($user_data['user_type'] === 'customer') ? 'selected' : ''; ?>>
                                Customer
                            </option>
                            <option value="admin" <?php echo ($user_data['user_type'] === 'admin') ? 'selected' : ''; ?>>
                                Admin
                            </option>
                        </select>
                    </div>

                    <div class="btn-group">
                        <input type="submit" value="Update User" class="btn">
                        <a href="admin_view_users.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <div class="btn-group">
                    <a href="admin_view_users.php" class="btn-secondary">Back to User List</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 PariahTech</p>
    </footer>
</body>
</html>