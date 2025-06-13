<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - PariahTech</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Welcome, Admin</h2>
        <p>Here you can manage users, view products, delete posts, etc.</p>
        <!-- Example features -->
        <ul>
            <li><a href="admin_view_users.php">View Users</a></li>
            <li><a href="admin_view_products.php">View Products</a></li>
        </ul>
    </main>

    <footer>
        <p>&copy; 2025 PariahTech</p>
    </footer>
</body>
</html>
