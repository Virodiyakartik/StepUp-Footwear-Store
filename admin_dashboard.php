<?php
session_start();

// --- 1. BROWSER CACHE DISABLE (Logout Back-Button Protection) ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: admin_login.php"); 
    exit(); 
}
require_once 'db.php';

// --- DATABASE STRUCTURAL INTEGRITY PATCH ---
$conn->query("ALTER TABLE messages ADD COLUMN IF NOT EXISTS admin_reply TEXT NULL AFTER message");

// --- AUTOMATIC AUTO-PURGE LOGIC: 24 HOURS LIFECYCLE CLEANER ---
$conn->query("DELETE FROM deleted_orders WHERE deleted_at < NOW() - INTERVAL 1 DAY");

// --- CONTROLLER: USER DIRECTORY PURGE OPERATOR (NEW) ---
if (isset($_GET['purge_user_id'])) {
    $user_id = (int)$_GET['purge_user_id'];
    $conn->query("DELETE FROM users WHERE id = $user_id");
    header("Location: admin_dashboard.php?msg=user_purged#users-directory-panel");
    exit();
}

// --- BACKGROUND ASYNCHRONOUS AJAX INTERFACE CONTROLLER FOR DISPATCH ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_dispatch_reply'])) {
    $msg_id = (int)$_POST['target_msg_id'];
    $reply_text = mysqli_real_escape_string($conn, $_POST['admin_reply_content']);
    
    $update = $conn->query("UPDATE messages SET admin_reply = '$reply_text' WHERE id = $msg_id");
    if($update) {
        echo "success";
    } else {
        echo "Database Write Error: " . $conn->error;
    }
    exit();
}

// --- CONTROLLER: UPDATE PAYMENT STATUS DIRECTLY ---
if (isset($_POST['update_payment_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_pay_status = mysqli_real_escape_string($conn, $_POST['payment_status_val']);
    $conn->query("UPDATE orders SET payment_status='$new_pay_status' WHERE id=$order_id");
    header("Location: admin_dashboard.php?msg=status_updated#finance-panel");
    exit();
}

// --- DYNAMIC CONTROLLER: INBOX MESSAGE PURGE OPERATOR ---
if (isset($_GET['delete_msg_id'])) {
    $msg_id = (int)$_GET['delete_msg_id'];
    $conn->query("DELETE FROM messages WHERE id = $msg_id");
    header("Location: admin_dashboard.php?msg=msg_deleted#messages-panel");
    exit();
}

// --- STATUS UPDATE DROPDOWN LOGIC ---
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['order_status']);
    $conn->query("UPDATE orders SET status='$new_status' WHERE id=$order_id");
    header("Location: admin_dashboard.php?msg=status_updated#orders-panel");
    exit();
}

