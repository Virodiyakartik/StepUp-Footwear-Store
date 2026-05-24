<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepUp Suite | AI Assistant Control Node</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #090a10;
            --bg-surface: #11131c;
            --bg-card: #181a26;
            --accent-cyan: #00d2ff;
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-base); color: var(--text-primary); display: flex; justify-content: center; align-items: center; height: 100vh; overflow: hidden; }

        .chat-container { width: 90%; max-width: 650px; height: 80vh; background: var(--bg-surface); border: 1px solid rgba(255,255,255,0.04); border-radius: 16px; display: flex; flex-direction: column; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .chat-header { background: var(--bg-card); padding: 20px 25px; border-bottom: 1px solid rgba(255,255,255,0.04); display: flex; justify-content: space-between; align-items: center; border-radius: 16px 16px 0 0; }
        .chat-header h2 { font-size: 1.15rem; font-weight: 800; display: flex; align-items: center; gap: 10px; color: #fff; }
        .chat-header h2 span { color: var(--accent-cyan); }
        .back-dashboard { color: var(--accent-cyan); text-decoration: none; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; border: 1px solid var(--accent-cyan); padding: 6px 14px; border-radius: 6px; transition: 0.3s; }
        .back-dashboard:hover { background: var(--accent-cyan); color: #000; }

        .chat-logs-box { flex: 1; padding: 25px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; background: radial-gradient(circle at top, rgba(0, 210, 255, 0.03) 0%, transparent 70%); }
        .chat-bubble { max-width: 80%; padding: 14px 18px; border-radius: 12px; font-size: 0.9rem; line-height: 1.5; white-space: pre-line; word-wrap: break-word; animation: bubbleIn 0.3s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
        @keyframes bubbleIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .bubble-admin { background: #222533; color: #fff; align-self: flex-end; border-bottom-right-radius: 2px; border: 1px solid rgba(255,255,255,0.02); }
        .bubble-bot { background: var(--bg-card); color: var(--text-primary); align-self: flex-start; border-bottom-left-radius: 2px; border-left: 3px solid var(--accent-cyan); }

        .chat-input-bar { background: var(--bg-card); padding: 20px; border-top: 1px solid rgba(255,255,255,0.04); border-radius: 0 0 16px 16px; }
        .input-form { display: flex; gap: 15px; }
        .input-form input { flex: 1; background: #090a10; color: white; border: 1px solid #2e3245; padding: 15px; border-radius: 8px; outline: none; font-size: 0.9rem; transition: 0.3s; }
        .input-form input:focus { border-color: var(--accent-cyan); box-shadow: 0 0 12px rgba(0, 210, 255, 0.15); }
        .send-bot-btn { background: var(--accent-cyan); border: none; padding: 0 25px; border-radius: 8px; font-weight: 800; text-transform: uppercase; font-size: 0.85rem; cursor: pointer; color: #000; letter-spacing: 0.5px; transition: 0.3s; }
        .send-bot-btn:hover { background: white; transform: translateY(-1px); }
    </style>
</head>
<body>

    <div class="chat-container">
        <header class="chat-header">
            <h2><i class="fa-solid fa-robot"></i> STEP<span>UP.</span> AI Assistant Terminal</h2>
            <a href="admin_dashboard.php" class="back-dashboard"><i class="fa-solid fa-arrow-left"></i> Console</a>
        </header>

        <div class="chat-logs-box" id="chatLogsWrapper">
            <div class="chat-bubble bubble-bot">
                👋 **System Diagnostic Online.** Welcome back, Operator. 

                Main system registries se connected hoon. Aap mujhse live revenue charts index, pending order volumes ya dynamic customer support tickets ka live status direct trace kar sakte hain. Kuch bhi puchiye!
            </div>
        </div>

        <div class="chat-input-bar">
            <form class="input-form" id="chatSubmissionTrigger">
                <input type="text" id="userMsgPayload" placeholder="Type administrative query token (e.g. Total Revenue, Pending Orders)..." autocomplete="off" required>
                <button type="submit" class="send-bot-btn">Query</button>
            </form>
        </div>
    </div>

    <script>
        const logsBox = document.getElementById('chatLogsWrapper');

        document.getElementById('chatSubmissionTrigger').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const inputField = document.getElementById('userMsgPayload');
            const messageText = inputField.value.trim();
            if(!messageText) return;

            // 1. Append User Bubble dynamically to DOM
            appendBubble(messageText, 'admin');
            inputField.value = ""; // Flush input frame
            scrollLogs();

            // 2. Mock Bot Processing state text block
            const loadingId = appendBubble("Processing framework analytics queries...", 'bot processing-node');
            scrollLogs();

            try {
                const formData = new FormData();
                formData.append('bot_msg', messageText);

                const response = await fetch('admin_bot_core.php', { method: 'POST', body: formData });
                const dataText = await response.text();
                
                // Clear processing frame placeholder node
                document.getElementById(loadingId).remove();

                const parsedData = JSON.parse(dataText);
                appendBubble(parsedData.reply, 'bot');
                
            } catch (error) {
                console.error("Chat Router Stack Error:", error);
                document.getElementById(loadingId).innerText = "❌ Connection timeout error matching network nodes.";
            } finally {
                scrollLogs();
            }
        });

        // Modular dynamic bubble factory configuration
        function appendBubble(text, speakerClass) {
            const id = 'bubble_' + Math.floor(Math.random() * 1000000);
            const div = document.createElement('div');
            div.className = `chat-bubble bubble-${speakerClass}`;
            div.id = id;
            div.innerText = text;
            logsBox.appendChild(div);
            return id;
        }

        function scrollLogs() {
            logsBox.scrollTop = logsBox.scrollHeight;
        }
    </script>
</body>
</html>