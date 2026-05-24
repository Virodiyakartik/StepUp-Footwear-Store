<?php
session_start();
require_once 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_email'])) {
    $product_id = (int)$_POST['product_id'];
    $user_email = $_SESSION['user_email'];
    $rating = (int)$_POST['rating'];
    $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);

    $conn->query("INSERT INTO product_reviews (product_id, user_email, rating, review_text) VALUES ($product_id, '$user_email', $rating, '$review_text')");
    header("Location: " . $_SERVER['HTTP_REFERER']); // Wapas purane page par bhejein
    exit();
}