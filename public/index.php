<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

// Calculate current sales per tier
$bookings = Database::getBookings();
$catSales = ['regular' => 0, 'vip' => 0, 'front' => 0];
foreach ($bookings as $b) {
    if (stripos($b['tier'], 'regular') !== false) $catSales['regular'] += $b['quantity'];
    elseif (stripos($b['tier'], 'vip') !== false) $catSales['vip'] += $b['quantity'];
    elseif (stripos($b['tier'], 'front') !== false) $catSales['front'] += $b['quantity'];
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo EVENT_NAME; ?> - টিকেট</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Hind+Siliguri:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6d28d9; /* Theatrical Purple */
            --accent: #f97316;  /* Vibrant Orange */
            --bg-overlay: rgba(0, 0, 0, 0.75);
            --card-bg: rgba(15, 10, 20, 0.9);
            --text: #f3f4f6;
            --text-gold: #fbbf24;
            --input-bg: #1a1a1a;
            --border: #312e81;
            --success: #10b981;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: url('background_theatre.jpg') no-repeat center center fixed;
            background-size: cover;
            color: var(--text);
            font-family: 'Hind Siliguri', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px 20px;
        }

        /* Theatrical Overlay */
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle, transparent 20%, rgba(0,0,0,0.85) 100%);
            z-index: -1;
        }

        @keyframes glow {
            0% { text-shadow: 0 0 10px rgba(109, 40, 217, 0.5); }
            50% { text-shadow: 0 0 30px rgba(249, 115, 22, 0.8), 0 0 15px var(--accent); }
            100% { text-shadow: 0 0 10px rgba(109, 40, 217, 0.5); }
        }

        .header h1 { 
            font-family: 'Playfair Display', serif;
            font-size: 4rem; 
            margin-bottom: 5px; 
            color: var(--accent);
            text-shadow: 0 0 20px rgba(0,0,0,0.8);
            letter-spacing: 3px;
            animation: glow 3s ease-in-out infinite;
        }

        @keyframes borderPulse {
            0% { border-color: var(--border); box-shadow: 0 0 30px rgba(109, 40, 217, 0.3); }
            50% { border-color: var(--accent); box-shadow: 0 0 50px rgba(249, 115, 22, 0.3); }
            100% { border-color: var(--border); box-shadow: 0 0 30px rgba(109, 40, 217, 0.3); }
        }

        .container {
            width: 100%;
            max-width: 650px;
            background: var(--card-bg);
            padding: 50px;
            border-radius: 8px;
            box-shadow: 0 0 60px rgba(0,0,0,0.9);
            border: 2px solid var(--border);
            position: relative;
            overflow: hidden;
            animation: borderPulse 4s infinite;
        }

        .header { text-align: center; margin-bottom: 40px; }
        
        .offer-section {
            background: rgba(109, 40, 217, 0.1);
            border: 1px solid var(--border);
            padding: 25px;
            border-radius: 4px;
            margin-bottom: 35px;
            font-size: 0.95rem;
            line-height: 1.6;
            box-shadow: inset 0 0 20px rgba(0,0,0,0.5);
        }

        .offer-section h3 { 
            font-family: 'Playfair Display', serif;
            color: var(--accent); 
            margin-bottom: 15px; 
            font-size: 1.35rem; 
            border-bottom: 1px solid var(--border); 
            padding-bottom: 10px; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .offer-list { color: #d1d5db; padding-left: 20px; }
        .offer-list li { margin-bottom: 8px; font-style: italic; }

        .price-summary {
            background: #0a0a0a;
            padding: 30px;
            border-radius: 4px;
            margin-bottom: 35px;
            border: 1px solid var(--accent);
            text-align: center;
            box-shadow: 0 0 30px rgba(249, 115, 22, 0.15);
        }

        .final-price { 
            font-family: 'Playfair Display', serif;
            font-size: 3.2rem; 
            font-weight: 700; 
            color: var(--accent); 
            display: block; 
            margin: 10px 0; 
        }
        .price-breakdown { font-size: 0.9rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; }
        .badge { display: inline-block; padding: 6px 16px; border-radius: 4px; font-size: 0.8rem; font-weight: 700; margin: 4px; border: 1px solid var(--accent); }
        .badge-offer { background: var(--accent); color: #000; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        .form-group { margin-bottom: 25px; }
        .form-group label { 
            display: block; 
            margin-bottom: 10px; 
            color: var(--accent); 
            font-size: 0.95rem; 
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }
        
        .form-group input, .form-group select {
            width: 100%; padding: 16px 20px; background: var(--input-bg);
            border: 1px solid var(--border); border-radius: 4px; color: var(--text);
            font-size: 1.1rem; transition: 0.4s;
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.4);
        }

        .form-group input:focus, .form-group select:focus {
            outline: none; border-color: var(--accent); background: #222;
        }

        .btn {
            width: 100%; padding: 22px; background: var(--primary); border: 2px solid var(--accent);
            border-radius: 4px; color: #fff; font-size: 1.4rem; font-weight: 700;
            cursor: pointer; transition: 0.4s; margin-top: 20px;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-family: 'Playfair Display', serif;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .btn:hover { 
            background: var(--accent); 
            color: #000;
            box-shadow: 0 0 40px rgba(249, 115, 22, 0.6);
            transform: translateY(-3px);
        }

        hr { border: 0; border-top: 1px solid var(--border); margin: 35px 0; }

        @media (max-width: 480px) { .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo EVENT_NAME; ?></h1>
            <p style="color: var(--accent); font-style: italic; letter-spacing: 3px; font-weight: 600;">এক কালজয়ী নাট্য গাথা</p>
            <div style="margin-top: 15px; font-size: 0.95rem; color: #9ca3af; letter-spacing: 1px;">
                <i class="fas fa-calendar-alt" style="color: var(--accent);"></i> <?php echo EVENT_DATE_TIME; ?> | 
                <i class="fas fa-map-marker-alt" style="color: var(--accent);"></i> <?php echo EVENT_LOCATION; ?>
            </div>
        </div>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <div class="offer-section">
            <h3>অগ্রিম ও গুচ্ছ টিকেটে বিশেষ ছাড়:</h3>
            <strong style="color: var(--accent);">অগ্রিম টিকেটে—</strong>
            <ul class="offer-list">
                <li>১০ জানুয়ারি ২০২৬ এর মধ্যে ক্রয় করলে ২০% ছাড়৷</li>
                <li>১৫ জানুয়ারি ২০২৬ এর মধ্যে ক্রয় করলে ১৫% ছাড়৷</li>
                <li>২০ জানুয়ারি ২০২৬ এর মধ্যে ক্রয় করলে ১০% ছাড়।</li>
            </ul>
            <br>
            <strong style="color: var(--accent);">গুচ্ছ টিকেটে—</strong>
            <ul class="offer-list">
                <li>একসাথে ৫টি বা তার অধিক টিকেট ক্রয় করলে ১০% ছাড়।</li>
                <li>একসাথে ১০টি বা তার অধিক টিকেট ক্রয় করলে ২০% ছাড়৷</li>
            </ul>
        </div>

        <div class="price-summary">
            <span style="font-size: 0.9rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px;">প্রদেয় অর্থ</span>
            <span class="final-price" id="display-price">BDT 500</span>
            <div id="applied-offers"></div>
            <div class="price-breakdown" id="price-breakdown">১টি টিকেট &times; ৫০০ BDT</div>
        </div>

        <form action="../api/create_payment.php" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="ticket_type">আসন বিভাগ</label>
                    <select id="ticket_type" name="ticket_type" required onchange="calculatePrice()">
                        <?php foreach($TICKET_TIERS as $key => $tier): 
                            $sold = $catSales[$key] ?? 0;
                            $cap = $TIER_CAPACITIES[$key] ?? 100;
                            $isSoldOut = ($sold >= $cap);
                        ?>
                            <option value="<?php echo $key; ?>" 
                                    data-price="<?php echo $tier['price']; ?>"
                                    <?php echo $isSoldOut ? 'disabled style="color: #666;"' : ''; ?>>
                                <?php echo $tier['name']; ?> <?php echo $isSoldOut ? '(Sold Out)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity">টিকেটের সংখ্যা</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="20" required oninput="calculatePrice()">
                </div>
            </div>

            <div class="form-group">
                <label for="promo_code">প্রোমো কোড (ঐচ্ছিক)</label>
                <input type="text" id="promo_code" name="promo_code" placeholder="যেমন: OFFER20" oninput="calculatePrice()">
            </div>

            <hr>

            <div class="form-group">
                <label>নাম</label>
                <input type="text" name="full_name" placeholder="আপনার পুরো নাম লিখুন" required>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>ইমেইল</label>
                    <input type="email" name="email" placeholder="example@mail.com" required>
                </div>
                <div class="form-group">
                    <label>মোবাইল</label>
                    <input type="tel" name="phone_number" placeholder="017XXXXXXXX" required>
                </div>
            </div>

            <button type="submit" class="btn">টিকেট সংগ্রহ করুন</button>
        </form>
    </div>

    <script>
        const earlyBirdRules = <?php echo json_encode($EARLY_BIRD_RULES); ?>;
        const bundleRules = <?php echo json_encode($BUNDLE_RULES); ?>;
        const promoCodes = <?php echo json_encode($PROMO_CODES); ?>;
        const tiers = <?php echo json_encode($TICKET_TIERS); ?>;

        function calculatePrice() {
            const ticketTypeSelect = document.getElementById('ticket_type');
            const qtyInput = document.getElementById('quantity');
            const promoInput = document.getElementById('promo_code').value.trim().toUpperCase();
            
            const qty = parseInt(qtyInput.value) || 1;
            const basePricePerTicket = parseInt(ticketTypeSelect.options[ticketTypeSelect.selectedIndex].getAttribute('data-price'));
            
            let totalBase = basePricePerTicket * qty;
            let finalPrice = totalBase;
            let appliedOffers = [];

            const today = new Date();
            let dateDiscount = 0;
            const sortedDates = Object.keys(earlyBirdRules).sort();
            for (let dateStr of sortedDates) {
                if (today <= new Date(dateStr)) {
                    dateDiscount = earlyBirdRules[dateStr];
                    break;
                }
            }
            if (dateDiscount > 0) {
                finalPrice -= (totalBase * (dateDiscount / 100));
                appliedOffers.push(`অগ্রিম ছাড় (${dateDiscount}%)`);
            }

            let bundleDiscount = 0;
            const sortedQtys = Object.keys(bundleRules).sort((a,b) => b-a);
            for (let minQty of sortedQtys) {
                if (qty >= parseInt(minQty)) {
                    bundleDiscount = bundleRules[minQty];
                    break;
                }
            }
            if (bundleDiscount > 0) {
                finalPrice -= (totalBase * (bundleDiscount / 100));
                appliedOffers.push(`গুচ্ছ ছাড় (${bundleDiscount}%)`);
            }

            let promoDiscount = 0;
            if (promoInput && promoCodes[promoInput]) {
                promoDiscount = promoCodes[promoInput];
                finalPrice -= (totalBase * (promoDiscount / 100));
                appliedOffers.push(`প্রোমো ছাড় (${promoDiscount}%)`);
            }

            document.getElementById('display-price').innerText = `BDT ${Math.round(finalPrice)}`;
            document.getElementById('price-breakdown').innerText = `${qty}টি টিকেট × ${basePricePerTicket} BDT`;
            
            const offerDisplay = document.getElementById('applied-offers');
            offerDisplay.innerHTML = appliedOffers.map(o => `<span class="badge badge-offer">${o}</span>`).join('');
        }

            window.onload = calculatePrice;
    </script>
</body>
</html>
