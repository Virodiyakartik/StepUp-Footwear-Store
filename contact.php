<?php 
require_once 'db.php'; 

$show_popup = false;
$user_name = "";

// Form submission handler
if (isset($_POST['send_msg'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    $query = "INSERT INTO messages (name, email, message) VALUES ('$name', '$email', '$message')";
    
    if ($conn->query($query)) {
        $show_popup = true;
        $user_name = $name;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepUp | Support Concierge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f1113;
            --card-bg: #1a1d21;
            --accent-blue: #00d2ff;
            --accent-emerald: #10b981;
            --text-main: #e0e0e0;
            --text-dim: #a0a0a0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-main); padding-top: 80px; overflow-x: hidden; }

        /* Navigation */
        nav { background: rgba(15, 17, 19, 0.95); backdrop-filter: blur(10px); padding: 20px 8%; display: flex; justify-content: space-between; align-items: center; position: fixed; top: 0; width: 100%; z-index: 1000; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .logo { font-size: 1.5rem; font-weight: 800; color: #fff; text-decoration: none; }
        .logo span { color: var(--accent-blue); }
        .nav-links { display: flex; list-style: none; align-items: center; }
        .nav-links li { margin-left: 30px; }
        .nav-links a { color: var(--text-dim); text-decoration: none; font-size: 0.9rem; text-transform: uppercase; }
        .nav-links a:hover { color: var(--accent-blue); }

        .contact-hero { text-align: center; padding: 50px 20px 20px; }
        .contact-hero h1 { font-size: 3rem; }
        .contact-hero span { color: var(--accent-blue); }

        /* Layout Grid */
        .contact-grid { display: grid; grid-template-columns: 1fr 1.2fr; max-width: 1100px; margin: 30px auto; padding: 0 20px; gap: 50px; }
        .contact-info { display: flex; flex-direction: column; justify-content: center; }
        .info-item { display: flex; align-items: center; gap: 20px; margin-bottom: 35px; }
        .info-item i { font-size: 1.8rem; color: var(--accent-blue); background: rgba(0, 210, 255, 0.05); padding: 15px; border-radius: 10px; border: 1px solid rgba(0, 210, 255, 0.1); }
        .info-item h4 { color: #fff; margin-bottom: 5px; }
        .info-item p { color: var(--text-dim); font-size: 0.95rem; }

        .contact-form { background: var(--card-bg); padding: 40px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.03); box-shadow: 0 15px 30px rgba(0,0,0,0.5); }
        .contact-form h3 { margin-bottom: 25px; font-weight: 600; color: #fff; }
        .form-group { margin-bottom: 20px; }
        .form-group input, .form-group textarea { width: 100%; background: #25292e; border: 1px solid #333; padding: 14px; color: white; border-radius: 8px; outline: none; transition: 0.3s; }
        .form-group input:focus, .form-group textarea:focus { border-color: var(--accent-blue); }
        
        .submit-btn { width: 100%; background: var(--accent-blue); border: none; padding: 16px; color: #000; font-weight: 800; border-radius: 8px; cursor: pointer; text-transform: uppercase; transition: 0.3s; }
        .submit-btn:hover { background: #fff; transform: translateY(-2px); }

        /* Dynamic User Response Lookup Interface Layout CSS */
        .lookup-switch-btn { background: transparent; border: 1px dashed var(--accent-blue); color: var(--accent-blue); padding: 12px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; width: 100%; cursor: pointer; text-align: center; margin-top: 15px; text-transform: uppercase; transition: 0.3s; }
        .lookup-switch-btn:hover { background: rgba(0, 210, 255, 0.03); }

        .user-lookup-frame { background: #14161a; border: 1px solid rgba(255,255,255,0.03); border-radius: 12px; padding: 30px; margin-top: 25px; display: none; }
        .user-lookup-frame h4 { color: #fff; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .user-lookup-frame h4 i { color: var(--accent-emerald); }

        .response-table { width: 100%; border-collapse: collapse; margin-top: 20px; text-align: left; }
        .response-table th { background: #1d212a; color: var(--text-dim); padding: 12px; font-size: 0.8rem; text-transform: uppercase; border-bottom: 1px solid #333; }
        .response-table td { padding: 14px 12px; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 0.85rem; vertical-align: top; }
        .txt-query { color: var(--text-dim); }
        .txt-reply { color: var(--accent-emerald); font-weight: 600; line-height: 1.4; }

        /* Popup overlay frame styles */
        .popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(8px); display: none; justify-content: center; align-items: center; z-index: 5000; }
        .popup-box { background: #1a1d21; padding: 40px; border-radius: 16px; border: 1px solid var(--accent-blue); text-align: center; max-width: 400px; width: 90%; box-shadow: 0 20px 40px rgba(0, 210, 255, 0.2); transform: scale(0.7); opacity: 0; transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .popup-overlay.active .popup-box { transform: scale(1); opacity: 1; }
        .popup-icon { font-size: 4rem; color: #28a745; margin-bottom: 20px; }

        footer { text-align: center; padding: 40px; color: #555; font-size: 0.85rem; border-top: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">STEP<span>UP.</span></a>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="shop.php">Shop</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php" style="color: var(--accent-blue);">Contact</a></li>
    </ul>
</nav>

<header class="contact-hero">
    <h1>GET IN <span>TOUCH.</span></h1>
</header>

<div class="contact-grid">
    <div class="contact-info">
        <div class="info-item">
            <i class="fa-solid fa-envelope"></i>
            <div>
                <h4>Support Registry</h4>
                <p>concierge@stepup.com</p>
            </div>
        </div>
        <div class="info-item">
            <i class="fa-solid fa-location-dot"></i>
            <div>
                <h4>Main Headquarters</h4>
                <p>C-104, Premium Square, CG Road, Ahmedabad, GJ</p>
            </div>
        </div>
        <div class="info-item">
            <i class="fa-solid fa-headset"></i>
            <div>
                <h4>Availability</h4>
                <p>24/7 Priority Member Assistance Link</p>
            </div>
        </div>
    </div>

    <div>
        <div class="contact-form">
            <h3>Submit System Query</h3>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Your Registered Email" required>
                </div>
                <div class="form-group">
                    <textarea name="message" rows="5" placeholder="Describe your request..." required></textarea>
                </div>
                <button type="submit" name="send_msg" class="submit-btn">Send Message</button>
            </form>
            
            <button class="lookup-switch-btn" onclick="toggleLookupPortal()"><i class="fa-solid fa-magnifying-glass"></i> Check Admin Responses</button>
        </div>

        <div class="user-lookup-frame" id="lookupPortalBlock">
            <h4><i class="fa-solid fa-comments"></i> Inbound Reply Matrix</h4>
            <form method="POST" action="contact.php?action=lookup#lookupPortalBlock">
                <div class="form-group" style="margin-bottom: 12px;">
                    <input type="email" name="lookup_email" placeholder="Enter email to track feedback..." value="<?php echo isset($_POST['lookup_email']) ? htmlspecialchars($_POST['lookup_email']) : ''; ?>" required>
                </div>
                <button type="submit" name="trigger_lookup" class="submit-btn" style="padding:10px; font-size:0.8rem; max-width:140px;">Fetch Logs</button>
            </form>

            <?php
            if (isset($_POST['trigger_lookup'])) {
                $search_email = mysqli_real_escape_string($conn, $_POST['lookup_email']);
                $logs = $conn->query("SELECT * FROM messages WHERE email='$search_email' ORDER BY submitted_at DESC");
                
                if($logs->num_rows > 0) {
                    echo '<table class="response-table"><thead><tr><th>Your Message</th><th>Admin Response</th></tr></thead><tbody>';
                    while($row = $logs->fetch_assoc()) {
                        $reply = !empty($row['admin_reply']) ? htmlspecialchars($row['admin_reply']) : '<i style="color:var(--text-secondary);">Awaiting operational dispatch...</i>';
                        echo '<tr>';
                        echo '<td class="txt-query">'.htmlspecialchars($row['message']).'<br><small style="color:#555;">'.$row['submitted_at'].'</small></td>';
                        echo '<td class="txt-reply">'.$reply.'</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    echo '<p style="font-size:0.85rem; color:var(--accent-rose); margin-top:20px;"><i class="fa-solid fa-circle-exclamation"></i> No indexed queries matched with this email sequence.</p>';
                }
            }
            ?>
        </div>
    </div>
</div>

<div class="popup-overlay" id="successPopup">
    <div class="box popup-box">
        <div class="popup-icon"><i class="fa-solid fa-circle-check"></i></div>
        <h2>MESSAGE SENT!</h2>
        <p>Thank you, <b style="color: var(--accent-blue);"><?php echo htmlspecialchars($user_name); ?></b>. Your query has been routed to the Admin Dashboard successfully.</p>
    </div>
</div>

<footer>&copy; 2026 StepUp Footwear | Premium Store</footer>

<script>
    function toggleLookupPortal() {
        const portal = document.getElementById('lookupPortalBlock');
        portal.style.display = (portal.style.display === 'block') ? 'none' : 'block';
    }

    // Hash tracking parameters checks on runtime initialization
    if(window.location.search.includes('action=lookup') || window.location.hash === '#lookupPortalBlock') {
        document.getElementById('lookupPortalBlock').style.display = 'block';
    }

    <?php if($show_popup): ?>
        const popup = document.getElementById('successPopup');
        popup.style.display = 'flex';
        setTimeout(() => { popup.classList.add('active'); }, 50);
        setTimeout(() => {
            popup.classList.remove('active');
            setTimeout(() => { window.location.href = 'contact.php'; }, 400);
        }, 3500);
    <?php endif; ?>
</script>
</body>
</html>