// --- SAFE DELETE WITH ARCHIVE LOGGER ---
if (isset($_GET['delete_order_id'])) {
    $order_id = (int)$_GET['delete_order_id'];
    $fetch_order = $conn->query("SELECT * FROM orders WHERE id = $order_id");
    if($fetch_order->num_rows > 0) {
        $o_data = $fetch_order->fetch_assoc();
        $u_email = mysqli_real_escape_string($conn, $o_data['user_email']);
        $p_name = mysqli_real_escape_string($conn, $o_data['product_name']);
        $p_price = mysqli_real_escape_string($conn, $o_data['price']);
        $p_status = mysqli_real_escape_string($conn, $o_data['status']);
        
        $conn->query("INSERT INTO deleted_orders (original_order_id, user_email, product_name, price, status) 
                      VALUES ($order_id, '$u_email', '$p_name', '$p_price', '$p_status')");
    }
    $conn->query("DELETE FROM orders WHERE id = $order_id");
    header("Location: admin_dashboard.php?msg=order_deleted#orders-panel");
    exit();
}

// --- ADD Product Logic ---
if (isset($_POST['add_product'])) {
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_name = time() . "_" . basename($_FILES["image_file"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
        $conn->query("INSERT INTO products (brand_name, product_title, product_price, image_path) VALUES ('$brand', '$title', '$price', '$target_file')");
        header("Location: admin_dashboard.php?msg=added#inventory-panel");
        exit();
    }
}

// --- DELETE Product Logic ---
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $res = $conn->query("SELECT image_path FROM products WHERE p_id = $id");
    $row = $res->fetch_assoc();
    if($row && file_exists($row['image_path'])) { unlink($row['image_path']); }
    $conn->query("DELETE FROM products WHERE p_id = $id");
    header("Location: admin_dashboard.php?msg=deleted#inventory-panel");
    exit();
}

// --- ANALYTICS CALCULATIONS ---
$earning_res = $conn->query("SELECT price FROM orders WHERE status IN ('Accepted', 'Delivered', 'Dispatched')");
$total_revenue = 0;
while($e_row = $earning_res->fetch_assoc()) {
    $clean_price = (int)preg_replace('/[^0-9]/', '', $e_row['price']);
    $total_revenue += $clean_price;
}

// Fetch variables for Chart.js and Data blocks
$pending_count = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status='Pending'")->fetch_assoc()['total'];
$accepted_count = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status='Accepted'")->fetch_assoc()['total'];
$dispatched_count = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status='Dispatched'")->fetch_assoc()['total'];
$delivered_count = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status='Delivered'")->fetch_assoc()['total'];

$inventory_count = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$total_registered_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepUp Suite | Enterprise Administration Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --bg-base: #090a10;
            --bg-surface: #11131c;
            --bg-card: #181a26;
            --accent-blue: #00d2ff;
            --accent-cyan: #00d2ff;
            --accent-emerald: #10b981;
            --accent-amber: #f59e0b;
            --accent-rose: #f43f5e;
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --border-muted: rgba(255, 255, 255, 0.04);
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-base); color: var(--text-primary); display: flex; min-height: 100vh; overflow: hidden; }

        .sidebar { width: var(--sidebar-width); background: var(--bg-surface); border-right: 1px solid var(--border-muted); position: fixed; height: 100vh; display: flex; flex-direction: column; padding: 30px 20px; z-index: 100; }
        .sidebar-brand { font-size: 1.4rem; font-weight: 800; letter-spacing: 2px; margin-bottom: 40px; color: #fff; text-decoration: none; }
        .sidebar-brand span { color: var(--accent-cyan); }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 8px; }
        .sidebar-link { display: flex; align-items: center; gap: 14px; color: var(--text-secondary); text-decoration: none; padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; font-weight: 500; transition: all 0.3s ease; cursor: pointer; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(0, 210, 255, 0.05); color: var(--accent-cyan); }
        .sidebar-link i { font-size: 1.1rem; width: 20px; }
        .sidebar-footer { margin-top: auto; border-top: 1px solid var(--border-muted); padding-top: 20px; }
        .logout-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; border: 1px solid var(--accent-rose); color: var(--accent-rose); padding: 12px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; transition: 0.3s; }
        .logout-btn:hover { background: var(--accent-rose); color: #fff; box-shadow: 0 4px 15px rgba(244, 63, 94, 0.3); }

        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px 50px; height: 100vh; overflow-y: auto; min-width: 0; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; border-bottom: 1px solid var(--border-muted); padding-bottom: 20px; }
        .top-header h1 { font-size: 1.8rem; font-weight: 800; letter-spacing: -0.5px; }
        .system-date { font-size: 0.85rem; color: var(--text-secondary); font-weight: 500; }

        .alert-msg { background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--accent-emerald); padding: 14px 20px; border-radius: 8px; margin-bottom: 30px; font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; animation: slideDown 0.4s ease; }
        @keyframes slideDown { from { transform: translateY(-10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 35px; }
        .stat-card { background: var(--bg-surface); border: 1px solid var(--border-muted); border-radius: 12px; padding: 24px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .stat-details p { color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 6px; }
        .stat-details h2 { font-size: 1.8rem; font-weight: 800; color: #fff; }
        .stat-icon { font-size: 1.6rem; color: var(--accent-cyan); background: rgba(0, 210, 255, 0.04); width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid rgba(0, 210, 255, 0.08); }

        .tab-panel { display: none; }
        .tab-panel.active-panel { display: block; animation: panelFade 0.4s ease; }
        @keyframes panelFade { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        .panel-section { background: var(--bg-surface); border: 1px solid var(--border-muted); border-radius: 14px; padding: 30px; margin-bottom: 40px; box-shadow: 0 4px 25px rgba(0,0,0,0.15); position: relative; }
        .panel-section h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 22px; display: flex; align-items: center; gap: 10px; }
        .panel-section h3 i { color: var(--accent-cyan); }

        .filter-wrapper-row { display: flex; gap: 15px; margin-bottom: 25px; background: rgba(255,255,255,0.02); padding: 15px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.03); align-items: center; justify-content: space-between; flex-wrap: wrap; }
        .filter-input { background: #090a10; color: white; border: 1px solid #2e3245; padding: 10px 14px; border-radius: 6px; outline: none; font-size: 0.85rem; width: 100%; max-width: 320px; }
        .filter-input:focus { border-color: var(--accent-blue); }
        .filter-action-btn { background: var(--accent-cyan); border: none; padding: 10px 20px; color: black; font-weight: 700; border-radius: 6px; font-size: 0.85rem; cursor: pointer; text-transform: uppercase; transition: 0.3s; }
        .filter-action-btn:hover { background: white; }

        .pdf-download-btn { background: rgba(16, 185, 129, 0.1); border: 1px solid var(--accent-emerald); padding: 10px 20px; color: var(--accent-emerald); border-radius: 6px; font-size: 0.85rem; font-weight: 700; cursor: pointer; text-transform: uppercase; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .pdf-download-btn:hover { background: var(--accent-emerald); color: black; box-shadow: 0 0 15px rgba(16, 185, 129, 0.3); }

        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #161924; color: var(--text-secondary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; padding: 16px 20px; border-bottom: 1px solid var(--border-muted); }
        td { padding: 18px 20px; border-bottom: 1px solid var(--border-muted); font-size: 0.9rem; color: var(--text-primary); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255, 255, 255, 0.01); }

        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .badge-pending { background: rgba(245, 158, 11, 0.08); color: var(--accent-amber); border: 1px solid rgba(245, 158, 11, 0.15); }
        .badge-accepted { background: rgba(16, 185, 129, 0.08); color: var(--accent-emerald); border: 1px solid rgba(16, 185, 129, 0.15); }
        .badge-dispatched { background: rgba(0, 210, 255, 0.08); color: var(--accent-cyan); border: 1px solid rgba(0, 210, 255, 0.15); }
        .badge-delivered { background: rgba(255, 255, 255, 0.05); color: #fff; border: 1px solid rgba(255, 255, 255, 0.1); }
        .badge-cancelled { background: rgba(244, 63, 94, 0.08); color: var(--accent-rose); border: 1px solid rgba(244, 63, 94, 0.15); }

        .strategy-badge-cod { background: rgba(255,255,255,0.03); border: 1px solid #333; color: var(--text-primary); padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 0.8rem; }
        .strategy-badge-online { background: rgba(0, 210, 255, 0.05); border: 1px solid rgba(0,210,255,0.2); color: var(--accent-cyan); padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem; }

        .status-select { background: var(--bg-card); color: var(--text-primary); border: 1px solid #2e3245; padding: 8px 12px; border-radius: 6px; outline: none; cursor: pointer; font-size: 0.85rem; font-weight: 500; }
        .sync-btn { background: var(--accent-cyan); border: none; padding: 8px 14px; font-weight: 700; border-radius: 6px; cursor: pointer; font-size: 0.8rem; margin-left: 8px; color: #000; text-transform: uppercase; }

        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
        .input-wrapper { display: flex; flex-direction: column; gap: 8px; }
        .input-wrapper label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--text-secondary); }
        .add-form input { background: var(--bg-card); border: 1px solid #2e3245; color: white; padding: 14px; border-radius: 8px; width: 100%; outline: none; }
        .upload-submit-btn { background: linear-gradient(90deg, var(--accent-cyan), #00a3cc); border: none; padding: 15px 30px; font-weight: 800; cursor: pointer; border-radius: 8px; width: 100%; color: #000; text-transform: uppercase; }
        
        .inbox-reply-form { display: flex; flex-direction: column; gap: 8px; width: 100%; max-width: 250px; }
        .inbox-reply-form textarea { background: var(--bg-card); color: white; border: 1px solid #2e3245; border-radius: 6px; padding: 8px 12px; outline: none; font-size: 0.8rem; resize: none; }
        .inbox-dispatch-btn { background: var(--accent-emerald); border: none; padding: 6px; font-size: 0.75rem; font-weight: bold; border-radius: 4px; cursor: pointer; color: white; text-transform: uppercase; transition: 0.3s; }

        .product-img { width: 44px; height: 44px; object-fit: contain; background: #0b0c10; border-radius: 6px; border: 1px solid var(--border-muted); }
        .msg-text { max-width: 250px; word-wrap: break-word; color: var(--text-secondary); line-height: 1.5; font-size: 0.85rem; }
        .control-link { font-size: 1.1rem; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
        .control-link:hover { transform: scale(1.1); }

        .confirm-popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(9, 10, 16, 0.9); backdrop-filter: blur(8px); display: none; justify-content: center; align-items: center; z-index: 9999; }
        .confirm-popup-box { background: var(--bg-surface); padding: 35px; border-radius: 14px; border: 1px solid var(--accent-rose); width: 90%; max-width: 400px; text-align: center; box-shadow: 0 15px 40px rgba(244, 63, 148, 0.15); transform: scale(0.7); opacity: 0; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .confirm-popup-overlay.show-popup .confirm-popup-box { transform: scale(1); opacity: 1; }
        .popup-danger-icon { font-size: 3.5rem; color: var(--accent-rose); margin-bottom: 15px; }
        .popup-action-row { display: flex; gap: 15px; justify-content: center; }
        .popup-btn { padding: 12px 24px; border: none; border-radius: 6px; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; cursor: pointer; letter-spacing: 0.5px; transition: 0.2s; }
        .popup-btn-confirm { background: var(--accent-rose); color: #fff; }
        .popup-btn-cancel { background: #2e3245; color: var(--text-primary); }

        .dispatch-success-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(9, 10, 16, 0.92); backdrop-filter: blur(10px); display: none; justify-content: center; align-items: center; z-index: 10000; }
        .dispatch-success-box { background: var(--bg-surface); padding: 40px; border-radius: 16px; border: 1px solid var(--accent-emerald); text-align: center; max-width: 360px; width: 90%; box-shadow: 0 20px 45px rgba(16, 185, 129, 0.2); transform: scale(0.6); opacity: 0; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .dispatch-success-overlay.active .dispatch-success-box { transform: scale(1); opacity: 1; }
        .dispatch-success-icon { font-size: 4.5rem; color: var(--accent-emerald); margin-bottom: 20px; }
        .dispatch-success-box h4 { font-size: 1.4rem; color: white; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 8px; }
        .dispatch-success-box p { color: var(--text-secondary); font-size: 0.9rem; line-height: 1.5; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <a href="#" class="sidebar-brand">STEP<span>UP.</span></a>
        <ul class="sidebar-menu">
            <li><div class="sidebar-link active" id="tab-dashboard" onclick="switchTab('dashboard', this)"><i class="fa-solid fa-chart-pie"></i> Overview</div></li>
            <li><div class="sidebar-link" id="tab-orders-panel" onclick="switchTab('orders-panel', this)"><i class="fa-solid fa-cart-shopping"></i> Manage Orders</div></li>
            <li><div class="sidebar-link" id="tab-finance-panel" onclick="switchTab('finance-panel', this)"><i class="fa-solid fa-file-invoice-dollar"></i> Payment Ledger</div></li>
            <li><div class="sidebar-link" id="tab-users-directory-panel" onclick="switchTab('users-directory-panel', this)"><i class="fa-solid fa-users-gear"></i> Manage Users</div></li>
            <li><div class="sidebar-link" id="tab-inventory-panel" onclick="switchTab('inventory-panel', this)"><i class="fa-solid fa-boxes-stacked"></i> Inventory Set</div></li>
            <li><div class="sidebar-link" id="tab-messages-panel" onclick="switchTab('messages-panel', this)"><i class="fa-solid fa-envelope-open-text"></i> Messages Inbox</div></li>
            <li><a href="admin_chat_panel.php" class="sidebar-link" style="color: var(--accent-cyan); border: 1px dashed rgba(0, 210, 255, 0.2);"><i class="fa-solid fa-robot"></i> AI Terminal Bot</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Clear Session</a>
        </div>
    </nav>

    <main class="main-content">
        <header class="top-header">
            <div>
                <h1>Core Administration Base</h1>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 4px;">Operational Matrix Node Interface</p>
            </div>
            <div class="system-date">
                <i class="fa-solid fa-circle" style="color: var(--accent-emerald); font-size: 0.5rem; margin-right: 5px;"></i> Server Logs Status: Secure
            </div>
        </header>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert-msg">
                <i class="fa-solid fa-circle-check"></i> 
                <?php 
                    if($_GET['msg'] == 'added') echo "Product asset configurations successfully deployed to system catalog.";
                    if($_GET['msg'] == 'deleted') echo "Asset deletion confirmed. Item completely purged from catalog logs.";
                    if($_GET['msg'] == 'status_updated') echo "Target transaction parameters successfully synchronized inside registries.";
                    if($_GET['msg'] == 'order_deleted') echo "Customer order data archived safely and purged from active registries.";
                    if($_GET['msg'] == 'msg_deleted') echo "Support inbox token successfully dropped from storage logs.";
                    if($_GET['msg'] == 'user_purged') echo "User registration registry successfully purged from security database.";
                ?>
            </div>
        <?php endif; ?>

        <div id="dashboard" class="tab-panel active-panel">
            <div class="analytics-grid">
                <div class="stat-card">
                    <div class="stat-details">
                        <p>Gross Earnings</p>
                        <h2>₹<?php echo number_format($total_revenue); ?></h2>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-details">
                        <p>Pending Pipelines</p>
                        <h2><?php echo $pending_count; ?></h2>
                    </div>
                    <div class="stat-icon" style="color: var(--accent-amber);"><i class="fa-solid fa-bell"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-details">
                        <p>Catalog Density</p>
                        <h2><?php echo $inventory_count; ?></h2>
                    </div>
                    <div class="stat-icon" style="color: var(--accent-emerald);"><i class="fa-solid fa-boxes-stacked"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-details">
                        <p>Registered Operators</p>
                        <h2><?php echo $total_registered_users; ?></h2>
                    </div>
                    <div class="stat-icon" style="color: var(--accent-blue); background:rgba(0,210,255,0.03); border-color:rgba(0,210,255,0.08);"><i class="fa-solid fa-users"></i></div>
                </div>
            </div>
            
            <div class="panel-section" style="margin-top: 30px;">
                <h3><i class="fa-solid fa-chart-line"></i> Sales Performance Matrix</h3>
                <div style="width: 100%; height: 320px;">
                    <canvas id="salesTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <div id="orders-panel" class="tab-panel">
            <section class="panel-section">
                <h3><i class="fa-solid fa-receipt"></i> Active Transaction Pipelines</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>User Email</th>
                                <th>Product Profile</th>
                                <th>Price Log</th>
                                <th>Tracking Status</th>
                                <th>Update Control</th>
                                <th style="text-align: center;">Purge Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orders = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");
                            if ($orders->num_rows > 0) {
                                while($o = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight: 500; color: #fff;"><?php echo htmlspecialchars($o['user_email']); ?></td>
                                    <td style="color: var(--text-secondary);"><?php echo htmlspecialchars($o['product_name']); ?></td>
                                    <td style="font-weight: 600; color: var(--accent-cyan);"><?php echo htmlspecialchars($o['price']); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            if($o['status']=='Pending') echo 'badge-pending';
                                            elseif($o['status']=='Accepted') echo 'badge-accepted';
                                            elseif($o['status']=='Dispatched') echo 'badge-dispatched';
                                            elseif($o['status']=='Delivered') echo 'badge-delivered';
                                            else echo 'badge-cancelled';
                                        ?>">
                                            <i class="fa-solid fa-circle" style="font-size: 0.45rem; margin-right: 2px;"></i>
                                            <?php echo $o['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: flex; align-items: center;">
                                            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                            <select name="order_status" class="status-select">
                                                <option value="Pending" <?php if($o['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                                <option value="Accepted" <?php if($o['status']=='Accepted') echo 'selected'; ?>>Accepted</option>
                                                <option value="Dispatched" <?php if($o['status']=='Dispatched') echo 'selected'; ?>>Dispatched</option>
                                                <option value="Delivered" <?php if($o['status']=='Delivered') echo 'selected'; ?>>Delivered</option>
                                                <option value="Cancelled" <?php if($o['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="sync-btn">Sync</button>
                                        </form>
                                    </td>
                                    <td style="text-align: center;">
                                        <a onclick="triggerDeletePopup(<?php echo $o['id']; ?>, 'order')" class="control-link" style="color: var(--accent-rose);" title="Purge Order">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile;
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center; color: var(--text-secondary); padding: 30px;'>No consumer records logged.</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div id="finance-panel" class="tab-panel">
            <section class="panel-section" id="printFinancialTargetBlock">
                <h3><i class="fa-solid fa-file-invoice-dollar"></i> Global Payment Audit & Transaction Records Ledger</h3>
                
                <div class="filter-wrapper-row" data-html2canvas-ignore="true">
                    <div style="display:flex; gap:10px; flex:1; align-items:center;">
                        <i class="fa-solid fa-filter" style="color:var(--accent-cyan);"></i>
                        <input type="text" id="userEmailFilterInput" class="filter-input" onkeyup="executeUserWiseLedgerFilter()" placeholder="Search User Email (e.g. guest@gmail.com)...">
                        <button class="filter-action-btn" onclick="clearUserFilter()">Reset</button>
                    </div>
                    <button class="pdf-download-btn" onclick="generateSystemFinancePdf()"><i class="fa-solid fa-file-pdf"></i> Download PDF Report</button>
                </div>
                
                <div class="table-responsive" style="margin-top: 20px;">
                    <table id="financeLedgerTable">
                        <thead>
                            <tr>
                                <th>Order Ref ID</th>
                                <th>User Account Vector</th>
                                <th>Price Indices</th>
                                <th>Payment Method</th>
                                <th>UPI / Transaction Hash ID</th>
                                <th>Verification Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $finances = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");
                            if ($finances->num_rows > 0) {
                                while($f = $finances->fetch_assoc()): 
                                    $method_badge = (isset($f['payment_method']) && strtolower($f['payment_method']) == 'online') ? 'strategy-badge-online' : 'strategy-badge-cod';
                                    $method_text = (isset($f['payment_method']) && strtolower($f['payment_method']) == 'online') ? 'Digital Net Banking / UPI' : 'Cash on Delivery (COD)';
                                    $txn_hash = (!empty($f['transaction_id'])) ? htmlspecialchars($f['transaction_id']) : '<span style="color:#555; font-style:italic;">Null Reference</span>';
                                    $pay_status = (!empty($f['payment_status'])) ? htmlspecialchars($f['payment_status']) : 'Pending';
                                    ?>
                                <tr class="ledger-data-row">
                                    <td style="font-weight: 700; color: #fff;">#STP-<?php echo $f['id']; ?></td>
                                    <td class="target-user-email-cell"><?php echo htmlspecialchars($f['user_email']); ?></td>
                                    <td style="font-weight: 600; color: var(--accent-cyan);"><?php echo htmlspecialchars($f['price']); ?></td>
                                    <td><span class="<?php echo $method_badge; ?>"><?php echo $method_text; ?></span></td>
                                    <td style="font-family: monospace; font-size:0.85rem;"><?php echo $txn_hash; ?></td>
                                    <td>
                                        <form method="POST" style="display:flex; align-items:center;">
                                            <input type="hidden" name="order_id" value="<?php echo $f['id']; ?>">
                                            <select name="payment_status_val" class="status-select" style="padding: 6px; font-size:0.8rem; border-color:#444;">
                                                <option value="Pending" <?php if($pay_status == 'Pending') echo 'selected'; ?>>Pending</option>
                                                <option value="Paid (Verified)" <?php if($pay_status == 'Paid (Verified)') echo 'selected'; ?>>Paid (Verified)</option>
                                                <option value="Failed / Bounced" <?php if($pay_status == 'Failed / Bounced') echo 'selected'; ?>>Failed / Bounced</option>
                                                <option value="Refunded" <?php if($pay_status == 'Refunded') echo 'selected'; ?>>Refunded</option>
                                            </select>
                                            <button type="submit" name="update_payment_status" class="sync-btn" style="padding:6px 10px; font-size:0.75rem; background: var(--accent-emerald);">Update</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile;
                            } ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div id="users-directory-panel" class="tab-panel">
            <section class="panel-section">
                <h3><i class="fa-solid fa-users-gear"></i> Registered Users Authentication Accounts Base</h3>
                
                <div class="filter-wrapper-row">
                    <div style="display:flex; gap:10px; flex:1; align-items:center;">
                        <i class="fa-solid fa-magnifying-glass" style="color:var(--accent-blue);"></i>
                        <input type="text" id="userDirectorySearchInput" class="filter-input" onkeyup="executeUserDirectoryLiveSearch()" placeholder="Search Operator Name or Email Target Vector...">
                        <button class="filter-action-btn" style="background:var(--bg-card); color:var(--text-primary); border:1px solid #333;" onclick="clearUserDirectorySearch()">Reset</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="usersDirectoryTable">
                        <thead>
                            <tr>
                                <th>Registry ID</th>
                                <th>Operator Name</th>
                                <th>Email Identity Vector</th>
                                <th style="text-align: center;">Account Purge Log</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $user_logs = $conn->query("SELECT * FROM users ORDER BY id DESC");
                            if($user_logs && $user_logs->num_rows > 0) {
                                while($u = $user_logs->fetch_assoc()): ?>
                                <tr class="user-directory-row">
                                    <td style="font-weight: 700; color: var(--accent-blue);">#USR-<?php echo $u['id']; ?></td>
                                    
                                    <td class="searchable-user-name" style="font-weight: 600; color:#fff;"><?php echo htmlspecialchars($u['name'] ?? $u['username'] ?? 'Unknown Operator'); ?></td>
                                    <td class="searchable-user-email" style="color: var(--text-secondary);"><?php echo htmlspecialchars($u['email'] ?? $u['user_email'] ?? 'Unknown Email'); ?></td>
                                    
                                    <td style="text-align: center;">
                                        <a onclick="triggerDeletePopup(<?php echo $u['id']; ?>, 'user')" class="control-link" style="color: var(--accent-rose);" title="Purge Account Profile">
                                            <i class="fa-solid fa-user-minus"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile;
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center; color: var(--text-secondary); padding: 30px;'>No registered operators verified in database registries.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div id="inventory-panel" class="tab-panel">
            <section class="panel-section add-form">
                <h3><i class="fa-solid fa-square-plus"></i> Deploy New Product Asset</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="input-wrapper">
                            <label>Manufacturer Brand</label>
                            <input type="text" name="brand" placeholder="e.g. Nike, Adidas, Puma" required>
                        </div>
                        <div class="input-wrapper">
                            <label>Model Edition Name</label>
                            <input type="text" name="title" placeholder="e.g. Air Max Limited" required>
                        </div>
                        <div class="input-wrapper">
                            <label>Asset Price Index</label>
                            <input type="text" name="price" placeholder="e.g. ₹4999" required>
                        </div>
                        <div class="input-wrapper">
                            <label>Image Matrix Source</label>
                            <input type="file" name="image_file" accept="image/*" class="file-upload-input" required>
                        </div>
                    </div>
                    <button type="submit" name="add_product" class="upload-submit-btn">Execute Asset Deployment</button>
                </form>
            </section>

            <section class="panel-section">
                <h3><i class="fa-solid fa-warehouse"></i> Catalog Inventory Sync Log</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Preview Asset</th>
                                <th>Manufacturer</th>
                                <th>Model Identifier</th>
                                <th>Price Target</th>
                                <th style="text-align: center;">Control Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $products = $conn->query("SELECT * FROM products ORDER BY p_id DESC");
                            if ($products->num_rows > 0) {
                                while($p = $products->fetch_assoc()): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($p['image_path']); ?>" class="product-img" alt="Shoe Matrix"></td>
                                    <td style="font-weight: 600; color: #fff; text-transform: capitalize;"><?php echo htmlspecialchars($p['brand_name']); ?></td>
                                    <td style="color: var(--text-secondary);"><?php echo htmlspecialchars($p['product_title']); ?></td>
                                    <td style="font-weight: 600; color: var(--accent-cyan);"><?php echo htmlspecialchars($p['product_price']); ?></td>
                                    <td style="text-align: center;">
                                        <a href="edit_product.php?id=<?php echo $p['p_id']; ?>" class="control-link" style="color: var(--accent-amber); margin-right:20px;" title="Edit Asset"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="?delete_id=<?php echo $p['p_id']; ?>" class="control-link" style="color: var(--accent-rose);" title="Purge Record" onclick="return confirm('Execute permanent purge operation on this asset record?')"><i class="fa-solid fa-trash-can"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile;
                            } ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div id="messages-panel" class="tab-panel">
            <section class="panel-section">
                <h3><i class="fa-solid fa-envelope-open-text"></i> Support Concierge Messages</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>User Name</th>
                                <th>Email Vector</th>
                                <th>Query Matrix</th>
                                <th>Current DB Saved Response</th> 
                                <th>Response Action Form</th>
                                <th style="text-align: center;">Purge</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $messages = $conn->query("SELECT * FROM messages ORDER BY submitted_at DESC");
                            if ($messages->num_rows > 0) {
                                while($m = $messages->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($m['name']); ?></td>
                                    <td style="color: var(--accent-cyan);"><?php echo htmlspecialchars($m['email']); ?></td>
                                    <td class="msg-text"><?php echo htmlspecialchars($m['message']); ?></td>
                                    <td class="msg-text response-log-cell" style="color: var(--accent-emerald); font-weight: 500;">
                                        <?php echo !empty($m['admin_reply']) ? htmlspecialchars($m['admin_reply']) : '<i style="color:var(--text-secondary); font-size:0.8rem;">No response logged yet</i>'; ?>
                                    </td>
                                    <td>
                                        <form class="inbox-reply-form" onsubmit="executeAjaxReplyDispatch(event, this)">
                                            <input type="hidden" name="target_msg_id" value="<?php echo $m['id']; ?>">
                                            <textarea name="admin_reply_content" rows="2" placeholder="Write response packet..." required><?php echo !empty($m['admin_reply']) ? htmlspecialchars($m['admin_reply']) : ''; ?></textarea>
                                            <button type="submit" class="inbox-dispatch-btn">Dispatch</button>
                                        </form>
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 500;"><?php echo $m['submitted_at']; ?></td>
                                    <td style="text-align: center;">
                                        <a onclick="triggerDeletePopup(<?php echo $m['id']; ?>, 'message')" class="control-link" style="color: var(--accent-rose);" title="Purge Query">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile;
                            } ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <div class="confirm-popup-overlay" id="customDeletePopup">
        <div class="confirm-popup-box">
            <div class="popup-danger-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <h4 id="popupTitleText">PURGE DATA LOG RECORD?</h4>
            <p id="popupBodyText">Are you sure you want to remove this data?</p>
            <div class="popup-action-row">
                <button class="popup-btn popup-btn-cancel" onclick="closeDeletePopup()">Abort</button>
                <button class="popup-btn popup-btn-confirm" id="confirmPurgeBtn">Confirm Purge</button>
            </div>
        </div>
    </div>

    <div class="dispatch-success-overlay" id="replySuccessPopup">
        <div class="dispatch-success-box">
            <div class="dispatch-success-icon"><i class="fa-solid fa-circle-check"></i></div>
            <h4>RESPONSE TRANSMITTED</h4>
            <p>The feedback token data has been processed and saved securely inside the global database registries.</p>
        </div>
    </div>

    <script>
        // --- CHART.JS GRAPHICAL ENGINE RENDER SCRIPT ---
        document.addEventListener("DOMContentLoaded", function() {
            const chartCanvas = document.getElementById('salesTrendsChart');
            if (chartCanvas) {
                const ctx = chartCanvas.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Pending', 'Accepted', 'Dispatched', 'Delivered'],
                        datasets: [{
                            label: 'Order Pipelines Statistics',
                            data: [
                                <?php echo (int)$pending_count; ?>, 
                                <?php echo (int)$accepted_count; ?>, 
                                <?php echo (int)$dispatched_count; ?>, 
                                <?php echo (int)$delivered_count; ?>
                            ],
                            backgroundColor: [
                                'rgba(245, 158, 11, 0.2)',  // Amber
                                'rgba(16, 185, 129, 0.2)', // Emerald
                                'rgba(0, 210, 255, 0.2)',   // Cyan
                                'rgba(255, 255, 255, 0.1)'  // Muted White
                            ],
                            borderColor: [
                                '#f59e0b',
                                '#10b981',
                                '#00d2ff',
                                '#ffffff'
                            ],
                            borderWidth: 2,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { color: '#9ca3af', font: { family: 'Inter' } } }
                        },
                        scales: {
                            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af', stepSize: 1 } },
                            x: { grid: { display: false }, ticks: { color: '#9ca3af' } }
                        }
                    }
                });
            }
        });

        let targetedId = null;
        let purgeType = '';

        function executeUserDirectoryLiveSearch() {
            const searchKeyword = document.getElementById('userDirectorySearchInput').value.toLowerCase().trim();
            const userRows = document.querySelectorAll('.user-directory-row');

            userRows.forEach(row => {
                const nameString = row.querySelector('.searchable-user-name').innerText.toLowerCase();
                const emailString = row.querySelector('.searchable-user-email').innerText.toLowerCase();
                if(nameString.includes(searchKeyword) || emailString.includes(searchKeyword)) {
                    row.style.display = ""; 
                } else {
                    row.style.display = "none"; 
                }
            });
        }

        function clearUserDirectorySearch() {
            document.getElementById('userDirectorySearchInput').value = "";
            executeUserDirectoryLiveSearch();
        }

        function executeUserWiseLedgerFilter() {
            const inputField = document.getElementById('userEmailFilterInput');
            const searchKeyword = inputField.value.toLowerCase().trim();
            const dataRows = document.querySelectorAll('.ledger-data-row');
            dataRows.forEach(row => {
                const userEmailCellText = row.querySelector('.target-user-email-cell').innerText.toLowerCase();
                if (userEmailCellText.includes(searchKeyword)) { row.style.display = ""; } else { row.style.display = "none"; }
            });
        }
        function clearUserFilter() { document.getElementById('userEmailFilterInput').value = ""; executeUserWiseLedgerFilter(); }

        function generateSystemFinancePdf() {
            const targetElement = document.getElementById('printFinancialTargetBlock');
            const filterInputVal = document.getElementById('userEmailFilterInput').value.trim();
            let filenameString = 'StepUp_Global_Financial_Ledger_2026.pdf';
            if(filterInputVal !== "") { filenameString = `StepUp_Ledger_Audit_[${filterInputVal}].pdf`; }
            const configurations = { margin: 12, filename: filenameString, image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2, logging: false, useCORS: true, backgroundColor: '#11131c' }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' } };
            html2pdf().from(targetElement).set(configurations).save();
        }

        async function executeAjaxReplyDispatch(event, formElement) {
            event.preventDefault();
            const submitBtn = formElement.querySelector('.inbox-dispatch-btn');
            const textareaVal = formElement.querySelector('textarea').value;
            const rowTargetCell = formElement.closest('tr').querySelector('.response-log-cell');
            submitBtn.disabled = true; submitBtn.innerText = "Processing...";
            const formData = new FormData(formElement); formData.append('ajax_dispatch_reply', '1');
            try {
                const response = await fetch('admin_dashboard.php', { method: 'POST', body: formData });
                const resText = await response.text();
                if (resText.trim() === "success") {
                    rowTargetCell.innerText = textareaVal; rowTargetCell.style.color = "var(--accent-emerald)";
                    const overlay = document.getElementById('replySuccessPopup'); overlay.style.display = 'flex';
                    setTimeout(() => { overlay.classList.add('active'); }, 50);
                    setTimeout(() => { overlay.classList.remove('active'); setTimeout(() => { overlay.style.display = 'none'; }, 400); }, 2300);
                } else { alert("Error processing logic: " + resText); }
            } catch (err) { alert("Connection failed! Check XAMPP status."); } finally { submitBtn.disabled = false; submitBtn.innerText = "Dispatch"; }
        }

        function triggerDeletePopup(id, type) {
            targetedId = id;
            purgeType = type;
            const overlay = document.getElementById('customDeletePopup');
            const pTitle = document.getElementById('popupTitleText');
            const pBody = document.getElementById('popupBodyText');
            
            if(type === 'message') {
                pTitle.innerText = "PURGE SUPPORT INBOX MSG?";
                pBody.innerText = "Are you sure you want to delete this customer inquiry? This configuration will be permanently wiped from inbox registries.";
            } else if(type === 'user') {
                pTitle.innerText = "TERMINATE USER ACCOUNT?";
                pBody.innerText = "Warning! This operation will completely revoke security credentials for this operator and purge their entry tokens from the registration base permanently.";
                overlay.querySelector('.confirm-popup-box').style.borderColor = "var(--accent-rose)";
            } else {
                pTitle.innerText = "PURGE ORDER RECORD?";
                pBody.innerText = "Are you sure you want to remove this data? The record will be dropped from the active queue and migrated safely to the Trash Archive for 24 hours.";
            }
            overlay.style.display = 'flex';
            setTimeout(() => { overlay.classList.add('show-popup'); }, 50);
        }

        function closeDeletePopup() {
            const overlay = document.getElementById('customDeletePopup');
            overlay.classList.remove('show-popup');
            setTimeout(() => { overlay.style.display = 'none'; targetedId = null; purgeType = ''; }, 300);
        }

        document.getElementById('confirmPurgeBtn').addEventListener('click', () => {
            if(targetedId !== null) {
                if(purgeType === 'message') { window.location.href = `?delete_msg_id=${targetedId}`; } 
                else if(purgeType === 'order') { window.location.href = `?delete_order_id=${targetedId}`; }
                else if(purgeType === 'user') { window.location.href = `?purge_user_id=${targetedId}`; }
            }
        });

        function switchTab(tabId, element) {
            document.querySelectorAll('.sidebar-link').forEach(link => { link.classList.remove('active'); });
            element.classList.add('active');
            document.querySelectorAll('.tab-panel').forEach(panel => { panel.classList.remove('active-panel'); });
            document.getElementById(tabId).classList.add('active-panel');
            window.location.hash = tabId;
        }

        window.addEventListener('DOMContentLoaded', () => {
            const currentHash = window.location.hash.replace('#', '');
            if(currentHash) {
                const targetLink = document.getElementById('tab-' + currentHash);
                if(targetLink) { targetLink.click(); }
            }
        });
    </script>
</body>
</html>