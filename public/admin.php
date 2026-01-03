<?php
/**
 * Simple Admin Dashboard to view Bookings
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

// Simple Password Protection (For demo purposes)
// In production, use a real login system or .htaccess
$ADMIN_PASS = 'admin123'; 

if (!isset($_GET['pass']) || $_GET['pass'] !== $ADMIN_PASS) {
    die("Unauthorized access. Access this page via admin.php?pass=admin123");
}

Database::connect();
$bookings = Database::getBookings();
// Sort by date descending
usort($bookings, function($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo EVENT_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: 'Outfit', sans-serif; padding: 40px; }
        .container { max-width: 1000px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h1 { color: #8b5cf6; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; }
        th { background: #334155; color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; }
        tr:hover { background: #263349; }
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
        .badge-vip { background: #f59e0b; color: #78350f; }
        .badge-premium { background: #ec4899; color: #500e33; }
        .badge-regular { background: #6366f1; color: #e0e7ff; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; }
        .stat-card span { color: #94a3b8; font-size: 0.9rem; }
        .stat-card p { font-size: 1.5rem; font-weight: 600; margin-top: 10px; color: #f59e0b; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Sales Dashboard</h1>
            <a href="index.php" style="color: #94a3b8; text-decoration: none;">Public Site</a>
        </header>

        <div class="stats">
            <div class="stat-card">
                <span>Total Tickets Sold</span>
                <p><?php echo count($bookings); ?></p>
            </div>
            <div class="stat-card">
                <span>Total Revenue</span>
                <p>BDT <?php echo number_format(array_sum(array_column($bookings, 'amount')), 2); ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Tier</th>
                    <th>Qty</th>
                    <th>Amount</th>
                    <th>TXN ID</th>
                    <th>Promo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bookings as $b): ?>
                <tr>
                    <td><?php echo date('M d, H:i', strtotime($b['created_at'])); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($b['name']); ?></strong><br>
                        <small style="color: #94a3b8;"><?php echo htmlspecialchars($b['email']); ?></small>
                    </td>
                    <td><span class="badge badge-<?php echo strtolower(explode(' ', $b['tier'])[0]); ?>"><?php echo htmlspecialchars($b['tier']); ?></span></td>
                    <td style="text-align: center;"><?php echo $b['quantity']; ?></td>
                    <td>BDT <?php echo number_format($b['amount'], 2); ?></td>
                    <td style="font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($b['txnid']); ?></td>
                    <td><?php echo htmlspecialchars($b['promo_used']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($bookings)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">No bookings found yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
