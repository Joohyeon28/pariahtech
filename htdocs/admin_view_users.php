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
            max-width: 1400px;
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

        .admin-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
            box-shadow: 0 3px 10px rgba(255, 107, 107, 0.3);
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 30px;
            align-items: center;
        }

        nav ul li a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .page-header {
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

        .page-header h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .message {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-left: 5px solid;
        }

        .message.success {
            border-left-color: #28a745;
            color: #155724;
        }

        .message.error {
            border-left-color: #dc3545;
            color: #721c24;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px 20px;
            text-align: center;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            display: block;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .table-header h3 {
            color: #2c3e50;
            font-size: 1.8rem;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .users-table th {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .users-table td {
            padding: 15px 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            background: white;
        }

        .users-table tr:nth-child(even) td {
            background: rgba(102, 126, 234, 0.02);
        }

        .users-table tr:hover td {
            background: rgba(102, 126, 234, 0.05);
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin: 2px;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        }

        .btn-danger {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            box-shadow: 0 3px 10px rgba(255, 107, 107, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .user-type-form select {
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 10px;
            padding: 5px 8px;
            font-size: 12px;
            color: #2c3e50;
        }

        .user-type-form select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }

        .add-user-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }

        .add-user-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
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

            .users-table {
                font-size: 12px;
            }

            .users-table th,
            .users-table td {
                padding: 8px 6px;
            }

            .table-container {
                padding: 20px;
                overflow-x: auto;
            }

            .action-buttons {
                flex-direction: column;
                gap: 3px;
            }

            .page-header h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="admin_dashboard.php" class="logo">
                    <div class="logo-icon">PT</div>
                    <div>
                        <h1>PariahTech</h1>
                        <span class="admin-badge">ADMIN</span>
                    </div>
                </a>
                <nav>
                    <ul>
                        <li><a href="admin_dashboard.php">Dashboard</a></li>
                        <li><a href="admin_view_users.php" class="active">Manage Users</a></li>
                        <li><a href="admin_view_products.php">Manage Products</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="page-header">
                <h2>👥 User Management</h2>
                <p>Manage user accounts, permissions, and platform access</p>
            </section>
            
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

            <section class="stats-section">
                <div class="stat-card">
                    <span class="stat-icon">👥</span>
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">👤</span>
                    <div class="stat-number"><?php echo $total_customers; ?></div>
                    <div class="stat-label">Customers</div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">⚡</span>
                    <div class="stat-number"><?php echo $total_admins; ?></div>
                    <div class="stat-label">Admins</div>
                </div>
            </section>

            <div class="table-container">
                <div class="table-header">
                    <h3>All Users</h3>
                    <a href="admin_add_user.php" class="add-user-btn">+ Add New User</a>
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
                                            <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">Edit</a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="?delete_user=<?php echo $user['id']; ?>" 
                                                   class="btn btn-danger" 
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
                    <p style="text-align: center; color: #7f8c8d; font-size: 1.2rem; padding: 40px;">No users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 PariahTech Admin Panel. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>