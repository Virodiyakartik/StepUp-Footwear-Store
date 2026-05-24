<?php
session_start();
require_once 'db.php'; // Database connection
$is_user_logged = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepUp | Shop Collections</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        :root {
            --bg-dark: #0f1113;
            --card-bg: #1a1d21;
            --accent-blue: #00d2ff;
            --accent-emerald: #10b981;
            --accent-amber: #ffcc00;
            --accent-rose: #ff3366;
            --text-main: #e0e0e0;
            --text-dim: #a0a0a0;
            --border-muted: rgba(255, 255, 255, 0.05);
            --transition-smooth: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: var(--bg-dark); color: var(--text-main); font-family: 'Inter', sans-serif; padding-top: 100px; overflow-x: hidden; }

        /* Navigation */
        nav { background: rgba(15, 17, 19, 0.95); backdrop-filter: blur(10px); padding: 20px 8%; display: flex; justify-content: space-between; align-items: center; position: fixed; top: 0; width: 100%; z-index: 1000; border-bottom: 1px solid var(--border-muted); transition: var(--transition-smooth); }
        .logo { font-size: 1.5rem; font-weight: 800; color: #fff; text-decoration: none; letter-spacing: 0.5px; }
        .logo span { color: var(--accent-blue); }
        
        .nav-links-container { display: flex; gap: 20px; align-items: center; }
        .back-home { color: #fff; text-decoration: none; font-size: 0.9rem; border: 1px solid var(--accent-blue); padding: 8px 15px; border-radius: 5px; font-weight: 600; transition: var(--transition-smooth); }
        .back-home:hover { background: var(--accent-blue); color: #000; box-shadow: 0 0 15px rgba(0, 210, 255, 0.3); }

        /* Brand Selector Bar */
        .brand-selector { display: flex; justify-content: center; gap: 25px; padding: 30px 5%; background: #14171a; flex-wrap: wrap; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.02); }
        .brand-item { cursor: pointer; text-align: center; transition: var(--transition-smooth); opacity: 0.4; min-width: 80px; }
        .brand-item img { width: 50px; height: 35px; object-fit: contain; filter: invert(1); margin: 0 auto 8px; display: block; transition: var(--transition-smooth); }
        .brand-item .brand-avatar { width: 45px; height: 45px; background: #25292e; color: var(--accent-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; border: 1px solid var(--border-muted); margin: 0 auto 8px; transition: var(--transition-smooth); }
        .brand-item:hover { opacity: 0.8; transform: translateY(-3px); }
        .brand-item.active { opacity: 1; transform: translateY(-4px); }
        .brand-item.active img { filter: invert(1) drop-shadow(0 0 8px var(--accent-blue)); }
        .brand-item.active .brand-avatar { border-color: var(--accent-blue); box-shadow: 0 0 15px rgba(0, 210, 255, 0.25); }
        .brand-item span { font-size: 0.8rem; font-weight: 600; display: block; text-transform: capitalize; margin-top: 2px; color: var(--text-dim); transition: var(--transition-smooth); }
        .brand-item.active span { color: var(--accent-blue); font-weight: 700; }

        /* Catalog Grid Mapping */
        .shop-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; padding: 40px 8%; }
        .shoe-card { background: var(--card-bg); padding: 25px; border-radius: 16px; text-align: center; border: 1px solid rgba(255,255,255,0.02); animation: cardAppear 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; cursor: pointer; transition: var(--transition-smooth); }
        @keyframes cardAppear { from { opacity: 0; transform: translateY(25px); } to { opacity: 1; transform: translateY(0); } }
        .shoe-card:hover { transform: translateY(-8px); border-color: var(--accent-blue); box-shadow: 0 12px 30px rgba(0, 210, 255, 0.12); }
        .shoe-card img { width: 100%; height: 220px; object-fit: contain; margin-bottom: 20px; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.6)); transition: transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1); }
        .shoe-card:hover img { transform: scale(1.08) rotate(-2deg); }
        .shoe-card h3 { font-size: 1.1rem; font-weight: 600; margin-bottom: 8px; color: #fff; transition: color 0.3s; }
        .shoe-card:hover h3 { color: var(--accent-blue); }
        .price { color: var(--accent-blue); font-weight: 800; font-size: 1.25rem; letter-spacing: -0.2px; }
        .buy-btn { display: inline-block; margin-top: 15px; padding: 10px 24px; background: rgba(255,255,255,0.03); color: var(--text-main); text-decoration: none; border-radius: 30px; font-weight: 700; border: 1px solid rgba(255,255,255,0.08); text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px; transition: var(--transition-smooth); }
        .shoe-card:hover .buy-btn { background: #fff; color: #000; box-shadow: 0 4px 15px rgba(255,255,255,0.2); }

        /* Advanced UI Glassmorphic Modals Architecture */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(5, 6, 8, 0.85); backdrop-filter: blur(12px); display: none; justify-content: center; align-items: center; z-index: 3000; transition: opacity 0.3s ease; }
        .modal-content { background: #15171e; width: 95%; max-width: 920px; display: flex; border-radius: 24px; border: 1px solid var(--accent-blue); overflow: hidden; position: relative; opacity: 0; transform: scale(0.9) translateY(15px); transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); max-height: 88vh; box-shadow: 0 30px 60px rgba(0,0,0,0.7); }
        .modal-overlay.show-modal .modal-content { opacity: 1; transform: scale(1) translateY(0); }
        .close-modal { position: absolute; top: 22px; right: 26px; font-size: 1.8rem; color: var(--text-secondary); cursor: pointer; z-index: 10; transition: color 0.2s, transform 0.2s; }
        .close-modal:hover { color: var(--accent-rose); transform: rotate(90deg); }
        
        .modal-left { flex: 1; background: #0c0e12; padding: 40px; display: flex; align-items: center; justify-content: center; border-right: 1px solid rgba(255,255,255,0.02); }
        .modal-left img { width: 100%; filter: drop-shadow(0 25px 35px rgba(0,0,0,0.7)); animation: shoeFloat 4s ease-in-out infinite; }
        @keyframes shoeFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        
        .modal-right { flex: 1.2; padding: 45px; display: flex; flex-direction: column; overflow-y: auto; background: #13151b; }
        .modal-right h2 { color: #fff; font-size: 1.8rem; font-weight: 800; margin-bottom: 6px; letter-spacing: -0.5px; }
        .modal-price { font-size: 1.6rem; font-weight: 800; margin-bottom: 20px; color: var(--accent-blue); }
        .detail-text { color: var(--text-dim); font-size: 0.9rem; margin-bottom: 25px; line-height: 1.6; }
        
        /* Interactive Size Nodes Selector */
        .size-selector { margin-bottom: 25px; }
        .size-grid { display: flex; gap: 12px; margin-top: 10px; }
        .size-btn { padding: 10px 18px; border: 1px solid #2e3245; background: #1a1d26; color: var(--text-primary); cursor: pointer; border-radius: 8px; font-size: 0.85rem; font-weight: 600; transition: var(--transition-smooth); }
        .size-btn:hover { border-color: var(--accent-blue); color: var(--accent-blue); }
        .size-btn.active { background: var(--accent-blue); color: #000; border-color: var(--accent-blue); font-weight: 800; box-shadow: 0 4px 15px rgba(0, 210, 255, 0.3); }

        /* Multi-Step Wizard Engine Transitions */
        .checkout-step-panel { display: none; opacity: 0; transform: translateX(15px); transition: opacity 0.35s ease, transform 0.35s ease; }
        .checkout-step-panel.active-step { display: block; opacity: 1; transform: translateX(0); }

        .feedback-container-block { border-top: 1px solid rgba(255,255,255,0.03); padding: 22px 0; margin-bottom: 10px; }
        .star-rating-row { color: var(--accent-amber); font-size: 1.05rem; display: flex; gap: 5px; align-items: center; margin-bottom: 8px; }
        .review-status-txt { font-size: 0.8rem; color: var(--text-secondary); line-height: 1.4; }

        /* Form Controls */
        .shipping-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 24px; }
        .shipping-form-grid .full-row { grid-column: span 2; }
        .form-group-wrapper { display: flex; flex-direction: column; gap: 6px; }
        .form-group-wrapper label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); font-weight: 700; letter-spacing: 0.5px; }
        .form-group-wrapper input { width: 100%; padding: 13px 16px; background: #1a1d26; border: 1px solid #2e3245; color: white; border-radius: 8px; outline: none; font-size: 0.9rem; }
        .form-group-wrapper input:focus { border-color: var(--accent-blue); background: #1f2330; }

        .payment-gateway-section { background: #11131a; padding: 22px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.02); margin-bottom: 25px; }
        .payment-gateway-section p { font-size: 0.9rem; font-weight: 700; margin-bottom: 14px; }
        .pay-methods { display: flex; gap: 24px; }
        .pay-radio { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 500; }
        .pay-radio input { cursor: pointer; accent-color: var(--accent-blue); width: 17px; height: 17px; }
        .online-fields { display: none; margin-top: 18px; }
        .online-fields input { width: 100%; padding: 13px 16px; background: #1a1d26; border: 1px solid #2e3245; color: white; border-radius: 8px; outline: none; }

        .wizard-nav-btn { width: 100%; padding: 16px; background: var(--accent-blue); border: none; color: #000; font-weight: 800; text-transform: uppercase; cursor: pointer; border-radius: 8px; font-size: 0.9rem; transition: var(--transition-smooth); display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 15px rgba(0, 210, 255, 0.2); }
        .wizard-nav-btn:hover { background: #fff; transform: translateY(-2px); }

        .receipt-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(5,6,8,0.92); backdrop-filter: blur(15px); display: none; justify-content: center; align-items: center; z-index: 5000; padding: 20px; opacity: 0; transition: opacity 0.3s ease; }
        .receipt-card { background: #ffffff; color: #111111; padding: 45px; border-radius: 16px; max-width: 500px; width: 100%; transform: translateY(20px); transition: all 0.4s ease; }
        .receipt-overlay.show-receipt { opacity: 1; }
        .receipt-overlay.show-receipt .receipt-card { transform: translateY(0); }
        
        .receipt-header { text-align: center; border-bottom: 2px dashed #dddddd; padding-bottom: 20px; margin-bottom: 20px; }
        .receipt-header h2 { font-weight: 800; color: #0f1113; font-size: 1.6rem; }
        .receipt-header h2 span { color: #00a3cc; }
        .receipt-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem; color: #444; }
        .receipt-row.total-row { border-top: 2px solid #111; padding-top: 14px; font-weight: 800; font-size: 1.15rem; margin-top: 18px; color: #000; }
        .download-receipt-btn { width: 100%; padding: 15px; background: #00a3cc; border: none; color: white; font-weight: 700; border-radius: 8px; cursor: pointer; text-transform: uppercase; margin-top: 25px; transition: var(--transition-smooth); font-size: 0.9rem; }
        .download-receipt-btn:hover { background: #0f1113; }
        .close-receipt { text-align: center; margin-top: 18px; font-size: 0.85rem; color: #666; cursor: pointer; text-decoration: underline; }

        footer { text-align: center; padding: 60px 20px; color: #555; border-top: 1px solid var(--border-muted); font-size: 0.9rem; }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">STEP<span>UP.</span></a>
    <div class="nav-links-container">
        <?php if($is_user_logged): ?>
            <a href="wishlist.php" style="color: var(--accent-rose); font-size: 0.9rem; font-weight: 600; text-decoration: none; text-transform: uppercase; margin-right: 15px;"><i class="fa-solid fa-heart"></i> Wishlist</a>
            <a href="my_orders.php" style="color: var(--accent-emerald); font-size: 0.9rem; font-weight: 600; text-decoration: none; text-transform: uppercase; margin-right: 15px;"><i class="fa-solid fa-box-open"></i> My Orders</a>
        <?php endif; ?>
        <a href="index.php" class="back-home"><i class="fa-solid fa-house"></i> Home</a>
    </div>
</nav>

<div class="brand-selector">
    <div class="brand-item active" onclick="filterShoes('all', this)">
        <div class="brand-avatar"><i class="fa-solid fa-border-all"></i></div>
        <span>All Items</span>
    </div>
    <?php
    $brand_query = $conn->query("SELECT DISTINCT brand_name FROM products WHERE brand_name != '' ORDER BY brand_name ASC");
    $known_logos = [
        'nike' => 'https://upload.wikimedia.org/wikipedia/commons/a/a6/Logo_NIKE.svg',
        'adidas' => 'https://upload.wikimedia.org/wikipedia/commons/2/20/Adidas_Logo.svg',
        'jordan' => 'https://upload.wikimedia.org/wikipedia/en/3/37/Jumpman_logo.svg',
        'puma' => 'https://upload.wikimedia.org/wikipedia/commons/8/88/Puma_Logo.svg',
        'reebok' => 'https://upload.wikimedia.org/wikipedia/commons/5/5f/Reebok_2019_logo.svg',
        'vans' => 'https://upload.wikimedia.org/wikipedia/commons/9/9b/Vans_logo.svg'
    ];

    while($b_row = $brand_query->fetch_assoc()) {
        $b_name = trim($b_row['brand_name']);
        $b_lower = strtolower($b_name);
        
        echo '<div class="brand-item" onclick="filterShoes(\''.$b_lower.'\', this)">';
        if(array_key_exists($b_lower, $known_logos)) {
            echo '<img src="'.$known_logos[$b_lower].'" alt="'.$b_name.'">';
        } else {
            $short_title = strtoupper(substr($b_name, 0, 2));
            echo '<div class="brand-avatar">'.$short_title.'</div>';
        }
        echo '<span>'.$b_name.'</span></div>';
    }
    ?>
</div>

<div class="shop-grid" id="shoeGrid">
    <?php
    $result = $conn->query("SELECT * FROM products ORDER BY p_id DESC");
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $brand_class = strtolower(trim($row['brand_name']));
            ?>
            <div class="shoe-card <?php echo $brand_class; ?>" onclick="openProduct('<?php echo $row['p_id']; ?>', '<?php echo htmlspecialchars($row['product_title']); ?>', '<?php echo htmlspecialchars($row['product_price']); ?>', '<?php echo htmlspecialchars($row['image_path']); ?>')">
                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Shoe">
                <h3><?php echo htmlspecialchars($row['product_title']); ?></h3>
                <p class="price"><?php echo htmlspecialchars($row['product_price']); ?></p>
                <span class="buy-btn">View Details</span>
            </div>
            <?php
        }
    } else {
        echo "<p style='grid-column: 1/-1; text-align:center; color:#666;'>No shoes available in the shop right now.</p>";
    }
    ?>
</div>

<div class="modal-overlay" id="productModal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeProduct()">&times;</span>
        <div class="modal-left">
            <img id="modalImg" src="" alt="Shoe Presentation Node">
        </div>
        <div class="modal-right">
            <h2 id="modalTitle">Shoe Name</h2>
            <div class="modal-price" id="modalPrice">$0.00</div>

            <div class="checkout-step-panel active-step" id="checkoutStepDetails">
                <p class="detail-text">Experience premium adaptive matrix cushioning with light engineering panels built around minimalist streetwear design codes.</p>
                
                <div class="size-selector">
                    <p style="font-size: 0.85rem; font-weight:700; margin-bottom: 4px; color:#fff;">Select Size (UK):</p>
                    <div class="size-grid">
                        <button class="size-btn">7</button>
                        <button class="size-btn active">8</button>
                        <button class="size-btn">9</button>
                        <button class="size-btn">10</button>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 10px;">
                    <button type="button" class="wizard-nav-btn" style="background:#232630; border:1px solid #333; color:var(--accent-rose); width: 15%; box-shadow:none; font-size:1.1rem;" onclick="addToWishlistAction(event)" title="Add to Wishlist">
                        <i class="fa-solid fa-heart"></i>
                    </button>
                    <button type="button" class="wizard-nav-btn" style="background:#232630; border:1px solid #333; color:var(--text-main); width: 35%; box-shadow:none;" onclick="addToCartAction(event)">
                        <i class="fa-solid fa-cart-plus"></i> Cart
                    </button>
                    <button type="button" class="wizard-nav-btn" style="width: 50%;" onclick="navigateToStep('shipping')">
                        Buy Now
                    </button>
                </div>

                <div class="feedback-container-block">
                    <div class="star-rating-row">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-alt"></i>
                        <span style="font-weight: 800; font-size:0.95rem; margin-left: 5px; color:#fff;">4.8 / 5</span>
                    </div>
                    <p class="review-status-txt">Based on verified member feedback logs distributed across Ahmedabad stores.</p>

                    <?php if($is_user_logged): ?>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed rgba(255,255,255,0.1);">
                        <p style="font-size: 0.8rem; font-weight: 600; margin-bottom: 8px; color: var(--accent-blue);">Write a Review:</p>
                        <form action="submit_review.php" method="POST" style="display:flex; flex-direction:column; gap:8px;">
                            <input type="hidden" name="product_id" id="reviewProductId">
                            <select name="rating" style="padding: 8px; background: #1a1d26; color: #fff; border: 1px solid #2e3245; border-radius: 6px; outline:none; font-size: 0.8rem;">
                                <option value="5">⭐⭐⭐⭐⭐ 5 Stars</option>
                                <option value="4">⭐⭐⭐⭐ 4 Stars</option>
                                <option value="3">⭐⭐⭐ 3 Stars</option>
                                <option value="2">⭐⭐ 2 Stars</option>
                                <option value="1">⭐ 1 Star</option>
                            </select>
                            <textarea name="review_text" rows="2" placeholder="Share your experience..." required style="padding: 8px; background: #1a1d26; color: #fff; border: 1px solid #2e3245; border-radius: 6px; outline:none; resize:none; font-size:0.8rem;"></textarea>
                            <button type="submit" style="background: var(--accent-emerald); color: #000; border: none; padding: 8px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size:0.75rem; text-transform: uppercase;">Submit Feedback</button>
                        </form>
                    </div>
                    <?php else: ?>
                        <p style="font-size:0.8rem; color:var(--accent-amber); margin-top:15px; padding-top: 15px; border-top: 1px dashed rgba(255,255,255,0.1);"><i class="fa-solid fa-lock"></i> Log in to submit a review.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="checkout-step-panel" id="checkoutStepShipping">
                <p style="font-size:0.9rem; font-weight:800; margin-bottom:18px; color: var(--accent-blue); text-transform:uppercase; letter-spacing:0.5px;"><i class="fa-solid fa-truck-fast"></i> Shipping Destination Registry</p>
                <div class="shipping-form-grid">
                    <div class="form-group-wrapper full-row">
                        <label>Full Name</label>
                        <input type="text" id="custName" placeholder="Enter full name identity" required>
                    </div>
                    <div class="form-group-wrapper">
                        <label>Email Vector</label>
                        <input type="email" id="custEmail" placeholder="name@example.com" value="<?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''; ?>" required>
                    </div>
                    <div class="form-group-wrapper">
                        <label>Mobile Line</label>
                        <input type="tel" id="custMobile" placeholder="Enter contact line" required>
                    </div>
                    <div class="form-group-wrapper full-row">
                        <label>Street Address Location</label>
                        <input type="text" id="custAddress" placeholder="Flat/House No., Colony, Landmark" required>
                    </div>
                    <div class="form-group-wrapper">
                        <label>City Hub</label>
                        <input type="text" id="custCity" placeholder="e.g. Ahmedabad" required>
                    </div>
                    <div class="form-group-wrapper">
                        <label>Pin Code Vector</label>
                        <input type="text" id="custPin" placeholder="e.g. 380001" required>
                    </div>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="button" class="wizard-nav-btn" style="background:#232630; border:1px solid #333; color:var(--text-secondary); width: 35%; box-shadow:none;" onclick="navigateToStep('details')">Back</button>
                    <button type="button" class="wizard-nav-btn" style="width: 65%;" onclick="navigateToStep('payment')">Proceed to Payment</button>
                </div>
            </div>

            <div class="checkout-step-panel" id="checkoutStepPayment">
                <div class="payment-gateway-section">
                    <p><i class="fa-solid fa-shield-halved"></i> Transaction Gateway Protocol</p>
                    <div class="pay-methods">
                        <label class="pay-radio">
                            <input type="radio" name="pay_method" value="COD" checked onclick="togglePaymentFields('COD')"> Cash on Delivery
                        </label>
                        <label class="pay-radio">
                            <input type="radio" name="pay_method" value="Online" onclick="togglePaymentFields('Online')"> Net Banking / UPI
                        </label>
                    </div>
                    <div class="online-fields" id="onlineFieldsContainer">
                        <input type="text" id="transactionIdInput" placeholder="Enter 12-Digit UPI Ref No. / Transaction ID">
                    </div>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="button" class="wizard-nav-btn" style="background:#232630; border:1px solid #333; color:var(--text-secondary); width: 35%; box-shadow:none;" onclick="navigateToStep('shipping')">Back</button>
                    <button type="button" class="wizard-nav-btn" style="width: 65%; background: var(--accent-emerald); color:#fff;" id="finalCheckoutBtn">Complete Order</button>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="receipt-overlay" id="receiptOverlayBlock">
    <div class="receipt-card">
        <div id="invoicePrintTarget">
            <div class="receipt-header">
                <h2>STEP<span>UP.</span> HUB</h2>
                <p style="font-size: 0.82rem; color: #666; margin-top: 5px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Premium Sneakers Log System</p>
            </div>
            <div class="receipt-row"><strong>Receipt Ref:</strong> <span id="recInvoiceNo">#STP000000</span></div>
            <div class="receipt-row"><strong>Date Logs:</strong> <span id="recDate"><?php echo date("Y-m-d"); ?></span></div>
            <div class="receipt-row"><strong>User Account:</strong> <span id="recUserEmail">guest@stepup.com</span></div>
            <div class="receipt-row"><strong>Customer Name:</strong> <span id="recUserName">Guest Operator</span></div>
            <hr style="border: none; border-top: 1px dashed #ccc; margin: 15px 0;">
            <div class="receipt-row"><strong>Product Identifier:</strong> <span id="recProductName" style="font-weight: 700; color:#000;">Shoe Model</span></div>
            <div class="receipt-row"><strong>Size Segment (UK):</strong> <span id="recSize">8</span></div>
            <div class="receipt-row"><strong>Payment Strategy:</strong> <span id="recMethod">COD</span></div>
            <div class="receipt-row" id="recTxnRow" style="display:none;"><strong>Transaction Ref ID:</strong> <span id="recTxnId">-</span></div>
            <div class="receipt-row total-row"><span>Total Net Value Paid</span> <span id="recTotal">₹0.00</span></div>
        </div>
        <button class="download-receipt-btn" onclick="generatePdfInvoice()"><i class="fa-solid fa-file-arrow-down"></i> Download Receipt PDF</button>
        <div class="close-receipt" onclick="closeReceiptView()">Close & Return to Shop</div>
    </div>
</div>

<footer>&copy; 2026 StepUp Footwear | Premium Store</footer>

<script>
    function filterShoes(brand, element) {
        document.querySelectorAll('.brand-item').forEach(item => item.classList.remove('active'));
        element.classList.add('active');
        const cards = document.querySelectorAll('.shoe-card');
        cards.forEach(card => {
            if (brand === 'all' || card.classList.contains(brand)) {
                card.style.display = 'block';
                card.style.animation = 'none';
                void card.offsetWidth;
                card.style.animation = 'cardAppear 0.5s ease-out forwards';
            } else { card.style.display = 'none'; }
        });
    }

    function openProduct(id, name, price, img) {
        document.getElementById('reviewProductId').value = id; 
        document.getElementById('modalTitle').innerText = name;
        document.getElementById('modalPrice').innerText = price;
        document.getElementById('modalImg').src = img;
        
        navigateToStep('details'); 
        
        const overlay = document.getElementById('productModal');
        overlay.style.display = 'flex';
        setTimeout(() => { overlay.classList.add('show-modal'); }, 30);
    }

    function closeProduct() { 
        const overlay = document.getElementById('productModal');
        overlay.classList.remove('show-modal');
        setTimeout(() => { overlay.style.display = 'none'; }, 350);
    }

    // --- NEW AJAX FUNCTION: ADD TO WISHLIST ---
    async function addToWishlistAction(event) {
        const btn = event.currentTarget;
        const pId = document.getElementById('reviewProductId').value;
        const originalHtml = btn.innerHTML;

        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        try {
            const response = await fetch('add_to_wishlist.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `product_id=${pId}`
            });
            const resText = await response.text();
            
            if(resText.trim() === "success") {
                btn.innerHTML = '<i class="fa-solid fa-check"></i>';
                btn.style.background = "var(--accent-rose)";
                btn.style.color = "#fff";
                setTimeout(() => { btn.innerHTML = originalHtml; btn.style.background = "#232630"; btn.style.color = "var(--accent-rose)"; }, 2000);
            } else if (resText.trim() === "exists") {
                alert("This shoe is already in your wishlist!");
                btn.innerHTML = originalHtml;
            } else {
                alert("Please log in to add items to your wishlist.");
                btn.innerHTML = originalHtml;
            }
        } catch(e) {
            btn.innerHTML = originalHtml;
            console.error("Wishlist Error:", e);
        }
    }

    function addToCartAction(event) {
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Added!';
        btn.style.color = "var(--accent-emerald)";
        btn.style.borderColor = "var(--accent-emerald)";
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.color = "var(--text-main)";
            btn.style.borderColor = "#333";
        }, 2000);
    }

    function togglePaymentFields(method) {
        const target = document.getElementById('onlineFieldsContainer');
        target.style.display = (method === 'Online') ? 'block' : 'none';
    }

    function navigateToStep(step) {
        const panelDetails = document.getElementById('checkoutStepDetails');
        const panelShipping = document.getElementById('checkoutStepShipping');
        const panelPayment = document.getElementById('checkoutStepPayment');
        
        document.querySelectorAll('.checkout-step-panel').forEach(p => p.classList.remove('active-step'));

        if(step === 'details') panelDetails.classList.add('active-step');
        else if (step === 'shipping') panelShipping.classList.add('active-step');
        else if (step === 'payment') {
            if(document.getElementById('custName').value.trim() === '' || 
               document.getElementById('custEmail').value.trim() === '' || 
               document.getElementById('custMobile').value.trim() === '' || 
               document.getElementById('custAddress').value.trim() === '' || 
               document.getElementById('custCity').value.trim() === '' || 
               document.getElementById('custPin').value.trim() === '') {
                alert('Please fill up all required fields to proceed to payment gateways.');
                panelShipping.classList.add('active-step'); 
                return;
            }
            panelPayment.classList.add('active-step');
        }
    }

    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    document.getElementById('finalCheckoutBtn').addEventListener('click', async function() {
        const pName = document.getElementById('modalTitle').innerText;
        const pPrice = document.getElementById('modalPrice').innerText;
        const activeSizeElement = document.querySelector('.size-btn.active');
        const pSize = activeSizeElement ? activeSizeElement.innerText : '8';
        const pMethod = document.querySelector('input[name="pay_method"]:checked').value;
        const pTxnId = (pMethod === 'Online') ? document.getElementById('transactionIdInput').value.trim() : '';

        const cName = document.getElementById('custName').value.trim();
        const cEmail = document.getElementById('custEmail').value.trim();
        const cMobile = document.getElementById('custMobile').value.trim();
        const cAddress = document.getElementById('custAddress').value.trim();
        const cCity = document.getElementById('custCity').value.trim();
        const cPin = document.getElementById('custPin').value.trim();

        if(pMethod === 'Online' && pTxnId === '') { alert('Please input your Transaction Ref / UPI ID to verify processing.'); return; }

        try {
            const response = await fetch('place_order.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `product=${encodeURIComponent(pName)}&price=${encodeURIComponent(pPrice)}&size=${encodeURIComponent(pSize)}&pay_method=${encodeURIComponent(pMethod)}&txn_id=${encodeURIComponent(pTxnId)}&cust_name=${encodeURIComponent(cName)}&cust_email=${encodeURIComponent(cEmail)}&cust_mobile=${encodeURIComponent(cMobile)}&cust_address=${encodeURIComponent(cAddress)}&cust_city=${encodeURIComponent(cCity)}&cust_pin=${encodeURIComponent(cPin)}`
            });
            
            const resText = await response.text();
            closeProduct();

            document.getElementById('recInvoiceNo').innerText = '#STP' + Math.floor(100000 + Math.random() * 900000);
            document.getElementById('recProductName').innerText = pName;
            document.getElementById('recSize').innerText = pSize;
            document.getElementById('recMethod').innerText = pMethod === 'COD' ? 'Cash on Delivery' : 'Online Digital Paid';
            document.getElementById('recTotal').innerText = pPrice;
            document.getElementById('recUserEmail').innerText = cEmail;
            document.getElementById('recUserName').innerText = cName;
            
            if(pMethod === 'Online') {
                document.getElementById('recTxnId').innerText = pTxnId;
                document.getElementById('recTxnRow').style.display = 'flex';
            } else { document.getElementById('recTxnRow').style.display = 'none'; }

            const receiptOverlay = document.getElementById('receiptOverlayBlock');
            receiptOverlay.style.display = 'flex';
            setTimeout(() => { receiptOverlay.classList.add('show-receipt'); }, 30);

        } catch (error) { alert("Database interface error! Check XAMPP server logs."); }
    });

    function generatePdfInvoice() {
        const element = document.getElementById('invoicePrintTarget');
        const options = { margin: 10, filename: 'StepUp_Invoice_' + document.getElementById('recInvoiceNo').innerText + '.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2, logging: false }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } };
        html2pdf().from(element).set(options).save();
    }

    function closeReceiptView() {
        const receiptOverlay = document.getElementById('receiptOverlayBlock');
        receiptOverlay.classList.remove('show-receipt');
        setTimeout(() => { receiptOverlay.style.display = 'none'; window.location.reload(); }, 350);
    }
</script>
</body>
</html>