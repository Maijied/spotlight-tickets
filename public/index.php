<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

// Calculate current sales per slot and tier
$bookings = Database::getBookings();
$slotSales = []; // [slot_id][tier] => count
foreach ($bookings as $b) {
    $sid = $b['slot_id'] ?? 'slot_default';
    if (!isset($slotSales[$sid])) $slotSales[$sid] = ['regular' => 0, 'vip' => 0, 'front' => 0];
    
    if (stripos($b['tier'], 'regular') !== false) $slotSales[$sid]['regular'] += $b['quantity'];
    elseif (stripos($b['tier'], 'vip') !== false) $slotSales[$sid]['vip'] += $b['quantity'];
    elseif (stripos($b['tier'], 'front') !== false) $slotSales[$sid]['front'] += $b['quantity'];
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

        /* Slot Card Styles */
        .slot-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .slot-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            padding: 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
            text-align: center;
        }
        .slot-card:hover { border-color: var(--accent); background: rgba(249, 115, 22, 0.1); }
        .slot-card.selected {
            border-color: var(--accent);
            background: rgba(249, 115, 22, 0.2);
            box-shadow: 0 0 15px rgba(249, 115, 22, 0.3);
        }
        .slot-time { font-size: 1.1rem; font-weight: 700; color: #fff; display: block; margin-bottom: 5px; }
        .slot-loc { font-size: 0.85rem; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo EVENT_NAME; ?></h1>
            <p style="color: var(--accent); font-style: italic; letter-spacing: 3px; font-weight: 600;">এক কালজয়ী নাট্য গাথা</p>
            <div style="margin-top: 15px; font-size: 0.95rem; color: #9ca3af; letter-spacing: 1px;">
                <span id="event-time-display">
                    <i class="fas fa-calendar-alt" style="color: var(--accent);"></i> <?php echo EVENT_DATE_TIME; ?> | 
                    <i class="fas fa-map-marker-alt" style="color: var(--accent);"></i> <?php echo EVENT_LOCATION; ?>
                </span>
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

            <div class="form-group">
                <label>শো এর সময় ও স্থান</label>
                <div class="slot-grid" id="slot-grid">
                    <?php foreach($SLOTS as $index => $s): ?>
                        <div class="slot-card <?php echo $index === 0 ? 'selected' : ''; ?>" 
                             onclick="selectSlot('<?php echo $s['id']; ?>', this)"
                             data-slot-id="<?php echo $s['id']; ?>"
                        >
                            <span class="slot-time"><?php echo htmlspecialchars($s['time']); ?></span>
                            <span class="slot-loc"><?php echo htmlspecialchars($s['location']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Hidden Input -->
                <input type="hidden" id="slot_id" name="slot_id" value="<?php echo $SLOTS[0]['id']; ?>">
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="ticket_type">আসন বিভাগ</label>
                    <select id="ticket_type" name="ticket_type" required onchange="calculatePrice()">
                        <?php foreach($TICKET_TIERS as $key => $tier): ?>
                            <option value="<?php echo $key; ?>" data-price="<?php echo $tier['price']; ?>">
                                <?php echo $tier['name']; ?>
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
        const slots = <?php echo json_encode($SLOTS); ?>;
        const slotSales = <?php echo json_encode($slotSales); ?>;

        function selectSlot(slotId, cardDesc) {
            // Visual Update
            document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('selected'));
            cardDesc.classList.add('selected');
            
            // Logic Update
            document.getElementById('slot_id').value = slotId;
            updateTierAvailability();
            calculatePrice();
        }

        function updateTierAvailability() {
            const slotId = document.getElementById('slot_id').value;
            const ticketTypeSelect = document.getElementById('ticket_type');
            const selectedSlot = slots.find(s => s.id === slotId);
            const sales = slotSales[slotId] || {regular: 0, vip: 0, front: 0};
            
            // Update Base Prices for this slot
            const slotPrices = selectedSlot.prices || {};

            Array.from(ticketTypeSelect.options).forEach(opt => {
                const tierKey = opt.value;
                const cap = selectedSlot.capacities[tierKey] || 0;
                const sold = sales[tierKey] || 0;
                const isSoldOut = sold >= cap;
                
                // Update Price Data
                if (slotPrices[tierKey]) {
                    opt.setAttribute('data-price', slotPrices[tierKey]);
                    // Update option text if not sold out to show new price
                    const baseName = tiers[tierKey].name; // Assuming 'name' is static
                    // We might want to append price to name, or just rely on breakdown
                } else {
                    // Revert to global default
                    opt.setAttribute('data-price', tiers[tierKey].price);
                }

                if (isSoldOut) {
                    opt.disabled = true;
                    opt.style.color = "#666";
                    if (!opt.innerText.includes('(Sold Out)')) opt.innerText = tiers[tierKey].name + " (Sold Out)";
                } else {
                    opt.disabled = false;
                    opt.style.color = "";
                    opt.innerText = tiers[tierKey].name;
                }
            });

            // Update Header info
            document.getElementById('event-time-display').innerText = selectedSlot.time + " | " + selectedSlot.location;
        }

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

            window.onload = () => {
                updateTierAvailability();
                calculatePrice();
            };
    </script>
</body>
</html>
