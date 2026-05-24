<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit(); }
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_id = trim($_POST['new_id']);
    $new_pass = trim($_POST['new_pass']);
    $confirm_pass = trim($_POST['confirm_pass']);

    if ($new_pass === $confirm_pass) {
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
        
        // Database update query
        $stmt = $conn->prepare("UPDATE admin SET admin_id = ?, password = ?");
        $stmt->bind_param("ss", $new_id, $hashed_password);
        
        if ($stmt->execute()) {
            echo "<script>alert('ID and Password Updated Successfully!'); window.location='admin_dashboard.php';</script>";
        } else {
            echo "Error updating records: " . $conn->error;
        }
    } else {
        echo "Passwords do not match!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Admin Credentials</title>
    <style>
        body { background: #0f1113; color: white; font-family: sans-serif; display: flex; justify-content: center; padding-top: 50px; }
        .box { background: #1a1d21; padding: 30px; border: 1px solid #00d2ff; border-radius: 10px; width: 400px; }
        input { width: 100%; padding: 10px; margin: 10px 0; background: #25292e; border: 1px solid #333; color: white; }
        button { background: #00d2ff; border: none; padding: 10px; width: 100%; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Change Credentials</h2>
        <form method="POST">
            <label>New Admin ID:</label>
            <input type="text" name="new_id" value="admin_stepup" required>
            
            <label>New Password:</label>
            <input type="password" name="new_pass" placeholder="Enter New Password" required>
            
            <label>Confirm Password:</label>
            <input type="password" name="confirm_pass" placeholder="Confirm New Password" required>
            
            <button type="submit">Update Credentials</button>
        </form>
    </div>
</body>
</html>