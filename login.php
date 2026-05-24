<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Session variables store karein
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            
            echo "Login successful!";
        } else {
            echo "Wrong password!";
        }
    } else {
        echo "User not found!";
    }
}
$conn->close();
?>