<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepUp | Member Access</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-dark: #0f1113;
            --accent-blue: #00d2ff;
        }

        body {
            background-color: var(--bg-dark);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Inter', sans-serif;
            color: #fff;
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* --- Background Shoe Animation --- */
        .bg-animation {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: 1;
            pointer-events: none;
            overflow: hidden;
        }

        .floating-shoe {
            position: absolute;
            width: 150px;
            opacity: 0.1;
            filter: grayscale(1) brightness(2);
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: translate(0, 110vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.1; }
            90% { opacity: 0.1; }
            100% { transform: translate(100px, -20vh) rotate(360deg); opacity: 0; }
        }

        /* --- Auth Container --- */
        .auth-container {
            background: rgba(26, 29, 33, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 12px;
            width: 380px;
            border: 1px solid var(--accent-blue);
            box-shadow: 0 10px 40px rgba(0, 210, 255, 0.2);
            position: relative;
            z-index: 10;
        }

        h2 { text-align: center; margin-bottom: 20px; color: var(--accent-blue); font-weight: 800; }

        input {
            width: 100%; padding: 12px; margin: 10px 0;
            background: #25292e; border: 1px solid #333;
            color: #fff; border-radius: 5px; box-sizing: border-box;
            outline: none; transition: 0.3s;
        }

        input:focus { border-color: var(--accent-blue); box-shadow: 0 0 10px rgba(0, 210, 255, 0.2); }

        button {
            width: 100%; padding: 12px; background: var(--accent-blue);
            border: none; color: #000; font-weight: bold;
            cursor: pointer; border-radius: 5px; margin-top: 10px;
            transition: 0.3s; text-transform: uppercase;
        }

        button:hover { background: #0099cc; transform: translateY(-2px); }

        .toggle-link { text-align: center; margin-top: 20px; font-size: 0.9rem; color: #a0a0a0; }
        .toggle-link span { color: var(--accent-blue); cursor: pointer; font-weight: 600; }

        /* --- Success Popup --- */
        .popup-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.9); display: none;
            justify-content: center; align-items: center; z-index: 2000;
            backdrop-filter: blur(10px);
        }

        .success-popup {
            background: #1a1d21; padding: 40px; border-radius: 20px;
            text-align: center; border: 2px solid var(--accent-blue);
            transform: scale(0.5); opacity: 0;
            transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            width: 300px;
        }

        .success-popup.show { transform: scale(1); opacity: 1; }
        .success-popup i { font-size: 5rem; color: var(--accent-blue); margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="bg-animation" id="bgAnimation"></div>

<div class="auth-container">
    <div id="loginSection">
        <h2>Member Login</h2>
        <form id="loginForm" action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login Now</button>
        </form>
        <div class="toggle-link">New here? <span onclick="toggleAuth()">Register</span></div>
    </div>

    <div id="registerSection" style="display:none;">
        <h2>Join StepUp</h2>
        <form id="registerForm" action="register.php" method="POST">
            <input type="text" name="username" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Create Account</button>
        </form>
        <div class="toggle-link">Member? <span onclick="toggleAuth()">Login</span></div>
    </div>
</div>

<div class="popup-overlay" id="popupOverlay">
    <div class="success-popup" id="successPopup">
        <i class="fa-solid fa-circle-check"></i>
        <h3 id="popTitle">Success!</h3>
        <p id="popupMessage">Redirecting...</p>
    </div>
</div>

<script>
    // --- Generate Floating Background Shoes ---
    const bg = document.getElementById('bgAnimation');
    const shoeImages = [
        'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=300&q=80',
        'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=300&q=80',
        'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=300&q=80'
    ];

    for (let i = 0; i < 10; i++) {
        const shoe = document.createElement('img');
        shoe.src = shoeImages[Math.floor(Math.random() * shoeImages.length)];
        shoe.className = 'floating-shoe';
        shoe.style.left = Math.random() * 100 + 'vw';
        shoe.style.animationDelay = Math.random() * 20 + 's';
        shoe.style.width = (Math.random() * 100 + 100) + 'px';
        bg.appendChild(shoe);
    }

    // --- Switch Form Views ---
    function toggleAuth() {
        const login = document.getElementById('loginSection');
        const register = document.getElementById('registerSection');
        login.style.display = login.style.display === 'none' ? 'block' : 'none';
        register.style.display = register.style.display === 'none' ? 'block' : 'none';
    }

    // --- Form AJAX Handling Logic ---
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const action = form.getAttribute('action');

            try {
                const response = await fetch(action, { method: 'POST', body: formData });
                const result = await response.text();

                if (result.toLowerCase().includes("successful")) {
                    showPopup(result);
                    
                    // Condition: Agar registration successful hui hai to wapas login screen par daalo
                    if(action.includes("register.php")) {
                        setTimeout(() => {
                            document.getElementById('popupOverlay').style.display = 'none';
                            document.getElementById('successPopup').classList.remove('show');
                            form.reset();
                            toggleAuth(); // Yeh functions screen ko login view me badal dega
                        }, 2200);
                    } else {
                        // Agar actual validation login successful hai, toh seedha index.php par bhej do
                        setTimeout(() => { window.location.href = 'index.php'; }, 2200);
                    }
                } else {
                    alert(result);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });

    function showPopup(message) {
        document.getElementById('popupMessage').innerText = message;
        document.getElementById('popupOverlay').style.display = 'flex';
        setTimeout(() => { document.getElementById('successPopup').classList.add('show'); }, 50);
    }
</script>

</body>
</html>