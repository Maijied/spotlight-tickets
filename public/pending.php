<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>যাচাইকরণ চলছে - <?php echo EVENT_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Hind+Siliguri:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background: #0f0a14; color: #fff; font-family: 'Hind Siliguri', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: #1a1a1a; padding: 40px; border-radius: 12px; border: 2px solid #fbbf24; text-align: center; max-width: 500px; box-shadow: 0 0 30px rgba(251, 191, 36, 0.2); }
        h1 { color: #fbbf24; font-family: 'Playfair Display', serif; }
        .txn { background: #333; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 1.2rem; letter-spacing: 2px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>⏳ যাচাইকরণ চলছে</h1>
        <p>ধন্যবাদ! আপনার বুকিং রিকোয়েস্ট আমরা পেয়েছি।</p>
        <p>আপনার ট্রানজ্যাকশন আইডি:</p>
        <div class="txn"><?php echo htmlspecialchars($_GET['txn'] ?? 'N/A'); ?></div>
        <br>
        <p style="color: #ccc; font-size: 0.9rem;">
            অ্যাডমিন আপনার পেমেন্ট যাচাই করার পর আপনি একটি <strong>কনফার্মেশন ইমেইল/SMS</strong> পাবেন। এতে ১-২ ঘণ্টা সময় লাগতে পারে।
        </p>
        
        <div style="margin-top: 30px;">
            <a href="success.php?txnid=<?php echo htmlspecialchars($_GET['txn'] ?? ''); ?>" style="background: #fbbf24; color: #000; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: bold;">
                টিকেট চেক করুন <!-- Check Ticket Status -->
            </a>
        </div>
        
        <a href="index.php" style="color: #fbbf24; margin-top: 20px; display: inline-block;">হোম পেইজে ফিরে যান</a>
    </div>
</body>
</html>
