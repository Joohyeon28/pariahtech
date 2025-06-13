<?php
session_start();
require_once "db_connect.php"; // Fixed path - removed includes/

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect'] = 'sell.php';
    header("Location: login.php");
    exit;
}

$message = "";
$message_type = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string(trim($_POST['product_name']));
    $price = $conn->real_escape_string(trim($_POST['product_price']));
    $condition = $conn->real_escape_string(trim($_POST['product_condition']));
    $description = $conn->real_escape_string(trim($_POST['product_description']));
    $category = $conn->real_escape_string(trim($_POST['product_category']));
    $user_id = $_SESSION['user_id'];

    $imageName = "";
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['product_image']['tmp_name'];
        $fileName = $_FILES['product_image']['name'];
        $fileSize = $_FILES['product_image']['size'];
        $fileType = $_FILES['product_image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Create images directory if it doesn't exist
            $uploadFileDir = 'images/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $imageName = $newFileName;
            } else {
                $message = "Error uploading the image file.";
                $message_type = "error";
            }
        } else {
            $message = "Upload failed. Allowed file types: " . implode(', ', $allowedfileExtensions);
            $message_type = "error";
        }
    } else {
        $message = "Image upload is required.";
        $message_type = "error";
    }

    if (empty($message)) {
        // Using prepared statements for better security
        $stmt = $conn->prepare("INSERT INTO products (name, price, `condition`, description, category, image, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sdssssi", $name, $price, $condition, $description, $category, $imageName, $user_id);
        
        if ($stmt->execute()) {
            $message = "Product uploaded successfully! Your listing is now live.";
            $message_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Your Tech - PariahTech</title>
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
            padding: 30px 0;
        }

        .sell-container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sell-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .sell-header h2 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .sell-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .alert-error {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .alert-success {
            background: linear-gradient(45deg, #00b894, #00a085);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 184, 148, 0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 30px;
            border: 2px dashed rgba(102, 126, 234, 0.3);
            border-radius: 10px;
            background: rgba(102, 126, 234, 0.05);
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .file-upload-label:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .file-upload-icon {
            font-size: 24px;
            color: #667eea;
        }

        .file-upload-text {
            color: #2c3e50;
            font-weight: 500;
        }

        .btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            margin-top: 20px;
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.6);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn:active:not(:disabled) {
            transform: translateY(-1px);
        }

        .sell-tips {
            background: rgba(240, 248, 255, 0.8);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        .sell-tips h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .sell-tips ul {
            list-style: none;
            padding: 0;
        }

        .sell-tips li {
            padding: 5px 0;
            color: #2c3e50;
            position: relative;
            padding-left: 20px;
        }

        .sell-tips li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #00b894;
            font-weight: bold;
        }

        footer {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            color: white;
            text-align: center;
            padding: 30px 0;
            margin-top: 50px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-section h4 {
            margin-bottom: 15px;
            color: #667eea;
        }

        .footer-section p,
        .footer-section ul {
            opacity: 0.8;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 8px;
        }

        .footer-section ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: #667eea;
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

            .sell-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .sell-header h2 {
                font-size: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 0 10px;
            }
        }
    </style>
    <script>
        function validateForm() {
            const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
            let allValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    allValid = false;
                }
            });
            
            const submitBtn = document.getElementById('upload_button');
            submitBtn.disabled = !allValid;
        }

        function updateFileName() {
            const fileInput = document.getElementById('product_image');
            const label = document.querySelector('.file-upload-text');
            
            if (fileInput.files.length > 0) {
                label.textContent = `Selected: ${fileInput.files[0].name}`;
            } else {
                label.innerHTML = '<strong>Choose Image File</strong><br><small>JPG, PNG, GIF up to 5MB</small>';
            }
            validateForm();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                input.addEventListener('input', validateForm);
                input.addEventListener('change', validateForm);
            });
            
            validateForm();
        });
    </script>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <div class="logo-icon">PT</div>
                    <h1>PariahTech</h1>
                </a>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="buy.php">Buy</a></li>
                        <li><a href="sell.php" class="active">Sell</a></li>
                        <li><a href="logout.php">Logout</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="sell-container">
                <div class="sell-header">
                    <h2>Sell Your Tech</h2>
                    <p>List your technology products and reach thousands of buyers</p>
                </div>

                <div class="sell-tips">
                    <h3>📋 Selling Tips</h3>
                    <ul>
                        <li>Use clear, high-quality photos</li>
                        <li>Write detailed, honest descriptions</li>
                        <li>Set competitive prices</li>
                        <li>Respond quickly to buyer inquiries</li>
                    </ul>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <strong><?php echo $message_type === 'success' ? '✅ Success:' : '⚠️ Error:'; ?></strong> 
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form action="sell.php" method="post" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product_name">Product Name</label>
                            <input type="text" id="product_name" name="product_name" required 
                                   placeholder="e.g., iPhone 15 Pro Max">
                        </div>

                        <div class="form-group">
                            <label for="product_price">Price (R)</label>
                            <input type="number" id="product_price" name="product_price" 
                                   step="0.01" min="0" required placeholder="0.00">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="product_condition">Condition</label>
                            <select id="product_condition" name="product_condition" required>
                                <option value="">Select condition</option>
                                <option value="Brand New">Brand New</option>
                                <option value="Like New">Like New</option>
                                <option value="Excellent">Excellent</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="For Parts">For Parts</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="product_category">Category</label>
                            <select id="product_category" name="product_category" required>
                                <option value="">Select category</option>
                                <option value="Smartphones">📱 Smartphones</option>
                                <option value="Laptops">💻 Laptops</option>
                                <option value="Gaming">🎮 Gaming</option>
                                <option value="Cameras">📷 Cameras</option>
                                <option value="Monitors">🖥️ Monitors</option>
                                <option value="Audio">🎧 Audio</option>
                                <option value="Tablets">📋 Tablets</option>
                                <option value="Accessories">🔌 Accessories</option>
                                <option value="Other">📦 Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="product_description">Description</label>
                        <textarea id="product_description" name="product_description" required 
                                  placeholder="Describe your product in detail. Include specifications, condition details, what's included, etc."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="product_image">Product Image</label>
                        <div class="file-upload">
                            <input type="file" id="product_image" name="product_image" 
                                   accept="image/*" required class="file-upload-input" onchange="updateFileName()">
                            <label for="product_image" class="file-upload-label">
                                <span class="file-upload-icon">📸</span>
                                <span class="file-upload-text">
                                    <strong>Choose Image File</strong><br>
                                    <small>JPG, PNG, GIF up to 5MB</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" id="upload_button" class="btn" disabled>
                        🚀 List My Product
                    </button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>About PariahTech</h4>
                    <p>We're the leading C2C marketplace for technology enthusiasts. Founded in 2025, we've helped thousands of people buy and sell their tech safely and efficiently.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#">How to Buy</a></li>
                        <li><a href="#">How to Sell</a></li>
                        <li><a href="#">Safety Tips</a></li>
                        <li><a href="#">Payment Methods</a></li>
                        <li><a href="#">Shipping Info</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Support</a></li>
                        <li><a href="#">Report an Issue</a></li>
                        <li><a href="#">Community Guidelines</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Connect With Us</h4>
                    <ul>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Instagram</a></li>
                        <li><a href="#">LinkedIn</a></li>
                        <li><a href="#">Newsletter</a></li>
                    </ul>
                </div>
            </div>
            <p>&copy; 2025 PariahTech. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>