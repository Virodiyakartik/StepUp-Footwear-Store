<?php
session_start();
$is_user_logged = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$initial_symbol = "";

if($is_user_logged) {
    // Single split substring se username ka first character uppercase nikalna
    $initial_symbol = strtoupper(substr($_SESSION['user_name'], 0, 1));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StepUp | Midnight Edition</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <style>
    :root {
      --bg-dark: #0f1113;
      --card-bg: #1a1d21;
      --accent-blue: #00d2ff;
      --text-main: #e0e0e0;
      --text-dim: #a0a0a0;
      --accent-red: #ff3333;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; scroll-behavior: smooth; }
    body { background-color: var(--bg-dark); color: var(--text-main); overflow-x: hidden; }
    @keyframes reveal { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    nav {
      background: rgba(15, 17, 19, 0.9);
      backdrop-filter: blur(15px);
      padding: 20px 8%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
      border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .logo { font-size: 1.5rem; font-weight: 800; letter-spacing: 2px; color: #fff; }
    .logo span { color: var(--accent-blue); }

    nav ul { display: flex; list-style: none; align-items: center; }
    nav ul li { margin-left: 30px; }
    nav ul li a { color: var(--text-dim); text-decoration: none; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
    nav ul li a:hover { color: var(--accent-blue); }

    .login-btn {
      border: 1px solid var(--accent-blue);
      padding: 10px 25px;
      border-radius: 4px;
      color: var(--accent-blue) !important;
      transition: 0.3s;
    }
    .login-btn:hover { background: var(--accent-blue); color: #000 !important; }

    .menu-wrapper { display: flex; align-items: center; }
    
    .user-initial-avatar {
        width: 38px; height: 38px;
        background: linear-gradient(135deg, var(--accent-blue), #0099cc);
        color: #000; font-weight: 800; font-size: 1.1rem;
        border-radius: 50%; display: flex; justify-content: center; align-items: center;
        margin-left: 25px; cursor: pointer; border: 2px solid rgba(255,255,255,0.1);
        transition: 0.3s ease; box-shadow: 0 0 10px rgba(0, 210, 255, 0.2);
    }
    .user-initial-avatar:hover { transform: scale(1.08); box-shadow: 0 0 18px var(--accent-blue); }

    .logout-animated-btn {
        margin-left: 20px;
        color: var(--accent-red);
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: 1px solid rgba(255, 51, 51, 0.3);
        padding: 8px 16px;
        border-radius: 4px;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .logout-animated-btn:hover {
        color: #fff;
        background: var(--accent-red);
        border-color: var(--accent-red);
        box-shadow: 0 0 15px rgba(255, 51, 51, 0.6);
        letter-spacing: 2px;
        transform: translateY(-1px);
    }

    /* Hero Section */
    .hero {
      height: 90vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;
      background: radial-gradient(circle at center, rgba(0, 210, 255, 0.15) 0%, rgba(15, 17, 19, 1) 70%), url('https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1920&q=80');
      background-size: cover; background-position: center; background-attachment: fixed;
    }
    .hero h1 { font-size: 5rem; font-weight: 800; line-height: 1; margin-bottom: 20px; animation: reveal 1s ease-out; }
    .hero p { color: var(--text-dim); font-size: 1.1rem; max-width: 500px; animation: reveal 1s ease-out 0.2s backwards; }

    /* Grid layout styling */
    .collection { padding: 100px 8%; }
    .section-title { text-align: center; font-size: 2.5rem; margin-bottom: 60px; }
    .shoe-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; }
    .shoe-card { background: var(--card-bg); padding: 30px; border-radius: 12px; text-align: center; transition: 0.4s; border: 1px solid rgba(255,255,255,0.03); }
    .shoe-card:hover { transform: translateY(-10px); border-color: var(--accent-blue); box-shadow: 0 10px 30px rgba(0, 210, 255, 0.1); }
    .shoe-card img { width: 100%; height: 250px; object-fit: contain; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5)); transition: 0.5s; }
    .shoe-card:hover img { transform: scale(1.1) rotate(-3deg); }
    .shoe-card h3 { margin: 20px 0 10px; font-weight: 600; }
    .shoe-card .price { color: var(--accent-blue); font-weight: 800; font-size: 1.2rem; }

    .info-section { padding: 100px 15%; text-align: center; line-height: 1.8; }
    #about { background: #14171a; }
    #about p { color: var(--text-dim); max-width: 800px; margin: 0 auto; }
    footer { background: #0a0c0e; padding: 60px 20px; border-top: 1px solid rgba(255,255,255,0.05); text-align: center; }
    .footer-logo { font-size: 1.8rem; font-weight: 800; color: #fff; margin-bottom: 25px; display: block; }
    .footer-logo span { color: var(--accent-blue); }
    .social-links { display: flex; justify-content: center; gap: 25px; margin-bottom: 30px; }
    .social-links a { color: var(--text-dim); font-size: 1.4rem; transition: 0.3s; }
    .social-links a:hover { color: var(--accent-blue); transform: translateY(-5px); }
    .copyright { color: #555; font-size: 0.85rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 25px; margin-top: 20px; }

    /* ================= NEW UI STYLES: FLOATING CUSTOM CHATBOT INTERFACE ================= */
    .bot-trigger-widget {
        position: fixed; bottom: 30px; right: 30px;
        width: 60px; height: 60px;
        background: linear-gradient(135deg, var(--accent-blue), #00a3cc);
        color: #000; border-radius: 50%;
        display: flex; justify-content: center; align-items: center;
        font-size: 1.6rem; cursor: pointer; z-index: 9999;
        box-shadow: 0 8px 25px rgba(0, 210, 255, 0.4);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .bot-trigger-widget:hover { transform: scale(1.08) rotate(8deg); }

    .bot-panel-overlay {
        position: fixed; bottom: 105px; right: 30px;
        width: 360px; height: 480px;
        background: rgba(26, 29, 33, 0.96);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(0, 210, 255, 0.2);
        border-radius: 14px; display: none; flex-direction: column;
        z-index: 9998; box-shadow: 0 15px 40px rgba(0,0,0,0.6);
        transform: translateY(20px); opacity: 0;
        transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
    }
    .bot-panel-overlay.active-panel { display: flex; transform: translateY(0); opacity: 1; }

    .bot-header { background: #14161a; padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.04); display: flex; justify-content: space-between; align-items: center; border-radius: 14px 14px 0 0; }
    .bot-header h4 { font-size: 0.95rem; font-weight: 800; display: flex; align-items: center; gap: 8px; }
    .bot-header h4 span { color: var(--accent-blue); }

    .bot-chat-scroller { flex: 1; padding: 18px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; }
    .msg-bubble { max-width: 85%; padding: 10px 14px; border-radius: 10px; font-size: 0.85rem; line-height: 1.4; word-wrap: break-word; white-space: pre-line; animation: bubbleAnim 0.25s ease-out forwards; }
    @keyframes bubbleAnim { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    
    .user-bubble { background: #222533; align-self: flex-end; color: #fff; border-bottom-right-radius: 2px; }
    .system-bubble { background: #1a1d21; align-self: flex-start; border-left: 2px solid var(--accent-blue); border-bottom-left-radius: 2px; }

    .bot-input-footer { padding: 14px; border-top: 1px solid rgba(255,255,255,0.04); background: #14161a; border-radius: 0 0 14px 14px; }
    .bot-form { display: flex; gap: 10px; }
    .bot-form input { flex: 1; padding: 10px 14px; background: #0f1113; border: 1px solid #2e3245; color: white; border-radius: 6px; outline: none; font-size: 0.85rem; }
    .bot-form input:focus { border-color: var(--accent-blue); }
    .bot-submit-btn { background: var(--accent-blue); border: none; padding: 0 16px; border-radius: 6px; font-weight: 800; cursor: pointer; font-size: 0.75rem; color: #000; text-transform: uppercase; }
  </style>
</head>
<body>

<nav>
  <div class="logo">
      <a href="admin_login.php" style="text-decoration: none; color: inherit;">
          STEP<span>UP.</span>
      </a>
  </div>
  <div class="menu-wrapper">
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="shop.php">Shop</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
         <li><a href="help.php">Help</a></li>
        <?php if(!$is_user_logged): ?>
            <li><a href="login_page.php" class="login-btn">Member Access</a></li>
        <?php endif; ?>
      </ul>
      <?php if($is_user_logged): ?>
          <div class="user-initial-avatar" title="Authenticated Profile: <?php echo htmlspecialchars($_SESSION['user_name']); ?>">
              <?php echo $initial_symbol; ?>
          </div>
          <a href="user_logout.php" class="logout-animated-btn">
              <i class="fa-solid fa-right-from-bracket"></i> Logout
          </a>
      <?php endif; ?>
  </div>
</nav>

<header class="hero">
  <h1>MOVE FASTER.</h1>
  <p>The next generation of athletic performance is here. Experience weightless comfort and precision engineering.</p>
</header>

<section id="collection" class="collection">
  <h2 class="section-title">New Arrivals</h2>
  <div class="shoe-container">
    <div class="shoe-card">
      <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80" alt="Nike">
      <h3>Neon Strike</h3>
      <p class="price">$160.00</p>
    </div>
    <div class="shoe-card">
      <img src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=600&q=80" alt="Runner">
      <h3>Cyber Runner</h3>
      <p class="price">$145.00</p>
    </div>
    <div class="shoe-card">
      <img src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=600&q=80" alt="Street">
      <h3>Aura White</h3>
      <p class="price">$120.00</p>
    </div>
  </div>
</section>

<section id="about" class="info-section">
  <h2>The Philosophy</h2>
  <p>StepUp was born out of a desire to push boundaries. We don't just sell shoes; we provide the foundation for your daily hustle. Based in Ahmedabad, our designs are tested to withstand the pace of modern life while keeping you ahead of the trend.</p>
</section>

<section id="contact" class="info-section">
  <h2>Support</h2>
  <p>Available 24/7 for our community.</p>
  <p style="margin-top: 20px; color: var(--accent-blue); font-weight: bold; font-size: 1.2rem;">concierge@stepup.com</p>
</section>

<div class="bot-trigger-widget" onclick="toggleUserBotOverlay()"><i class="fa-solid fa-robot"></i></div>

<div class="bot-panel-overlay" id="userBotPanel">
    <header class="bot-header">
        <h4><i class="fa-solid fa-circle-nodes" style="color:var(--accent-blue);"></i> STEP<span>UP.</span> Assistant</h4>
        <span onclick="toggleUserBotOverlay()" style="cursor:pointer; font-size:1.1rem; color:var(--text-secondary);">&times;</span>
    </header>
    <div class="bot-chat-scroller" id="botBoxScroller">
        <div class="msg-bubble system-bubble">
            👋 Hello! Main StepUp automated concierge bot hoon. 
            
            Aap mujhse sneakers product availability, UPI payments checkout strategy, ya direct admin response tracking portals ke baare mein help pooch sakte hain!
        </div>
    </div>
    <div class="bot-input-footer">
        <form class="bot-form" id="botSubmitEventForm">
            <input type="text" id="botInputPayload" placeholder="Ask anything (e.g. Shop, Payment)..." autocomplete="off" required>
            <button type="submit" class="bot-submit-btn">Ask</button>
        </form>
    </div>
</div>

<footer>
  <span class="footer-logo">STEP<span>UP.</span></span>
  <div class="social-links">
    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
    <a href="#" aria-label="X Twitter"><i class="fa-brands fa-x-twitter"></i></a>
    <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
  </div>
  <div class="copyright">
    &copy; 2026 StepUp Footwear. All rights reserved.
  </div>
</footer>

<script>
    const scrollContainer = document.getElementById('botBoxScroller');

    function toggleUserBotOverlay() {
        const target = document.getElementById('userBotPanel');
        if(target.style.display === 'flex') {
            target.classList.remove('active-panel');
            setTimeout(() => { target.style.display = 'none'; }, 300);
        } else {
            target.style.display = 'flex';
            setTimeout(() => { target.classList.add('active-panel'); }, 30);
        }
    }

    document.getElementById('botSubmitEventForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const payloadInput = document.getElementById('botInputPayload');
        const textValue = payloadInput.value.trim();
        if(!textValue) return;

        // Append user request bubble
        createBubble(textValue, 'user');
        payloadInput.value = "";
        scrollBotLogs();

        // Placeholder load
        const tempId = createBubble("Analyzing queries parameters...", 'system');
        scrollBotLogs();

        try {
            const params = new FormData();
            params.append('user_msg', textValue);

            const response = await fetch('user_bot_core.php', { method: 'POST', body: params });
            const dataText = await response.text();
            
            document.getElementById(tempId).remove(); // Clear template loader

            const parsed = JSON.parse(dataText);
            createBubble(parsed.reply, 'system');
        } catch (err) {
            console.error("Bot node processing failure:", err);
            document.getElementById(tempId).innerText = "❌ Pipeline network delay matching node protocols.";
        } finally {
            scrollBotLogs();
        }
    });

    function createBubble(text, speaker) {
        const bubbleId = 'bub_' + Math.floor(Math.random() * 100000);
        const element = document.createElement('div');
        element.className = `msg-bubble ${speaker}-bubble`;
        element.id = bubbleId;
        element.innerText = text;
        scrollContainer.appendChild(element);
        return bubbleId;
    }

    function scrollBotLogs() { scrollContainer.scrollTop = scrollContainer.scrollHeight; }
</script>

</body>
</html>