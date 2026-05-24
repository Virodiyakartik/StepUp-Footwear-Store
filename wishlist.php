<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login_page.php");
    exit();
}

$user_email = $_SESSION['user_email'];

// Handle Remove from Wishlist
if(isset($_GET['remove_id'])) {
    $remove_id = (int)$_GET['remove_id'];
    $conn->query("DELETE FROM wishlist WHERE id = $remove_id AND user_email = '$user_email'");
    header("Location: wishlist.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | StepUp</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { background: #0f1113; color: #e0e0e0; font-family: 'Inter', sans-serif; padding: 40px 8%; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 20px; }
        .header h1 { color: #fff; font-size: 2rem; font-weight: 800; }
        .header h1 i { color: #ff3366; }
        .back-btn { color: #00d2ff; text-decoration: none; font-weight: 600; border: 1px solid #00d2ff; padding: 8px 16px; border-radius: 6px; transition: 0.3s; }
        .back-btn:hover { background: #00d2ff; color: #000; }

        .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; }
        .shoe-card { background: #1a1d21; padding: 25px; border-radius: 16px; text-align: center; border: 1px solid rgba(255,255,255,0.02); transition: 0.3s; position: relative; }
        .shoe-card:hover { transform: translateY(-8px); border-color: #00d2ff; }
        .shoe-card img { width: 100%; height: 200px; object-fit: contain; margin-bottom: 20px; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.6)); }
        .shoe-card h3 { font-size: 1.1rem; color: #fff; margin-bottom: 8px; }
        .shoe-card p { color: #00d2ff; font-weight: 800; font-size: 1.2rem; }
        
        .remove-btn { position: absolute; top: 15px; right: 15px; background: rgba(255,51,102,0.1); color: #ff3366; border: 1px solid rgba(255,51,102,0.3); width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; text-decoration: none; }
        .remove-btn:hover { background: #ff3366; color: #fff; }
    </style>
</head>
<body>

<div class="header">
    <h1><i class="fa-solid fa-heart"></i> My Saved Collection</h1>
    <a href="shop.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Shop</a>
</div>

<div class="wishlist-grid">
    <?php
    // Fetch user's wishlist joined with product details
    $query = "SELECT wishlist.id as wish_id, products.* FROM wishlist 
              INNER JOIN products ON wishlist.product_id = products.p_id 
              WHERE wishlist.user_email = '$user_email' ORDER BY wishlist.created_at DESC";
    $result = $conn->query($query);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            ?>
            <div class="shoe-card">
                <a href="wishlist.php?remove_id=<?php echo $row['wish_id']; ?>" class="remove-btn" title="Remove from Wishlist"><i class="fa-solid fa-trash"></i></a>
                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Shoe">
                <h3><?php echo htmlspecialchars($row['product_title']); ?></h3>
                <p><?php echo htmlspecialchars($row['product_price']); ?></p>
            </div>
            <?php
        }
    } else {
        echo "<p style='grid-column: 1/-1; text-align:center; color:#666;'>Your wishlist is completely empty.</p>";
    }
    ?>
</div>

</body>
</html>