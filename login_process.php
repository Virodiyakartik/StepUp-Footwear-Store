if (password_verify($password, $user['password'])) {
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_name'] = $user['username']; // Yeh line add karein
    $_SESSION['user_email'] = $user['email'];
    header("Location: index.php");
    exit();
}