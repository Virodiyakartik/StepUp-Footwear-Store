<?php
// Database Credentials
$servername = "localhost";
$username = "root";      // XAMPP default username
$password = "";          // XAMPP default password (empty)
$dbname = "user_system"; // Aapke database ka naam

// Connection Create karein
$conn = new mysqli($servername, $username, $password, $dbname);

// Connection Check karein
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// UTF-8 Charset set karein (Optional: Hindi/Special chars ke liye)
$conn->set_charset("utf8");

// Ab aap is $conn variable ko kisi bhi file (admin_login.php, place_order.php) mein use kar sakte hain.
?>