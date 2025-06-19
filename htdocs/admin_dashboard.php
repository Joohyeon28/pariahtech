<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - PariahTech</title>
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
            position: relative;
        }

        nav ul li a:hover {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .welcome-section {
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

        .welcome-section h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .welcome-section p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .admin-stats {
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
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .stat-number {
            font-size: 2rem;
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

        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }

        .action-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }

        .action-card h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: #2c3e50;
            text-align: center;
        }

        .action-card p {
            color: #7f8c8d;
            margin-bottom: 20px;
            text-align: center;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-size: 14px;
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
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .recent-activity {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .recent-activity h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.8rem;
            text-align: center;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .activity-time {
            font-size: 0.9rem;
            color: #7f8c8d;
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

            .logo h1 {
                font-size: 24px;
            }

            .welcome-section h2 {
                font-size: 2rem;
            }

            .admin-stats {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .admin-actions {
                grid-template-columns: 1fr;
            }

            .activity-item {
                flex-direction: column;
                text-align: center;
                gap: 10px;
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
                    <div>
                        <h1>PariahTech</h1>
                        <span class="admin-badge">ADMIN</span>
                    </div>
                </a>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="welcome-section">
                <h2>👑 Admin Dashboard</h2>
                <p>Welcome back, Administrator! Manage your marketplace with powerful tools and insights.</p>
            </section>

            <section class="admin-stats">
                <div class="stat-card">
                    <span class="stat-icon">👥</span>
                    <div class="stat-number">1,247</div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">📦</span>
                    <div class="stat-number">3,892</div>
                    <div class="stat-label">Active Products</div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">💰</span>
                    <div class="stat-number">$125K</div>
                    <div class="stat-label">Monthly Revenue</div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">⚠️</span>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Pending Reports</div>
                </div>
            </section>

            <section class="admin-actions">
                <div class="action-card">
                    <div class="action-icon">👥</div>
                    <h3>User Management</h3>
                    <p>View, edit, suspend, or manage user accounts and permissions across the platform.</p>
                    <div class="action-buttons">
                        <a href="admin_view_users.php" class="btn btn-primary">View All Users</a>
                        <a href="admin_user_reports.php" class="btn btn-outline">User Reports</a>
                    </div>
                </div>

                <div class="action-card">
                    <div class="action-icon">🛍️</div>
                    <h3>Product Management</h3>
                    <p>Monitor product listings, approve new items, and manage marketplace inventory.</p>
                    <div class="action-buttons">
                        <a href="admin_view_products.php" class="btn btn-primary">View Products</a>
                        <a href="admin_pending_products.php" class="btn btn-outline">Pending Approval</a>
                    </div>
                </div>

                <div class="action-card">
                    <div class="action-icon">💬</div>
                    <h3>Messages & Support</h3>
                    <p>Handle customer support tickets, monitor user communications, and resolve disputes.</p>
                    <div class="action-buttons">
                        <a href="admin_messages.php" class="btn btn-primary">Support Tickets</a>
                        <a href="admin_disputes.php" class="btn btn-outline">Dispute Resolution</a>
                    </div>
                </div>

                <div class="action-card">
                    <div class="action-icon">📊</div>
                    <h3>Analytics & Reports</h3>
                    <p>Access detailed analytics, sales reports, and platform performance metrics.</p>
                    <div class="action-buttons">
                        <a href="admin_analytics.php" class="btn btn-primary">View Analytics</a>
                        <a href="admin_reports.php" class="btn btn-outline">Generate Reports</a>
                    </div>
                </div>

                <div class="action-card">
                    <div class="action-icon">⚙️</div>
                    <h3>System Settings</h3>
                    <p>Configure platform settings, manage categories, and update system preferences.</p>
                    <div class="action-buttons">
                        <a href="admin_settings.php" class="btn btn-primary">System Settings</a>
                        <a href="admin_categories.php" class="btn btn-outline">Manage Categories</a>
                    </div>
                </div>

                <div class="action-card">
                    <div class="action-icon">🚨</div>
                    <h3>Security & Moderation</h3>
                    <p>Monitor security threats, review flagged content, and maintain platform safety.</p>
                    <div class="action-buttons">
                        <a href="admin_security.php" class="btn btn-secondary">Security Center</a>
                        <a href="admin_moderation.php" class="btn btn-outline">Content Moderation</a>
                    </div>
                </div>
            </section>

            <section class="recent-activity">
                <h3>Recent Activity</h3>
                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon">👤</div>
                        <div class="activity-content">
                            <div class="activity-title">New user registration: john.doe@email.com</div>
                            <div class="activity-time">2 minutes ago</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon">📦</div>
                        <div class="activity-content">
                            <div class="activity-title">Product listing pending approval: iPhone 15 Pro Max</div>
                            <div class="activity-time">15 minutes ago</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon">💰</div>
                        <div class="activity-content">
                            <div class="activity-title">Transaction completed: $1,299.99</div>
                            <div class="activity-time">1 hour ago</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon">⚠️</div>
                        <div class="activity-content">
                            <div class="activity-title">New report submitted for product #12345</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon">🔧</div>
                        <div class="activity-content">
                            <div class="activity-title">System maintenance completed successfully</div>
                            <div class="activity-time">3 hours ago</div>
                        </div>
                    </li>
                </ul>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 PariahTech Admin Panel. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>