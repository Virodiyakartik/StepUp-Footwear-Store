<?php
require_once 'db.php';

$id = "2907";
$pass = "2907";
$hash = password_hash($pass, PASSWORD_DEFAULT);

$conn->query("TRUNCATE TABLE admin");
$stmt = $conn->prepare("INSERT INTO admin (admin_id, password) VALUES (?, ?)");
$stmt->bind_param("ss", $id, $hash);

if ($stmt->execute()) {
    echo "<h2>Success! Admin Updated.</h2>";
    echo "ID: 2907 <br> Pass: 2907 <br>";
    echo "<p>Ab login karke dekho, error nahi aayega.</p>";
} else {
    echo "Error: " . $conn->error;
}
?>