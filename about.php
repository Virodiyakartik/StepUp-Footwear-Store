<?php
session_start();
// Check user authentication status locally
$is_user_logged = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$initial_symbol = "";

if($is_user_logged) {
    // String split extraction logic to find the first character for profile avatar ring
    $initial_symbol = strtoupper(substr($_SESSION['user_name'], 0, 1));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StepUp | The Midnight Philosophy</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <style>
    /* Theme Variables matching StepUp System */
    :root {
      --bg-dark: #0f1113;
      --card-bg: #1a1d21;
      --accent-blue: #00d2ff;
      --text-main: #e0e0e0;
      --text-dim: #a0a0a0;
      --accent-red: #ff3333;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
      scroll-behavior: smooth;
    }

    body {
      background-color: var(--bg-dark);
      color: var(--text-main);
      overflow-x: hidden;
    }

    @keyframes reveal {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Navigation Panel CSS Structure */
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

    .logo {
      font-size: 1.5rem;
      font-weight: 800;
      letter-spacing: 2px;
      color: #fff;
    }
    .logo span { color: var(--accent-blue); }

    nav ul { display: flex; list-style: none; align-items: center; }
    nav ul li { margin-left: 30px; }
    nav ul li a {
      color: var(--text-dim);
      text-decoration: none;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      transition: 0.3s;
    }
    nav ul li a:hover { color: var(--accent-blue); }

    .login-btn {
      border: 1px solid var(--accent-blue);
      padding: 10px 25px;
      border-radius: 4px;
      color: var(--accent-blue) !important;
      transition: 0.3s;
    }
    .login-btn:hover {
      background: var(--accent-blue);
      color: #000 !important;
    }

    /* USER INITIALS SYSTEM STYLING MESH */
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

    /* ANIMATED LOGOUT CONTROL CONTAINER BUTTON */
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
    }

    /* About Section Grid Blocks Layout */
    .about-hero {
      padding: 100px 8% 60px;
      text-align: center;
      animation: reveal 1s ease-out;
    }
    .about-hero h1 { font-size: 4rem; font-weight: 800; margin-bottom: 20px; }
    .about-hero h1 span { color: var(--accent-blue); }
    .about-hero p { color: var(--text-dim); max-width: 700px; margin: 0 auto; font-size: 1.1rem; line-height: 1.8; }

    .philosophy-showcase {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 40px;
      padding: 60px 8% 100px;
    }
    .ph-card {
      background: var(--card-bg);
      padding: 40px;
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,0.02);
      transition: 0.4s;
    }
    .ph-card:hover {
      border-color: var(--accent-blue);
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 210, 255, 0.05);
    }
    .ph-card i { font-size: 2.5rem; color: var(--accent-blue); margin-bottom: 20px; }
    .ph-card h3 { font-size: 1.5rem; margin-bottom: 15px; font-weight: 600; color: #fff; }
    .ph-card p { color: var(--text-dim); font-size: 0.95rem; line-height: 1.6; }

    /* Footer structure links matching master theme */
    footer {
      background: #0a0c0e;
      padding: 60px 20px;
      border-top: 1px solid rgba(255,255,255,0.05);
      text-align: center;
    }
    .footer-logo { font-size: 1.8rem; font-weight: 800; color: #fff; margin-bottom: 25px; display: block; }
    .footer-logo span { color: var(--accent-blue); }
    .social-links { display: flex; justify-content: center; gap: 25px; margin-bottom: 30px; }
    .social-links a { color: var(--text-dim); font-size: 1.4rem; transition: 0.3s; }
    .social-links a:hover { color: var(--accent-blue); transform: translateY(-5px); }
    .copyright { color: #555; font-size: 0.85rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 25px; margin-top: 20px; }
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
        <li><a href="about.php" style="color: var(--accent-blue);">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        
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

<header class="about-hero">
    <h1>OUR <span>PHILOSOPHY.</span></h1>
    <p>StepUp was born out of a desire to break boundaries. We don't just engineer footwear architectures; we design precision equipment for your relentless daily hustle.</p>
</header>

<section class="philosophy-showcase">
    <div class="ph-card">
        <i class="fa-solid fa-microchip"></i>
        <h3>Precision Engineering</h3>
        <p>Breathable dynamic matrix materials woven together to provide weightless stabilization during active performance loops.</p>
    </div>
    <div class="ph-card">
        <i class="fa-solid fa-bolt-lightning"></i>
        <h3>Reactive Sole Nodes</h3>
        <p>Every single impact creates a reactive loop of kinetic conversion. Bounce back faster and maximize city traversal momentum.</p>
    </div>
    <div class="ph-card">
        <i class="fa-solid fa-city"></i>
        <h3>Ahmedabad DNA</h3>
        <p>Designed locally to keep pace with modern rapid urbanization grids, combining athletic acceleration metrics with clean minimalist profiles.</p>
    </div>
</section>

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

</body>
</html>