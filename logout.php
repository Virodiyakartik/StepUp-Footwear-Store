<?php
session_start();

// 1. Saare session variables ko khali karein
$_SESSION = array();

// 2. Agar session cookies use ho rahi hain toh unhe bhi clear karein
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Poora session destroy karein
session_destroy();

// 4. Redirect karke wapas login page par bhej dein
header("Location: admin_login.php");
exit();
?>