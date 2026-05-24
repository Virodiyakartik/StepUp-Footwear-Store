<?php
session_start();
require_once 'db.php';

// Agar user login nahi hai, toh use login page par bhejein
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$user_email = $_SESSION['user_email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders | StepUp</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #090a10; color: #fff; font-family: sans-serif; padding: 40px; }
        .orders-container { max-width: 900px; margin: 0 auto; background: #11131c; padding: 30px; border-radius: 12px; }
        h2 { border-bottom: 1px solid #222; padding-bottom: 15px; color: #00d2ff; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #222; }
        th { color: #9ca3af; text-transform: uppercase; font-size: 0.8rem; }
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .status-dispatched { background: rgba(0, 210, 255, 0.1); color: #00d2ff; }
        .status-delivered { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    </style>
</head>
<body>

<div class="orders-container">
    <h2><i class="fa-solid fa-bag-shopping"></i> My Orders History</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $conn->prepare("SELECT * FROM orders WHERE user_email = ? ORDER BY order_date DESC");
            $stmt->bind_param("s", $user_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $status_class = 'status-pending';
                    if($row['status'] == 'Dispatched') $status_class = 'status-dispatched';
                    if($row['status'] == 'Delivered') $status_class = 'status-delivered';
                    
                    echo "<tr>";
                    echo "<td>#STP-".$row['id']."</td>";
                    echo "<td>".htmlspecialchars($row['product_name'])."</td>";
                    echo "<td style='color:#00d2ff;'>".htmlspecialchars($row['price'])."</td>";
                    echo "<td>".$row['order_date']."</td>";
                    echo "<td><span class='status-badge $status_class'>".$row['status']."</span></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center; color:#555;'>Not Order Request You Sent By Admin......Thank You.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>