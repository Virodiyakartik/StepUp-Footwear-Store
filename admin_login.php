<?php
session_start();
require_once 'db.php';

// Agar admin pehle se logged in hai, toh direct dashboard par bhej do
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Background AJAX validation handler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_check'])) {
    $admin_id = trim($_POST['admin_id']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
    $stmt->bind_param("s", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            echo "success"; // JavaScript ise read karke animation chalayega
            exit();
        } else { echo "Invalid Password!"; exit(); }
    } else { echo "Admin ID not found!"; exit(); }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>StepUp | Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --accent-blue: #00d2ff;
            --bg-dark: #0f1113;
            --card-bg: #1a1d21;
            --accent-red: #ff4d4d;
        }

        body { 
            background: var(--bg-dark); 
            color: white; 
            font-family: 'Inter', sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            position: relative;
            overflow: hidden;
        }

        /* --- STYLES & LOADING KEYFRAMES --- */
        @keyframes boxLoad {
            from { opacity: 0; transform: scale(0.9) translateY(30px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        .admin-box { 
            background: var(--card-bg); 
            padding: 40px; 
            border: 1px solid var(--accent-blue); 
            border-radius: 12px; 
            width: 350px; 
            text-align: center; 
            box-shadow: 0 10px 40px rgba(0, 210, 255, 0.1);
            animation: boxLoad 0.6s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            position: relative;
            z-index: 10;
        }
        
        h2 { color: var(--accent-blue); margin-bottom: 25px; font-weight: 800; letter-spacing: 2px; }
        
        input { 
            width: 100%; padding: 14px; margin: 12px 0; 
            background: #25292e; border: 1px solid #333; 
            color: white; border-radius: 6px; box-sizing: border-box; 
            outline: none; transition: all 0.3s ease;
        }
        input:focus { border-color: var(--accent-blue); box-shadow: 0 0 12px rgba(0, 210, 255, 0.3); }
        
        button { 
            width: 100%; padding: 14px; background: var(--accent-blue); 
            border: none; font-weight: 800; cursor: pointer; 
            border-radius: 6px; margin-top: 15px; color: black;
            text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
        }
        button:hover { background: #fff; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(255, 255, 255, 0.2); }
        
        .error { color: var(--accent-red); font-size: 0.85rem; background: rgba(255,77,77,0.08); padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid var(--accent-red); display: none; }
        .error.shake { display: block; animation: errorShake 0.4s ease-in-out; }

        .back-home { position: absolute; top: 30px; right: 40px; color: var(--accent-blue); text-decoration: none; font-size: 0.9rem; border: 1px solid var(--accent-blue); padding: 10px 20px; border-radius: 5px; transition: 0.3s; display: flex; align-items: center; gap: 8px; text-transform: uppercase; }
        .back-home:hover { background: var(--accent-blue); color: #000; box-shadow: 0 0 15px rgba(0, 210, 255, 0.4); }

        /* --- DYNAMIC SUCCESS POPUP ANIMATION OVERLAY --- */
        .success-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 17, 19, 0.95);
            backdrop-filter: blur(10px);
            display: none; justify-content: center; align-items: center;
            z-index: 5000;
        }
        
        .success-modal {
            background: #1a1d21; padding: 40px; border-radius: 16px;
            border: 1px solid #28a745; text-align: center;
            max-width: 320px; width: 90%;
            box-shadow: 0 20px 40px rgba(40, 167, 69, 0.2);
            transform: scale(0.6); opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .success-overlay.active .success-modal { transform: scale(1); opacity: 1; }

        .success-icon { font-size: 4.5rem; color: #28a745; margin-bottom: 20px; transform: scale(0.3); opacity: 0; }
        .success-overlay.active .success-icon {
            animation: popCheck 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s forwards;
        }

        @keyframes popCheck {
            0% { transform: scale(0.3); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .success-modal h3 { font-size: 1.5rem; letter-spacing: 1px; margin-bottom: 8px; color: #fff; font-weight: 800; }
        .success-modal p { color: #a0a0a0; font-size: 0.9rem; }
    </style>
</head>
<body>

    <a href="index.php" class="back-home"><i class="fa-solid fa-house"></i> Home</a>

    <div class="admin-box">
        <h2>ADMIN LOGIN</h2>
        
        <div class="error" id="errorBlock"></div>
        
        <form id="adminLoginForm">
            <input type="text" name="admin_id" placeholder="Admin ID" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">LOGIN TO DASHBOARD</button>
        </form>
    </div>

    <div class="success-overlay" id="successLayer">
        <div class="success-modal">
            <div class="success-icon"><i class="fa-solid fa-circle-check"></i></div>
            <h3>ACCESS GRANTED</h3>
            <p>Synchronizing admin dashboard nodes...</p>
        </div>
    </div>

<script>
document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
    e.preventDefault(); // Stop normal form submit routing
    
    const errorBlock = document.getElementById('errorBlock');
    errorBlock.classList.remove('shake'); // Clear any previous shake state
    
    const formData = new FormData(this);
    formData.append('ajax_check', '1'); // Trigger custom dynamic conditional node in PHP

    try {
        const response = await fetch('admin_login.php', { method: 'POST', body: formData });
        const resText = await response.text();

        if (resText.trim() === "success") {
            const layer = document.getElementById('successLayer');
            layer.style.display = 'flex';
            
            // Trigger zoom CSS scale-up active transition
            setTimeout(() => { layer.classList.add('active'); }, 50);
            
            // 2.2 Seconds tak loading hold rkhega, fr redirect krega dashboard pr
            setTimeout(() => { window.location.href = 'admin_dashboard.php'; }, 2200);
        } else {
            // Agar validation wrong hai, toh dynamic screen warning milegi shake sound effect ke sath
            errorBlock.innerHTML = `<i class='fa-solid fa-triangle-exclamation'></i> ${resText}`;
            errorBlock.classList.add('shake');
        }
    } catch (err) {
        console.error("Fetch Execution Error:", err);
    }
});
</script>

</body>
</html>