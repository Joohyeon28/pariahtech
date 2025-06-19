<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$error_message = "";
$product = null;

// Get product ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_view_products.php");
    exit();
}

$product_id = intval($_GET['id']);

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: admin_view_products.php");
    exit();
}

$product = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['product_name'] ?? '');
    $price = floatval($_POST['product_price'] ?? 0);
    $condition = trim($_POST['product_condition'] ?? '');
    $description = trim($_POST['product_description'] ?? '');
    $current_image = $product['image'];
    $new_image = $current_image;
    
    // Validate inputs
    if (empty($name) || empty($condition) || empty($description) || $price <= 0) {
        $error_message = "All fields are required and price must be greater than 0.";
    } else {
        // Handle file upload if a new image was provided
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['product_image']['type'], $allowed_types)) {
                $error_message = "Only JPG, JPEG, PNG and GIF files are allowed.";
            } elseif ($_FILES['product_image']['size'] > $max_size) {
                $error_message = "File size must be less than 5MB.";
            } else {
                // Create images directory if it doesn't exist
                if (!file_exists('images')) {
                    mkdir('images', 0777, true);
                }
                
                // Generate unique filename
                $file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                $new_image = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
                $upload_path = 'images/' . $new_image;
                
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                    // Delete old image if it exists and is different from new one
                    if (!empty($current_image) && $current_image !== $new_image && file_exists('images/' . $current_image)) {
                        unlink('images/' . $current_image);
                    }
                } else {
                    $error_message = "Failed to upload image.";
                    $new_image = $current_image;
                }
            }
        }
        
        // Update product if no errors
        if (empty($error_message)) {
            try {
                $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, `condition` = ?, description = ?, image = ? WHERE id = ?");
                $stmt->bind_param("sdsssi", $name, $price, $condition, $description, $new_image, $product_id);
                
                if ($stmt->execute()) {
                    $message = "Product updated successfully!";
                    // Update local product data
                    $product['name'] = $name;
                    $product['price'] = $price;
                    $product['condition'] = $condition;
                    $product['description'] = $description;
                    $product['image'] = $new_image;
                } else {
                    $error_message = "Error updating product: " . $conn->error;
                    // Delete new image if database update failed
                    if ($new_image !== $current_image && file_exists('images/' . $new_image)) {
                        unlink('images/' . $new_image);
                    }
                }
            } catch (Exception $e) {
                $error_message = "Database error: " . $e->getMessage();
                // Delete new image if database update failed
                if ($new_image !== $current_image && file_exists('images/' . $new_image)) {
                    unlink('images/' . $new_image);
                }
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
    <title>Edit Product - PariahTech Admin</title>
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

        .btn-danger {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .file-input {
            width: 100%;
            padding: 12px;
            border: 2px dashed rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input:hover {
            border-color: #667eea;
            background: rgba(255, 255, 255, 0.7);
        }

        .image-preview {
            margin-top: 15px;
            text-align: center;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .current-image {
            margin-top: 15px;
            text-align: center;
        }

        .current-image img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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

        .required {
            color: #ff6b6b;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
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

            .btn-group {
                flex-direction: column;
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
        <a href="admin_view_products.php" class="back-link">&larr; Back to Product Management</a>
        
        <div class="admin-container">
            <h2>Edit Product</h2>
            
            <?php if (!empty($message)): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form action="admin_edit_product.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product_name">Product Name <span class="required">*</span></label>
                    <input type="text" 
                           name="product_name" 
                           id="product_name" 
                           value="<?php echo htmlspecialchars($product['name']); ?>" 
                           required 
                           placeholder="Enter product name">
                </div>
                
                <div class="form-group">
                    <label for="product_price">Price (R) <span class="required">*</span></label>
                    <input type="number" 
                           name="product_price" 
                           id="product_price" 
                           value="<?php echo htmlspecialchars($product['price']); ?>" 
                           min="0.01" 
                           step="0.01" 
                           required 
                           placeholder="0.00">
                </div>
                
                <div class="form-group">
                    <label for="product_condition">Condition <span class="required">*</span></label>
                    <select name="product_condition" id="product_condition" required>
                        <option value="">Select condition</option>
                        <option value="New" <?php echo ($product['condition'] === 'New') ? 'selected' : ''; ?>>New</option>
                        <option value="Like New" <?php echo ($product['condition'] === 'Like New') ? 'selected' : ''; ?>>Like New</option>
                        <option value="Very Good" <?php echo ($product['condition'] === 'Very Good') ? 'selected' : ''; ?>>Very Good</option>
                        <option value="Good" <?php echo ($product['condition'] === 'Good') ? 'selected' : ''; ?>>Good</option>
                        <option value="Fair" <?php echo ($product['condition'] === 'Fair') ? 'selected' : ''; ?>>Fair</option>
                        <option value="Poor" <?php echo ($product['condition'] === 'Poor') ? 'selected' : ''; ?>>Poor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="product_description">Description <span class="required">*</span></label>
                    <textarea name="product_description" 
                              id="product_description" 
                              required 
                              placeholder="Describe the product in detail..."><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Current Image</label>
                    <div class="current-image">
                        <?php if (!empty($product['image']) && file_exists('images/' . $product['image'])): ?>
                            <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="Current Product Image">
                        <?php else: ?>
                            <p>No image available</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="product_image">New Image (Leave blank to keep current)</label>
                    <input type="file" 
                           name="product_image" 
                           id="product_image" 
                           accept="image/*" 
                           class="file-input">
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Supported formats: JPG, JPEG, PNG, GIF. Maximum size: 5MB
                    </small>
                    <div id="imagePreview" class="image-preview" style="display: none;">
                        <img id="previewImg" src="" alt="Preview">
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        Update Product
                    </button>
                    <a href="admin_view_products.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 PariahTech. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Image preview functionality
        document.getElementById('product_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>