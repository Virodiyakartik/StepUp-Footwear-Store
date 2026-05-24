<?php
session_start();

// Browser cache disable taaki purana data load na ho back button par
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: admin_login.php"); 
    exit(); 
}
require_once 'db.php';

// Check karein ki URL mein ID mili ya nahi
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$p_id = (int)$_GET['id'];

// Database se product ka current data nikalen
$result = $conn->query("SELECT * FROM products WHERE p_id = $p_id");
$product = $result->fetch_assoc();

if (!$product) {
    echo "Product not found!";
    exit();
}

// UPDATE LOGIC
if (isset($_POST['update_product'])) {
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $target_file = $product['image_path']; // Default purana path rakhlo

    // Agar user ne nayi file select ki hai
    if (isset($_FILES["image_file"]["name"]) && $_FILES["image_file"]["name"] != "") {
        $target_dir = "uploads/";
        
        // Purani physical file delete karein agar naya photo daal rahe ho
        if (file_exists($product['image_path'])) {
            unlink($product['image_path']);
        }
        
        $file_name = time() . "_" . basename($_FILES["image_file"]["name"]);
        $target_file = $target_dir . $file_name;
        move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file);
    }

    // Database update execution query
    $update_query = "UPDATE products SET brand_name='$brand', product_title='$title', product_price='$price', image_path='$target_file' WHERE p_id=$p_id";
    
    if ($conn->query($update_query)) {
        // Success notification ke sath dashboard par bhejein
        header("Location: admin_dashboard.php?msg=status_updated");
        exit();
    } else {
        $error = "Error updating database: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>StepUp | Edit Shoe Configuration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { 
            --accent-blue: #00d2ff; 
            --bg-dark: #0f1113; 
            --card-bg: #1a1d21; 
        }
        body { background: var(--bg-dark); color: white; font-family: 'Inter', sans-serif; padding: 40px; }
        .container { max-width: 600px; margin: auto; }
        
        h1 { color: var(--accent-blue); font-weight: 800; margin-bottom: 10px; }
        .back-btn { color: var(--accent-blue); text-decoration: none; font-weight: bold; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 30px; }
        .back-btn:hover { text-decoration: underline; }

        /* Form Styling */
        .edit-form { background: var(--card-bg); padding: 35px; border-radius: 12px; border-left: 5px solid var(--accent-blue); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; color: #a0a0a0; }
        .edit-form input { background: #25292e; border: 1px solid #333; color: white; padding: 12px; border-radius: 6px; width: 100%; box-sizing: border-box; outline: none; transition: 0.3s; }
        .edit-form input:focus { border-color: var(--accent-blue); }
        
        /* Preview Frame */
        .current-preview { display: flex; align-items: center; gap: 20px; background: #0f1113; padding: 15px; border-radius: 8px; border: 1px solid #25292e; }
        .current-preview img { width: 80px; height: 80px; object-fit: contain; background: #000; border-radius: 4px; }

        .save-btn { background: var(--accent-blue); border: none; padding: 14px; font-weight: bold; cursor: pointer; border-radius: 6px; width: 100%; color: #000; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; margin-top: 10px; }
        .save-btn:hover { background: #fff; }
    </style>
</head>
<body>

<div class="container">
    <a href="admin_dashboard.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h1>Edit Product Asset</h1>
    <p style="color: #a0a0a0; margin-bottom: 30px;">Modify the target database record for shoe configurations.</p>

    <div class="edit-form">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Brand Manufacturer</label>
                <input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Model Title Name</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($product['product_title']); ?>" required>
            </div>

            <div class="form-group">
                <label>Asset Price Value</label>
                <input type="text" name="price" value="<?php echo htmlspecialchars($product['product_price']); ?>" required>
            </div>

            <div class="form-group">
                <label>Current Visual Source</label>
                <div class="current-preview">
                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="Current Shoe">
                    <div>
                        <span style="font-size: 0.85rem; color: #666; display: block; word-break: break-all;"><?php echo htmlspecialchars($product['image_path']); ?></span>
                        <span style="font-size: 0.8rem; color: var(--accent-blue);">Active Configuration</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Replace Image Matrix (Optional)</label>
                <input type="file" name="image_file" accept="image/*" style="border: none; background: none; padding: 5px 0;">
            </div>

            <button type="submit" name="update_product" class="save-btn">Apply Configuration Updates</button>
        </form>
    </div>
</div>

</body>
</html>