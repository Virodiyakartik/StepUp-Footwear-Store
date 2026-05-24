<?php
session_start();
require_once 'db.php';

if(isset($_SESSION['user_email']) && isset($_POST['product_id'])) {
    $user_email = $_SESSION['user_email'];
    $product_id = (int)$_POST['product_id'];

    // Check karein ki pehle se wishlist mein hai ya nahi
    $check = $conn->query("SELECT * FROM wishlist WHERE user_email='$user_email' AND product_id=$product_id");
    
    if($check->num_rows > 0) {
        echo "exists";
    } else {
        $conn->query("INSERT INTO wishlist (user_email, product_id) VALUES ('$user_email', $product_id)");
        echo "success";
    }
} else {
    echo "auth_error";
}
?>