<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$txnid = isset($_GET['txnid']) ? trim(htmlspecialchars($_GET['txnid'])) : '';
$booking = null;

if($txnid) {
    // Fetch from DB
    $bookings = Database::getBookings();
    foreach($bookings as $b) {
        if(strcasecmp(trim($b['txnid']), $txnid) === 0) {
            $booking = $b;
            break;
        }
    }
}

if (!$booking) {
    die("Invalid Ticket ID");
}

if ($booking['status'] !== 'confirmed') {
    if ($booking['status'] === 'pending') {
         header("Location: pending.php?txn=$txnid");
         exit;
    }
    die("Ticket Status: " . htmlspecialchars($booking['status']));
}

$amount = $booking['amount'];
// Update variables for View
$tier = $booking['tier'];
$qty = $booking['quantity'];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ধন্যবাদ - <?php echo EVENT_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Hind+Siliguri:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6d28d9;
            --accent: #f97316;
            --card-bg: rgba(15, 10, 20, 0.95);
            --text: #f3f4f6;
            --border: #312e81;
        }

        body {
            background: url('background_theatre.jpg') no-repeat center center fixed;
            background-size: cover;
            color: var(--text);
            font-family: 'Hind Siliguri', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle, transparent 20%, rgba(0,0,0,0.85) 100%);
            z-index: -1;
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: var(--card-bg);
            padding: 50px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid var(--border);
            box-shadow: 0 0 50px rgba(0,0,0,0.9);
        }

        .icon { font-size: 4rem; color: #10b981; margin-bottom: 20px; }
        
        h1 { 
            font-family: 'Playfair Display', serif;
            color: var(--accent); 
            font-size: 2.8rem; 
            margin-bottom: 15px; 
            animation: glow 3s infinite;
        }
        
        @keyframes glow {
            0% { text-shadow: 0 0 10px rgba(249, 115, 22, 0.5); }
            50% { text-shadow: 0 0 30px var(--accent); }
            100% { text-shadow: 0 0 10px rgba(249, 115, 22, 0.5); }
        }

        p { color: #d1d5db; margin-bottom: 30px; line-height: 1.6; }

        .details {
            background: #0a0a0a;
            padding: 25px;
            border-radius: 4px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
            text-align: left;
        }

        .details div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            font-size: 1rem;
            border-bottom: 1px solid #1a1a1a;
            padding-bottom: 10px;
        }

        .details span { color: var(--accent); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1.5px; font-weight: 600; }

        .qr-section {
            background: #fff;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
            display: inline-block;
            border: 8px solid var(--accent);
            box-shadow: 0 0 20px rgba(249, 115, 22, 0.3);
        }

        .qr-section {
            background: #fff;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
            display: inline-block;
            border: 8px solid var(--accent);
            box-shadow: 0 0 20px rgba(249, 115, 22, 0.3);
        }

        .qr-section p { color: #000; font-weight: 700; margin-bottom: 10px; font-size: 0.85rem; text-transform: uppercase; }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            padding: 16px 32px;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            border: 2px solid var(--accent);
            border-radius: 4px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            transition: 0.3s;
            font-family: 'Playfair Display', serif;
            cursor: pointer;
        }

        .btn:hover { background: var(--accent); color: #000; transform: scale(1.05); }
        .btn-secondary { background: transparent; }

        @media print {
            body { background: none; color: #000; padding: 0; }
            body::before { display: none; }
            .container { 
                border: 2px solid #000; 
                box-shadow: none; 
                background: #fff; 
                max-width: 100%; 
                margin: 0;
            }
            .btn-group, .icon { display: none; }
            .details { border: 1px solid #ddd; background: #fff; }
            .details div { border-bottom: 1px solid #eee; }
            .details span, h1 { color: #000; text-shadow: none; animation: none; }
            .qr-section { border: 4px solid #000; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">📜</div>
        <h1>সাফল্য!</h1>
        <p>আপনার টিকেট সংগ্রহ সফল হয়েছে। ইমেইল চেক করুন।</p>

        <div class="details">
            <div><span>অনুষ্ঠান:</span> <strong><?php echo EVENT_NAME; ?></strong></div>
            <div><span>বিভাগ:</span> <strong><?php echo htmlspecialchars($tier); ?></strong></div>
            <div><span>সংখ্যা:</span> <strong><?php echo htmlspecialchars($qty); ?></strong></div>
            <div><span>মোট:</span> <strong><?php echo CURRENCY . ' ' . number_format($amount); ?></strong></div>
            <div><span>ট্রানজ্যাকশন:</span> <strong style="font-family: monospace;"><?php echo $txnid; ?></strong></div>
            <div style="border: none; margin: 0; padding: 0;">
                <span style="color: #10b981;">স্ট্যাটাস:</span> <strong style="color: #10b981;">কনফার্মড (PAID)</strong>
            </div>
        </div>

        <div class="qr-section">
            <p>আপনার ডিজিটাল টিকেট</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($txnid); ?>" alt="QR Code">
            <p style="margin-top: 10px; font-family: monospace;"><?php echo $txnid; ?></p>
        </div>

        <div class="btn-group">
            <button onclick="window.print()" class="btn">টিকেট প্রিন্ট করুন</button>
            <a href="index.php" class="btn btn-secondary">ফিরে যান</a>
        </div>
    </div>
</body>
</html>
