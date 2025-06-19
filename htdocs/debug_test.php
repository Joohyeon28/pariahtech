<?php
// Debug script to test database connection and login functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PariahTech Login Debug Test</h2>";

// Test 1: Database Connection
echo "<h3>1. Testing Database Connection</h3>";
try {
    require_once 'db_connect.php';
    echo "✅ Database connection successful<br>";
    echo "Database name: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit();
}

// Test 2: Check if tables exist
echo "<h3>2. Checking Database Tables</h3>";
$tables = ['users', 'products', 'purchases'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' does not exist<br>";
    }
}

// Test 3: Check users table structure
echo "<h3>3. Users Table Structure</h3>";
$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . ($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Could not describe users table<br>";
}

// Test 4: Count existing users
echo "<h3>4. Existing Users Count</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "Total users in database: $count<br>";
    
    if ($count > 0) {
        echo "<h4>Existing Users:</h4>";
        $users = $conn->query("SELECT id, name, surname, email, user_type FROM users");
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Surname</th><th>Email</th><th>Type</th></tr>";
        while ($user = $users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['name'] . "</td>";
            echo "<td>" . $user['surname'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['user_type'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Test 5: Session test
echo "<h3>5. Session Test</h3>";
session_start();
echo "Session ID: " . session_id() . "<br>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User is logged in. User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User type: " . ($_SESSION['user_type'] ?? 'Not set') . "<br>";
    echo "User name: " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";
} else {
    echo "❌ No user logged in<br>";
}

// Test 6: Test form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<h3>6. Form Submission Test</h3>";
    echo "Form data received:<br>";
    foreach ($_POST as $key => $value) {
        echo "$key: " . htmlspecialchars($value) . "<br>";
    }
    
    // Test login process
    $email = $conn->real_escape_string($_POST['email']);
    $name = $conn->real_escape_string($_POST['name']);
    $surname = $conn->real_escape_string($_POST['surname']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $user_type = $_POST['user_type'];
    
    echo "<br>Processing login for email: $email<br>";
    
    // Check if user exists
    $check = $conn->query("SELECT * FROM users WHERE email = '$email'");
    echo "Users found with this email: " . $check->num_rows . "<br>";
    
    if ($check->num_rows == 0) {
        // Insert new user
        $insert_sql = "INSERT INTO users (name, surname, email, contact, user_type) 
                      VALUES ('$name', '$surname', '$email', '$contact', '$user_type')";
        echo "Inserting new user with SQL: $insert_sql<br>";
        
        if ($conn->query($insert_sql)) {
            echo "✅ User inserted successfully<br>";
        } else {
            echo "❌ Error inserting user: " . $conn->error . "<br>";
        }
    }
    
    // Try to log in
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_type'] = $row['user_type'];
        $_SESSION['user_name'] = $row['name'];
        
        echo "✅ Login successful!<br>";
        echo "User ID: " . $row['id'] . "<br>";
        echo "User Type: " . $row['user_type'] . "<br>";
        echo "User Name: " . $row['name'] . "<br>";
        
        echo "<br><a href='debug_test.php'>Refresh page to see session status</a><br>";
    } else {
        echo "❌ Login failed - user not found after insert<br>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { margin: 10px 0; }
        th, td { padding: 8px; text-align: left; }
        .form-section { background: #f0f0f0; padding: 20px; margin: 20px 0; }
    </style>
</head>
<body>

<div class="form-section">
    <h3>Test Login Form</h3>
    <form method="POST">
        <label>First Name: <input type="text" name="name" required></label><br><br>
        <label>Surname: <input type="text" name="surname" required></label><br><br>
        <label>Email: <input type="email" name="email" required></label><br><br>
        <label>Contact: <input type="text" name="contact" required></label><br><br>
        <label>User Type: 
            <select name="user_type">
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
            </select>
        </label><br><br>
        <input type="submit" value="Test Login">
    </form>
</div>

<p><strong>Instructions:</strong></p>
<ol>
    <li>First, check that all database connections and tables are working above</li>
    <li>Fill out the test form and submit it</li>
    <li>Check if the login process works correctly</li>
    <li>Then try accessing your actual login.php page</li>
</ol>

<p><a href="login.php">Go to actual login page</a> | <a href="index.php">Go to home page</a></p>

</body>
</html>