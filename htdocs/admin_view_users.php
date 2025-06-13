<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle user deletion
if (isset($_GET['delete_user']) && is_numeric($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);
    
    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $message = "You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $message = "User deleted successfully.";
        } else {
            $message = "Error deleting user: " . $conn->error;
        }
    }
}

// Handle user type change
if (isset($_POST['change_type']) && isset($_POST['user_id']) && isset($_POST['new_type'])) {
    $user_id = intval($_POST['user_id']);
    $new_type = $_POST['new_type'];
    
    // Prevent admin from changing their own type
    if ($user_id == $_SESSION['user_id']) {
        $message = "You cannot change your own user type.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET user_type = ? WHERE id = ?");
        $stmt->bind_param("si", $new_type, $user_id);
        
        if ($stmt->execute()) {
            $message = "User type updated successfully.";
        } else {
            $message = "Error updating user type: " . $conn->error;
        }
    }
}

// Get all users
$sql = "SELECT id, name, surname, email, contact, user_type, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - PariahTech Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        .users-table th, .users-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .users-table th {
            background-color: #004080;
            color: white;
        }
        .users-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
            margin: 0;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .user-type-form {
            display: inline-block;
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
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #004080;
        }
    </style>
</head>
<body>
    <header>
        <h1>PariahTech Admin</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_view_users.php" class="active">Manage Users</a></li>
                <li><a href="admin_view_products.php">Manage Products</a></li>
                <li><a href="index.php">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>User Management</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php
        // Get user statistics
        $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        $total_customers = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'")->fetch_assoc()['count'];
        $total_admins = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin'")->fetch_assoc()['count'];
        ?>

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div>Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_customers; ?></div>
                <div>Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_admins; ?></div>
                <div>Admins</div>
            </div>
        </div>

        <div style="margin: 20px 0;">
            <a href="admin_add_user.php" class="btn">Add New User</a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>User Type</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['contact']); ?></td>
                            <td>
                                <form method="POST" class="user-type-form">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="new_type" onchange="this.form.submit()" 
                                            <?php echo ($user['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                        <option value="customer" <?php echo ($user['user_type'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                                        <option value="admin" <?php echo ($user['user_type'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <input type="hidden" name="change_type" value="1">
                                </form>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-small">Edit</a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete_user=<?php echo $user['id']; ?>" 
                                           class="btn btn-small btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this user? This will also delete all their products and purchases.')">
                                           Delete
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 PariahTech</p>
    </footer>
</body>
</html>