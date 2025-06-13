<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['product_name'] ?? '');
    $price = floatval($_POST['product_price'] ?? 0);
    $condition = trim($_POST['product_condition'] ?? '');
    $description = trim($_POST['product_description'] ?? '');
    
    // Validate inputs
    if (empty($name) || empty($condition) || empty($description) || $price <= 0) {
        $error_message = "All fields are required and price must be greater than 0.";
    } else {
        // Handle file upload
        $image_name = '';
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
                $image_name = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
                $upload_path = 'images/' . $image_name;
                
                if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                    $error_message = "Failed to upload image.";
                    $image_name = '';
                }
            }
        } else {
            $error_message = "Please select an image file.";
        }
        
        // Insert product if no errors
        if (empty($error_message)) {
            try {
                $stmt = $conn->prepare("INSERT INTO products (name, price, `condition`, description, image, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sdsssi", $name, $price, $condition, $description, $image_name, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $message = "Product added successfully!";
                    // Clear form data
                    $_POST = array();
                } else {
                    $error_message = "Error adding product: " . $conn->error;
                    // Delete uploaded image if database insert failed
                    if (!empty($image_name) && file_exists('images/' . $image_name)) {
                        unlink('images/' . $image_name);
                    }
                }
            } catch (Exception $e) {
                $error_message = "Database error: " . $e->getMessage();
                // Delete uploaded image if database insert failed
                if (!empty($image_name) && file_exists('images/' . $image_name)) {
                    unlink('images/' . $image_name);
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
    <title>Add Product - PariahTech Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-input {
            width: 100%;
            padding: 12px;
            border: 2px dashed #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        
        .file-input:hover {
            border-color: #003366;
        }
        
        .image-preview {
            margin-top: 15px;
            text-align: center;
        }
        
        .image-preview img {
            max-width: 300px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .submit-btn {
            background-color: #999;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            cursor: not-allowed;
            border-radius: 4px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .submit-btn:enabled {
            background-color: #003366;
            cursor: pointer;
        }
        
        .submit-btn:enabled:hover {
            background-color: #00264d;
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
            margin-bottom: 20px;
            color: #003366;
            text-decoration: none;
            font-weight: bold;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .required {
            color: #dc3545;
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
        <div class="form-container">
            <a href="admin_view_products.php" class="back-link">← Back to Product Management</a>
            
            <h2>Add New Product</h2>
            
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
            
            <form action="admin_add_product.php" method="POST" enctype="multipart/form-data" id="productForm">
                <div class="form-group">
                    <label for="product_name">Product Name <span class="required">*</span></label>
                    <input type="text" 
                           name="product_name" 
                           id="product_name" 
                           value="<?php echo htmlspecialchars($_POST['product_name'] ?? ''); ?>" 
                           required 
                           placeholder="Enter product name">
                </div>
                
                <div class="form-group">
                    <label for="product_price">Price (R) <span class="required">*</span></label>
                    <input type="number" 
                           name="product_price" 
                           id="product_price" 
                           value="<?php echo htmlspecialchars($_POST['product_price'] ?? ''); ?>" 
                           min="0.01" 
                           step="0.01" 
                           required 
                           placeholder="0.00">
                </div>
                
                <div class="form-group">
                    <label for="product_condition">Condition <span class="required">*</span></label>
                    <select name="product_condition" id="product_condition" required>
                        <option value="">Select condition</option>
                        <option value="New" <?php echo (($_POST['product_condition'] ?? '') === 'New') ? 'selected' : ''; ?>>New</option>
                        <option value="Like New" <?php echo (($_POST['product_condition'] ?? '') === 'Like New') ? 'selected' : ''; ?>>Like New</option>
                        <option value="Very Good" <?php echo (($_POST['product_condition'] ?? '') === 'Very Good') ? 'selected' : ''; ?>>Very Good</option>
                        <option value="Good" <?php echo (($_POST['product_condition'] ?? '') === 'Good') ? 'selected' : ''; ?>>Good</option>
                        <option value="Fair" <?php echo (($_POST['product_condition'] ?? '') === 'Fair') ? 'selected' : ''; ?>>Fair</option>
                        <option value="Poor" <?php echo (($_POST['product_condition'] ?? '') === 'Poor') ? 'selected' : ''; ?>>Poor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="product_description">Description <span class="required">*</span></label>
                    <textarea name="product_description" 
                              id="product_description" 
                              required 
                              placeholder="Describe the product in detail..."><?php echo htmlspecialchars($_POST['product_description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="product_image">Product Image <span class="required">*</span></label>
                    <input type="file" 
                           name="product_image" 
                           id="product_image" 
                           accept="image/*" 
                           required 
                           class="file-input">
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Supported formats: JPG, JPEG, PNG, GIF. Maximum size: 5MB
                    </small>
                    <div id="imagePreview" class="image-preview" style="display: none;">
                        <img id="previewImg" src="" alt="Preview">
                    </div>
                </div>
                
                <button type="submit" id="upload_button" class="submit-btn" disabled>
                    Add Product
                </button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 PariahTech</p>
    </footer>

    <script src="scripts.js"></script>
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