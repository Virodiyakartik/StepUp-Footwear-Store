<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Help Center | StepUp</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #0f1113; color: #e0e0e0; font-family: 'Inter', sans-serif; padding: 100px 8%; }
        .help-container { max-width: 800px; margin: 0 auto; }
        h1 { color: #fff; margin-bottom: 40px; text-align: center; }
        .faq-item { background: #1a1d21; margin-bottom: 15px; border-radius: 8px; overflow: hidden; }
        .faq-question { padding: 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: 600; color: #00d2ff; }
        .faq-answer { padding: 0 20px 20px; color: #a0a0a0; font-size: 0.9rem; line-height: 1.6; display: none; }
    </style>
</head>
<body>

<div class="help-container">
    <h1><i class="fa-solid fa-circle-question"></i> Help Center</h1>
    
    <div class="faq-item">
        <div class="faq-question" onclick="toggle(this)">How can I track my order? <i class="fa-solid fa-chevron-down"></i></div>
        <div class="faq-answer">Aap "My Orders" tab mein jaakar apne har ek transaction ka status live track kar sakte hain. Wahan aapko Pending, Dispatched, ya Delivered ki details mil jayengi.</div>
    </div>

    <div class="faq-item">
        <div class="faq-question" onclick="toggle(this)">Payment failed, what to do? <i class="fa-solid fa-chevron-down"></i></div>
        <div class="faq-answer">Agar aapka payment fail ho jata hai, toh aap hamare "Contact" page par message bhejein. Admin team aapke UPI/Transaction ID ko verify karke status manually "Paid" update kar degi.</div>
    </div>

    <div class="faq-item">
        <div class="faq-question" onclick="toggle(this)">How to download invoice? <i class="fa-solid fa-chevron-down"></i></div>
        <div class="faq-answer">Checkout complete hone ke baad ek receipt modal open hota hai, wahan "Download Receipt PDF" button par click karke aap invoice save kar sakte hain.</div>
    </div>
</div>

<script>
    function toggle(element) {
        let answer = element.nextElementSibling;
        answer.style.display = (answer.style.display === 'block') ? 'none' : 'block';
    }
</script>
</body>
</html>