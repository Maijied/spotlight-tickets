<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ব্যর্থ - <?php echo EVENT_NAME; ?></title>
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

        .icon { font-size: 4rem; color: #ef4444; margin-bottom: 20px; }
        
        h1 { 
            font-family: 'Playfair Display', serif;
            color: var(--accent); 
            font-size: 2.8rem; 
            margin-bottom: 15px; 
        }
        
        p { color: #d1d5db; margin-bottom: 35px; line-height: 1.6; }

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
            margin: 10px;
        }

        .btn:hover { background: var(--accent); color: #000; transform: scale(1.05); }
        .btn-outline { background: transparent; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🎭</div>
        <h1>দুঃখিত!</h1>
        <p>পেমেন্ট প্রক্রিয়াটি সম্পন্ন করা সম্ভব হয়নি। অনুগ্রহ করে পুনরায় চেষ্টা করুন।</p>

        <a href="index.php" class="btn">আবার চেষ্টা করুন</a>
        <a href="mailto:support@example.com" class="btn btn-outline">সহায়তা নিন</a>
    </div>
</body>
</html>
