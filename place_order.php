<?php
session_start();
require_once 'db.php';

$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'guest_user@gmail.com';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = mysqli_real_escape_string($conn, $_POST['product']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $size = mysqli_real_escape_string($conn, $_POST['size']);
    
    // Naya data fields extraction for management loop
    $pay_method = mysqli_real_escape_string($conn, $_POST['pay_method']);
    $txn_id = isset($_POST['txn_id']) ? mysqli_real_escape_string($conn, $_POST['txn_id']) : '';
    
    // Status metrics initialization mapping
    $p_status = ($pay_method === 'Online') ? 'Paid (Awaiting Verification)' : 'Pending';

    $query = "INSERT INTO orders (user_email, product_name, price, size, status, payment_method, transaction_id, payment_status) 
              VALUES ('$user_email', '$product_name', '$price', '$size', 'Pending', '$pay_method', " . ($txn_id !== '' ? "'$txn_id'" : "NULL") . ", '$p_status')";
              
    if ($conn->query($query)) {
        echo "Order Request Sent Successfully to Admin Logs!";
    } else {
        echo "Database Log Error: " . $conn->error;
    }
}
?>