<?php
session_start();
require_once 'db.php';

// Authorization lock matrix check
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["reply" => "Unauthorized access request detected. Command terminated."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bot_msg'])) {
    $user_input = strtolower(trim($_POST['bot_msg']));
    $reply = "";

    // --- CASE 1: REVENUE INDEX LOGS QUERY ---
    if (strpos($user_input, 'revenue') !== false || strpos($user_input, 'earning') !== false || strpos($user_input, 'paisa') !== false || strpos($user_input, 'income') !== false) {
        $earning_res = $conn->query("SELECT price FROM orders WHERE status IN ('Accepted', 'Delivered', 'Dispatched')");
        $total_revenue = 0;
        while($e_row = $earning_res->fetch_assoc()) {
            $clean_price = (int)preg_replace('/[^0-9]/', '', $e_row['price']);
            $total_revenue += $clean_price;
        }
        $reply = "📊 **Financial Matrix Node:** Global StepUp network nodes par total clean validated collection index **₹" . number_format($total_revenue) . "** tracked kiya gaya hai.";
    }
    
    // --- CASE 2: PENDING ORDERS COUNT PIPELINE ---
    elseif (strpos($user_input, 'pending') !== false || strpos($user_input, 'order') !== false || strpos($user_input, 'request') !== false) {
        $pending_count_res = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status='Pending'");
        $pending_count = $pending_count_res->fetch_assoc()['total'];
        $reply = "🔔 **Order Log Core:** Right now, customer pipeline registries me total **" . $pending_count . " active buy requests** awaiting authorization par pending hain.";
    }
    
    // --- CASE 3: INVENTORY TOTAL PRODUCT ASSETS ---
    elseif (strpos($user_input, 'product') !== false || strpos($user_input, 'inventory') !== false || strpos($user_input, 'stock') !== false || strpos($user_input, 'shoe') !== false) {
        $inventory_count_res = $conn->query("SELECT COUNT(*) as total FROM products");
        $inventory_count = $inventory_count_res->fetch_assoc()['total'];
        $reply = "👟 **Catalog Layer Density:** Inventory framework database me abhi total **" . $inventory_count . " physical shoe models** active matrix par deployed hain.";
    }
    
    // --- CASE 4: UNRESPONDED INBOX CHECKS ---
    elseif (strpos($user_input, 'message') !== false || strpos($user_input, 'mail') !== false || strpos($user_input, 'feedback') !== false || strpos($user_input, 'query') !== false) {
        $msg_count_res = $conn->query("SELECT COUNT(*) as total FROM messages WHERE admin_reply IS NULL OR admin_reply = ''");
        $msg_count = $msg_count_res->fetch_assoc()['total'];
        $reply = "📩 **Concierge Interface Alert:** Support system registries me abhi tak **" . $msg_count . " customer tickets** unanswered pending status par hain.";
    }
    
    // --- CASE 5: RECYCLE BIN ARCHIVE LIFE TRACKER ---
    elseif (strpos($user_input, 'trash') !== false || strpos($user_input, 'delete') !== false || strpos($user_input, 'recycle') !== false) {
        $trash_count_res = $conn->query("SELECT COUNT(*) as total FROM deleted_orders");
        $trash_count = $trash_count_res->fetch_assoc()['total'];
        $reply = "♻️ **Data Archiving Logs:** Safely intercepted systems metrics ke hisab se trash bucket me total **" . $trash_count . " items** archived hain jo 24-hour cycle hote hi system se auto-purge ho jayenge.";
    }
    
    // --- DEFAULT SYSTEM HEURISTICS RESPONSES ---
    else {
        $reply = "👋 **Greetings Operator!** Main aapka personal *StepUp Virtual Administrative Concierge AI Bot* hoon. Aap mujhse niche diye huye commands directly trace kar sakte hain:\n\n" . 
                 "• *Earning/Revenue:* Live collection index calculations ke liye.\n" .
                 "• *Pending Orders:* Pipeline requests check karne ke liye.\n" .
                 "• *Shoe Inventory:* Registered dynamic database sizes janne ke liye.\n" .
                 "• *Messages Inbox:* Inbound customer queries filter karne ke liye.";
    }

    echo json_encode(["reply" => $reply]);
    exit();
}
?>