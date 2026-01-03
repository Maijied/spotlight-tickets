<?php
/**
 * Advanced Admin Dashboard with Search, Filters, Export, and Check-in System
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
        $new_password = $_POST['new_password'];
        
        // Check if exists
        $exists = false;
        foreach($admins as $a) { if($a['username'] === $new_user) $exists = true; }

        if (!$exists && !empty($new_user) && !empty($new_password)) {
            Database::saveAdmin($new_user, password_hash($new_password, PASSWORD_DEFAULT));
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

    // Update Settings
    if (isset($_POST['update_settings'])) {
        $new_settings = [
            'event_name' => $_POST['event_name'],
            'event_date_time' => $_POST['event_date_time'],
            'event_location' => $_POST['event_location'],
            'capacities' => [
                'regular' => (int)$_POST['cap_regular'],
                'vip' => (int)$_POST['cap_vip'],
                'front' => (int)$_POST['cap_front']
            ]
        ];
        Database::saveSettings($new_settings);
        header('Location: admin.php?success=settings');
        exit;
    }
}

$bookings = Database::getBookings();
// Sort by date descending
usort($bookings, function($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});

// Calculate Stats
$totalTickets = array_sum(array_column($bookings, 'quantity'));
$occupationRate = (TOTAL_CAPACITY > 0) ? min(100, round(($totalTickets / TOTAL_CAPACITY) * 100)) : 0;

// Category Wise Stats
$catTickets = [
    'regular' => 0,
    'vip' => 0,
    'front' => 0
];
foreach ($bookings as $b) {
    if (stripos($b['tier'], 'regular') !== false) $catTickets['regular'] += $b['quantity'];
    elseif (stripos($b['tier'], 'vip') !== false) $catTickets['vip'] += $b['quantity'];
    elseif (stripos($b['tier'], 'front') !== false) $catTickets['front'] += $b['quantity'];
}

$totalRevenue = array_sum(array_column($bookings, 'amount'));
$todaySales = 0;
$today = date('Y-m-d');
foreach ($bookings as $b) {
    if (date('Y-m-d', strtotime($b['created_at'])) === $today) {
        $todaySales += $b['amount'];
    }
}

$tierCounts = array_count_values(array_column($bookings, 'tier'));
arsort($tierCounts);
$popularTier = !empty($tierCounts) ? array_key_first($tierCounts) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo EVENT_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0f172a;
            --card-bg: #1e293b;
            --primary: #8b5cf6;
            --primary-hover: #7c3aed;
            --text: #f8fafc;
            --text-dim: #94a3b8;
            --border: #334155;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        body { background: var(--bg); color: var(--text); font-family: 'Outfit', sans-serif; padding: 40px; margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        h1 { color: var(--primary); margin: 0; font-size: 2rem; }
        
        .header-actions { display: flex; align-items: center; gap: 20px; }
        .nav-links a { color: var(--text-dim); text-decoration: none; font-weight: 500; transition: 0.3s; }
        .nav-links a:hover { color: var(--text); }
        .btn-logout { color: var(--danger) !important; }

        /* Stats Cards */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--card-bg); padding: 25px; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .stat-card .label { color: var(--text-dim); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card .value { font-size: 1.8rem; font-weight: 600; margin-top: 10px; color: var(--warning); }
        .stat-card .icon { float: right; font-size: 2rem; color: var(--primary); opacity: 0.3; }

        /* Capacity Progress Bar */
        .capacity-bar { height: 8px; background: var(--bg); border-radius: 4px; margin-top: 15px; overflow: hidden; position: relative; }
        .capacity-fill { height: 100%; background: linear-gradient(90deg, var(--primary), var(--success)); border-radius: 4px; transition: width 0.5s ease; }

        /* Filters & Search */
        .toolbar { background: var(--card-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; }
        .search-box { position: relative; flex-grow: 1; min-width: 300px; }
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-dim); }
        .search-box input { width: 100%; padding: 12px 12px 12px 45px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: #fff; box-sizing: border-box; }
        
        .filters { display: flex; gap: 15px; }
        .filters select { padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: #fff; cursor: pointer; }
        
        .btn-export { padding: 12px 20px; background: var(--success); border: none; border-radius: 8px; color: #fff; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-export:hover { background: #059669; transform: translateY(-2px); }

        /* Table Styles */
        .table-container { background: var(--card-bg); border-radius: 16px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 18px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: rgba(255,255,255,0.03); color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .badge { padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
        .badge-vip { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
        .badge-premium { background: rgba(236, 72, 153, 0.15); color: #f472b6; border: 1px solid rgba(236,72,153,0.3); }
        .badge-regular { background: rgba(139, 92, 246, 0.15); color: #a78bfa; border: 1px solid rgba(139,92,246,0.3); }

        .status-tag { display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 500; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; }
        .status-pending { color: var(--warning); }
        .status-pending .status-dot { background: var(--warning); box-shadow: 0 0 10px var(--warning); }
        .status-confirmed { color: var(--primary); }
        .status-confirmed .status-dot { background: var(--primary); box-shadow: 0 0 10px var(--primary); }
        .status-checked-in { color: var(--success); }
        .status-checked-in .status-dot { background: var(--success); box-shadow: 0 0 10px var(--success); }

        .checkin-btn { padding: 6px 10px; background: var(--primary); border: none; border-radius: 4px; color: #fff; font-size: 0.75rem; cursor: pointer; transition: 0.2s; }
        .checkin-btn:hover { background: var(--primary-hover); }
        .checkin-btn.active { background: var(--success); }

        /* User Management */
        .section-title { margin-top: 60px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; }
        .section-title h2 { margin: 0; color: var(--primary); font-size: 1.5rem; }
        .section-title hr { flex-grow: 1; border: 0; border-top: 1px solid var(--border); }

        .mgmt-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 60px; }
        .form-card { background: var(--card-bg); padding: 30px; border-radius: 16px; border: 1px solid var(--border); }
        .form-card h3 { margin-top: 0; color: var(--primary); margin-bottom: 25px; }
        
        .inline-form { display: grid; grid-template-columns: 1fr 1fr auto; gap: 20px; align-items: end; }
        .inline-form label { display: block; filter: brightness(0.8); margin-bottom: 8px; font-size: 0.85rem; }
        .inline-form input { width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: #fff; }
        
        .btn-small { padding: 12px 24px; background: var(--primary); border: none; border-radius: 8px; color: #fff; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-small:hover { background: var(--primary-hover); transform: translateY(-2px); }
        .btn-danger { background: var(--danger); }
        .btn-danger:hover { background: #dc2626; }

        @media (max-width: 768px) {
            .toolbar { flex-direction: column; align-items: stretch; }
            .mgmt-grid { grid-template-columns: 1fr; }
            .inline-form { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h1>Sales Dashboard - <?php echo EVENT_NAME; ?></h1>
                <div style="margin-top: 8px; display: flex; align-items: center; gap: 15px; color: var(--text-dim); font-size: 0.9rem;">
                    <span><i class="fas fa-calendar-alt" style="color: var(--primary);"></i> <?php echo EVENT_DATE_TIME; ?></span>
                    <span><i class="fas fa-map-marker-alt" style="color: var(--primary);"></i> <?php echo EVENT_LOCATION; ?></span>
                </div>
            </div>
            <div class="header-actions">
                <a href="../api/export_bookings.php" class="btn-export">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
                <div class="nav-links">
                    <a href="index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Public Site</a>
                    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </header>

        <div class="stats">
            <div class="stat-card">
                <i class="fas fa-ticket-alt icon"></i>
                <div class="label">Tickets Sold</div>
                <div class="value"><?php echo count($bookings); ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-coins icon"></i>
                <div class="label">Total Revenue</div>
                <div class="value">BDT <?php echo number_format($totalRevenue, 2); ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-day icon"></i>
                <div class="label">Today's Sales</div>
                <div class="value" style="color: var(--success);">BDT <?php echo number_format($todaySales, 2); ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users icon"></i>
                <div class="label">Seat Availability</div>
                <div class="value"><?php echo $totalTickets; ?> / <?php echo TOTAL_CAPACITY; ?></div>
                <div class="capacity-bar">
                    <div class="capacity-fill" style="width: <?php echo $occupationRate; ?>%;"></div>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-dim); margin-top: 5px;"><?php echo $occupationRate; ?>% Capacity Filled</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-star icon"></i>
                <div class="label">Popular Tier</div>
                <div class="value" style="color: var(--primary); font-size: 1.2rem; margin-top: 15px;">
                    <?php echo htmlspecialchars($popularTier); ?>
                </div>
            </div>
        </div>

        <div class="section-title">
            <h2>Category-wise Seat Inventory</h2>
            <hr>
        </div>

        <div class="stats">
            <?php foreach(['regular', 'vip', 'front'] as $cat): 
                $cap = $TIER_CAPACITIES[$cat] ?? 100;
                $sold = $catTickets[$cat] ?? 0;
                $rate = ($cap > 0) ? min(100, round(($sold / $cap) * 100)) : 0;
            ?>
            <div class="stat-card">
                <div class="label"><?php echo ucfirst($cat); ?> Capacity</div>
                <div class="value" style="font-size: 1.4rem;"><?php echo $sold; ?> / <?php echo $cap; ?></div>
                <div class="capacity-bar">
                    <div class="capacity-fill" style="width: <?php echo $rate; ?>%; background: <?php echo ($rate > 90) ? 'var(--danger)' : (($rate > 70) ? 'var(--warning)' : 'var(--success)'); ?>;"></div>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-dim); margin-top: 5px;"><?php echo $rate; ?>% Sold</div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="bookingSearch" placeholder="Search by name, email, or TXN ID..." onkeyup="filterTable()">
            </div>
            <div class="filters">
                <select id="tierFilter" onchange="filterTable()">
                    <option value="">All Tiers</option>
                    <option value="Regular">Regular</option>
                    <option value="VIP">VIP</option>
                    <option value="Front">Front Row</option>
                </select>
                <select id="statusFilter" onchange="filterTable()">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="checked-in">Checked-In</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table id="bookingsTable">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Customer Details</th>
                        <th>Tier</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bookings as $b): ?>
                    <tr data-tier="<?php echo htmlspecialchars($b['tier']); ?>" data-status="<?php echo htmlspecialchars($b['status'] ?? 'confirmed'); ?>">
                        <td style="font-size: 0.85rem; color: var(--text-dim);">
                            <?php echo date('M d, Y', strtotime($b['created_at'])); ?><br>
                            <span style="font-size: 0.75rem;"><?php echo date('H:i', strtotime($b['created_at'])); ?></span>
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($b['name']); ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-dim);"><?php echo htmlspecialchars($b['email']); ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-dim);"><?php echo htmlspecialchars($b['phone']); ?></div>
                        </td>
                        <td><span class="badge badge-<?php echo htmlspecialchars(strtolower(explode(' ', $b['tier'])[0])); ?>"><?php echo htmlspecialchars($b['tier']); ?></span></td>
                        <td style="text-align: center; font-weight: 600;"><?php echo (int)$b['quantity']; ?></td>
                        <td style="font-weight: 600;">৳<?php echo number_format((float)$b['amount'], 0); ?></td>
                        <td>
                            <?php $s = $b['status'] ?? 'confirmed'; ?>
                            <span class="status-tag status-<?php echo $s; ?>" id="status-tag-<?php echo $b['txnid']; ?>">
                                <span class="status-dot"></span>
                                <span><?php echo ucfirst($s); ?></span>
                            </span>
                        </td>
                        <td>
                            <button class="checkin-btn <?php echo ($s === 'checked-in') ? 'active' : ''; ?>" 
                                    onclick="toggleCheckIn('<?php echo $b['txnid']; ?>', this)">
                                <i class="fas fa-<?php echo ($s === 'checked-in') ? 'undo' : 'check-circle'; ?>"></i>
                                <span><?php echo ($s === 'checked-in') ? 'Revert' : 'Check-In'; ?></span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($bookings)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 60px; color: var(--text-dim);">
                            <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 15px; display: block; opacity: 0.2;"></i>
                            No bookings found yet.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section-title">
            <h2>System Administrators & Event Settings</h2>
            <hr>
        </div>

        <div class="mgmt-grid">
            <div class="form-card">
                <h3>Event Configuration</h3>
                <form method="POST">
                    <div style="margin-bottom: 20px;">
                        <label>Event Name</label>
                        <input type="text" name="event_name" value="<?php echo htmlspecialchars(EVENT_NAME); ?>" required style="width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: #fff;">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label>Date & Time</label>
                            <input type="text" name="event_date_time" value="<?php echo htmlspecialchars(EVENT_DATE_TIME); ?>" required style="width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: #fff;">
                        </div>
                        <div>
                            <label>Location</label>
                            <input type="text" name="event_location" value="<?php echo htmlspecialchars(EVENT_LOCATION); ?>" required style="width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: #fff;">
                        </div>
                    </div>
                    <h4 style="color: var(--primary); margin-top: 30px;">Tier Capacities</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                        <div>
                            <label>Regular Seats</label>
                            <input type="number" name="cap_regular" value="<?php echo $TIER_CAPACITIES['regular']; ?>" required style="width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: #fff;">
                        </div>
                        <div>
                            <label>VIP Seats</label>
                            <input type="number" name="cap_vip" value="<?php echo $TIER_CAPACITIES['vip']; ?>" required style="width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: #fff;">
                        </div>
                        <div>
                            <label>Front Row</label>
                            <input type="number" name="cap_front" value="<?php echo $TIER_CAPACITIES['front']; ?>" required style="width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: #fff;">
                        </div>
                    </div>
                    <button type="submit" name="update_settings" class="btn-small"><i class="fas fa-save"></i> Save Event Settings</button>
                    <?php if(isset($_GET['success']) && $_GET['success'] === 'settings'): ?>
                        <span style="color: var(--success); margin-left: 15px; font-size: 0.9rem;">Settings updated!</span>
                    <?php endif; ?>
                </form>
            </div>

            <div style="display: flex; flex-direction: column; gap: 30px;">
                <div class="form-card">
                    <h3>Add New Admin</h3>
                    <form method="POST">
                        <div style="margin-bottom: 15px;">
                            <label>Username</label>
                            <input type="text" name="new_username" placeholder="johndoe" required style="width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: #fff;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label>Password</label>
                            <input type="password" name="new_password" required style="width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: #fff;">
                        </div>
                        <button type="submit" name="add_user" class="btn-small"><i class="fas fa-user-plus"></i> Create User</button>
                    </form>
                </div>

                <div class="form-card">
                    <h3>Team</h3>
                    <table style="background: transparent; box-shadow: none; margin: 0;">
                        <tbody>
                            <?php foreach($admins as $a): ?>
                            <tr>
                                <td style="padding: 10px 0; border: none;">
                                    <i class="fas fa-user-circle" style="color: var(--primary);"></i>
                                    <span style="margin-left: 10px;"><?php echo htmlspecialchars($a['username']); ?></span>
                                    <?php if($a['username'] === $_SESSION['admin_user']): ?>
                                    <span style="font-size: 0.75rem; color: var(--primary); margin-left: 5px;">(You)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px 0; border: none; text-align: right;">
                                    <?php if($a['username'] !== $_SESSION['admin_user']): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Remove access for this user?');">
                                        <input type="hidden" name="delete_user" value="<?php echo htmlspecialchars($a['username']); ?>">
                                        <button type="submit" style="background: none; border: none; color: var(--danger); cursor: pointer;">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterTable() {
            const input = document.getElementById("bookingSearch");
            const filter = input.value.toLowerCase();
            const tierFilter = document.getElementById("tierFilter").value.toLowerCase();
            const statusFilter = document.getElementById("statusFilter").value.toLowerCase();
            const table = document.getElementById("bookingsTable");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                const tdText = tr[i].innerText.toLowerCase();
                const tier = tr[i].getAttribute("data-tier").toLowerCase();
                const status = tr[i].getAttribute("data-status").toLowerCase();

                let matchesSearch = tdText.indexOf(filter) > -1;
                let matchesTier = !tierFilter || tier.indexOf(tierFilter) > -1;
                let matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesTier && matchesStatus) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }

        async function toggleCheckIn(txnid, btn) {
            const isCheckedIn = btn.classList.contains('active');
            const newStatus = isCheckedIn ? 'confirmed' : 'checked-in';
            const icon = btn.querySelector('i');
            const span = btn.querySelector('span');
            const statusTag = document.getElementById(`status-tag-${txnid}`);

            btn.disabled = true;
            
            try {
                const response = await fetch('../api/update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `txnid=${txnid}&status=${newStatus}`
                });
                
                const res = await response.json();
                
                if (res.success) {
                    btn.classList.toggle('active');
                    icon.className = isCheckedIn ? 'fas fa-check-circle' : 'fas fa-undo';
                    span.innerText = isCheckedIn ? 'Check-In' : 'Revert';
                    
                    // Update Row Attribute
                    btn.closest('tr').setAttribute('data-status', newStatus);
                    
                    // Update Status Tag UI
                    statusTag.className = `status-tag status-${newStatus}`;
                    statusTag.querySelector('span:last-child').innerText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                } else {
                    alert('Status update failed: ' + (res.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Network error while updating status');
            } finally {
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
