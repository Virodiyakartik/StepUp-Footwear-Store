<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_msg'])) {
    $user_input = strtolower(trim($_POST['user_msg']));
    $reply = "";

    // --- CASE 1: SHOPPING, CATALOG, BRANDS, AND SNEAKERS ---
    if (strpos($user_input, 'shop') !== false || strpos($user_input, 'buy') !== false || strpos($user_input, 'shoe') !== false || strpos($user_input, 'brand') !== false || strpos($user_input, 'catalog') !== false) {
        $reply = "👟 **StepUp Dynamic Catalog:**\nYou can explore our premium sneaker collection directly on the [Shop Page](shop.php).\n\n" . 
                 "• **Active Brand Modules:** Nike, Adidas, Jordan, Puma, Reebok, and Vans.\n" .
                 "• **How to Order:** Click on any sneaker card to open the **Interactive Product Modal**, select your UK shoe size, select a payment strategy, and click **Complete Checkout**.";
    } 
    
    // --- CASE 2: PAYMENT STRATEGY, TRANSACTIONS, AND UPI NO. ---
    elseif (strpos($user_input, 'payment') !== false || strpos($user_input, 'pay') !== false || strpos($user_input, 'upi') !== false || strpos($user_input, 'cod') !== false || strpos($user_input, 'transaction') !== false) {
        $reply = "💳 **Payment Gateways & Options:**\nOur checkout matrix natively supports two core transactional strategies:\n\n" .
                 "1. **Cash on Delivery (COD):** No advance processing required. Direct physical synchronization upon delivery.\n" .
                 "2. **Net Banking / UPI (Online Paid):** Select 'Net Banking / UPI' inside the product modal, input your unique **UPI Ref No. / Transaction ID**, and click checkout. The data will be safely routed to the Admin Console for immediate validation.";
    } 
    
    // --- CASE 3: DIGITAL INVOICES, RECEIPTS, AND PDF DOWNLOADS ---
    elseif (strpos($user_input, 'receipt') !== false || strpos($user_input, 'invoice') !== false || strpos($user_input, 'pdf') !== false || strpos($user_input, 'download') !== false) {
        $reply = "📄 **Dynamic Invoice Generation:**\nOur platform features an instant client-side PDF rendering system using `html2pdf.js` libraries.\n\n" .
                 "• **How to get it:** Right after you click *Complete Checkout* inside the product modal, a highly detailed custom **Store Receipt Modal** will overlay automatically.\n" .
                 "• **Download Option:** Click the **Download Receipt PDF** button inside that overlay to get a clean vector invoice directly downloaded to your local browser storage cache.";
    } 
    
    // --- CASE 4: CONTACT FORM, FEEDBACK SYSTEM, AND LOOKUP ADMIN REPLIES ---
    elseif (strpos($user_input, 'support') !== false || strpos($user_input, 'contact') !== false || strpos($user_input, 'feedback') !== false || strpos($user_input, 'message') !== false || strpos($user_input, 'reply') !== false || strpos($user_input, 'response') !== false) {
        $reply = "📩 **Communications & Support Portal:**\nIf you have any operational queries or configuration feedback, use our dedicated [Contact Page](contact.php).\n\n" .
                 "• **Submit Query:** Fill in your Name, Registered Email, and Message to dispatch a token entry directly to the Admin Dashboard inbox.\n" .
                 "• **Real-Time Reply Lookup:** Inside the [Contact Page](contact.php), click the **Check Admin Responses** button, type your registered email vector, and click *Fetch Logs*. You will instantly see your original query message stacked right next to the **Live Admin Response Matrix** saved in our database.";
    } 

    // --- CASE 5: THE PHILOSOPHY, BRAND IDENTITY, AND HEADQUARTERS ---
    elseif (strpos($user_input, 'about') !== false || strpos($user_input, 'philosophy') !== false || strpos($user_input, 'location') !== false || strpos($user_input, 'headquarters') !== false || strpos($user_input, 'ahmedabad') !== false) {
        $reply = "📍 **About StepUp & Corporate Headquarters:**\n" .
                 "• **Our Philosophy:** StepUp Midnight Edition is built around breaking minimalist boundaries, combining athletic acceleration metrics with elite urban style codes. Read our full story on the [About Page](about.php).\n" .
                 "• **Physical Hub Node:** Our main headquarters is physically anchored at **C-104, Premium Square, CG Road, Ahmedabad, GJ**.";
    }

    // --- CASE 6: MEMBER ACCESS, SIGNUP, SESSIONS, AND LOGOUT ---
    elseif (strpos($user_input, 'login') !== false || strpos($user_input, 'register') !== false || strpos($user_input, 'member') !== false || strpos($user_input, 'logout') !== false || strpos($user_input, 'session') !== false || strpos($user_input, 'account') !== false) {
        $reply = "🔐 **Member Access & Session Control:**\n" .
                 "• **Registration & Login:** Access the secure portal via the **Member Access** button on the navbar. New operators can seamlessly toggle to the registration tab to initialize a password-hashed account profile.\n" .
                 "• **Dynamic Initials Ring:** Once authenticated, the navbar automatically replaces the login button with a sleek gradient **First Letter Initial Avatar Ring** mapping your user session name.\n" .
                 "• **Logout Security:** Click the red animated **Logout Button** right next to your initials avatar to completely flush your session data arrays and securely exit.";
    }
    
    // --- DEFAULT COMPREHENSIVE HELP DESK PLATFORM DICTIONARY ---
    else {
        $reply = "🤖 **StepUp Digital Concierge Terminal:**\nI am your automated multi-module assistance bot. I can map and guide you through the entire functionality of our platform. \n\nPlease ask any explicit query or type any of these operational keywords:\n\n" . 
                 "• **Shop / Brands:** Sneaker collections, sizing grids, and checkout modal workflows.\n" .
                 "• **Payment Strategy:** Guidance on Cash on Delivery (COD) and UPI Transaction structures.\n" .
                 "• **Invoice PDF:** Dynamic generation and local receipt downloads.\n" .
                 "• **Check Reply:** How to lookup real-time admin responses to your feedback queries.\n" .
                 "• **Member Session:** Registration steps, login control, and navbar initial rings.\n" .
                 "• **Headquarters:** Core philosophy overview and Ahmedabad database branch routing.";
    }

    echo json_encode(["reply" => $reply]);
    exit();
}
?>