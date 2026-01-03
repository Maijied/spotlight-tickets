<?php
/**
 * Advanced Admin Dashboard with Multi-User Management
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

session_start();

Database::connect();
$admins = Database::getAdmins();

// --- Handle Login ---
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    foreach ($admins as $admin) {
        if ($admin['username'] === $user && password_verify($pass, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $user;
            header('Location: admin.php');
            exit;
        }
    }
    $error = 'Invalid username or password.';
}

// --- Check Auth ---
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login - Admin Dashboard</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
        <style>
            body { background: #0f172a; color: #f8fafc; font-family: 'Outfit', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-card { background: #1e293b; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 100%; max-width: 400px; border: 1px solid #334155; }
            h1 { color: #8b5cf6; margin-bottom: 30px; text-align: center; }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 8px; color: #94a3b8; font-size: 0.9rem; }
            input { width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #fff; box-sizing: border-box; }
            .btn { width: 100%; padding: 14px; background: #8b5cf6; border: none; border-radius: 6px; color: #fff; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 10px; }
            .btn:hover { background: #7c3aed; }
            .error { color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; border: 1px solid #ef4444; font-size: 0.9rem; }
        </style>
    </head>
    <body>
        <div class="login-card">
            <h1>Admin Login</h1>
            <?php if ($error): ?> <div class="error"><?php echo $error; ?></div> <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- Logged In Logic ---

// --- Handle User Management ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add User
    if (isset($_POST['add_user'])) {
        $new_user = trim($_POST['new_username']);
        $new_pass = $_POST['new_password'];
        
        // Check if exists
        $exists = false;
        foreach($admins as $a) { if($a['username'] === $new_user) $exists = true; }

        if (!$exists && !empty($new_user) && !empty($new_pass)) {
            Database::saveAdmin($new_user, password_hash($new_pass, PASSWORD_DEFAULT));
            $admins = Database::getAdmins(); // Refresh list
        }
    }
    
    // Delete User
    if (isset($_POST['delete_user'])) {
        $user_to_delete = $_POST['delete_user'];
        // Don't delete self or last admin
        if ($user_to_delete !== $_SESSION['admin_user'] && count($admins) > 1) {
            Database::deleteAdmin($user_to_delete);
            $admins = Database::getAdmins(); // Refresh list
        }
    }
}


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
        .container { max-width: 1100px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h1 { color: #8b5cf6; margin: 0; }
        .nav-links a { color: #94a3b8; text-decoration: none; margin-left: 20px; font-weight: 500; transition: 0.3s; }
        .nav-links a:hover { color: #fff; }
        
        .section-title { margin-top: 50px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: #8b5cf6; }
        .section-title hr { flex-grow: 1; border: 0; border-top: 1px solid #334155; }

        table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin-bottom: 30px; }
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

        /* User Management Form */
        .mgmt-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .form-card { background: #1e293b; padding: 25px; border-radius: 12px; border: 1px solid #334155; }
        .form-card h3 { margin-top: 0; color: #8b5cf6; margin-bottom: 20px; }
        .inline-form { display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: end; }
        .inline-form input { padding: 10px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #fff; }
        .btn-small { padding: 10px 20px; background: #8b5cf6; border: none; border-radius: 6px; color: #fff; cursor: pointer; font-weight: 600; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h1>Sales Dashboard</h1>
                <small style="color: #94a3b8;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']); ?></small>
            </div>
            <div class="nav-links">
                <a href="index.php" target="_blank">Public Site</a>
                <a href="logout.php" style="color: #ef4444;">Logout</a>
            </div>
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

        <div class="section-title">
            <h3>Recent Bookings</h3>
            <hr>
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
                    <td><span class="badge badge-<?php echo htmlspecialchars(strtolower(explode(' ', $b['tier'])[0])); ?>"><?php echo htmlspecialchars($b['tier']); ?></span></td>
                    <td style="text-align: center;"><?php echo (int)$b['quantity']; ?></td>
                    <td>BDT <?php echo number_format((float)$b['amount'], 2); ?></td>
                    <td style="font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($b['txnid']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($bookings)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">No bookings found yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="section-title">
            <h3>Admin User Management</h3>
            <hr>
        </div>

        <div class="mgmt-grid">
            <div class="form-card">
                <h3>Add New Administrator</h3>
                <form method="POST" class="inline-form">
                    <div style="display:flex; flex-direction:column; gap:5px;">
                        <label style="font-size:0.8rem; color:#94a3b8;">Username</label>
                        <input type="text" name="new_username" placeholder="johndoe" required>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:5px;">
                        <label style="font-size:0.8rem; color:#94a3b8;">Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <button type="submit" name="add_user" class="btn-small">Create User</button>
                </form>
            </div>

            <div class="form-card">
                <h3>Current Admins</h3>
                <table style="background: transparent; box-shadow: none; margin: 0;">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($admins as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['username']); ?></td>
                            <td>
                                <?php if($a['username'] !== $_SESSION['admin_user']): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="delete_user" value="<?php echo htmlspecialchars($a['username']); ?>">
                                    <button type="submit" class="btn-small btn-danger" onclick="return confirm('Full delete user?');">Delete</button>
                                </form>
                                <?php else: ?>
                                <span style="font-size:0.8rem; color:#64748b;">(You)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
