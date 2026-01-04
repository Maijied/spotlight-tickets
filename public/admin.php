<?php
/**
 * Professional Admin Dashboard
 * Features: Sidebar navigation, Step-by-step workflow, Advanced filtering, Dynamic Settings
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

session_start();

// --- Auth Check ---
Database::connect();
$admins = Database::getAdmins();

// Handle Login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    foreach ($admins as $u => $p) {
        if ($u === $user && password_verify($pass, $p)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $user;
            header('Location: admin.php');
            exit;
        }
    }
    $error = 'Invalid credentials.';
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login - Admin Portal</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
        <style>
            body { background: #0f172a; color: #f8fafc; font-family: 'Inter', sans-serif; display: flex; height: 100vh; align-items: center; justify-content: center; margin: 0; }
            .login-box { background: #1e293b; padding: 40px; border-radius: 12px; width: 350px; border: 1px solid #334155; }
            h2 { color: #8b5cf6; margin: 0 0 20px; text-align: center; font-weight: 600; }
            input { width: 100%; padding: 12px; margin-bottom: 15px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px; box-sizing: border-box; }
            button { width: 100%; padding: 12px; background: #8b5cf6; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
            button:hover { background: #7c3aed; }
            .error { color: #ef4444; font-size: 0.85rem; text-align: center; margin-bottom: 15px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>Admin Login</h2>
            <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Sign In</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- Fetch Data ---
$bookings = Database::getBookings();
$settings = Database::getSettings();
$SLOTS = $settings['slots'] ?? [];
$admins = Database::getAdmins();

// --- Data Processing (Post Actions) ---
$activeTab = $_GET['tab'] ?? 'dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Settings
    if (isset($_POST['update_event_name'])) {
        Database::updateEventName($_POST['event_name']);
        $activeTab = 'events';
        header("Refresh:0; url=admin.php?tab=events");
    }
    
    // 2. Slots (Add)
    if (isset($_POST['add_slot'])) {
        $datetime = date('Y-m-d H:i:s', strtotime($_POST['slot_time']));
        Database::addEvent(
            $datetime,
            $_POST['slot_location'],
            ['regular' => (int)$_POST['cap_regular'], 'vip' => (int)$_POST['cap_vip'], 'front' => (int)$_POST['cap_front']],
            ['regular' => (int)$_POST['price_regular'], 'vip' => (int)$_POST['price_vip'], 'front' => (int)$_POST['price_front']]
        );
        $activeTab = 'events';
        header("Refresh:0; url=admin.php?tab=events");
    }

    // 3. Slots (Delete)
    if (isset($_POST['delete_slot'])) {
        $idx = (int)$_POST['slot_index'];
        Database::deleteEvent($idx);
        $activeTab = 'events';
        header("Refresh:0; url=admin.php?tab=events");
    }

    // 4. Admins
    if (isset($_POST['add_admin'])) {
        Database::saveAdmin($_POST['new_user'], password_hash($_POST['new_pass'], PASSWORD_DEFAULT));
        $activeTab = 'admins';
        header("Refresh:0; url=admin.php?tab=admins");
    }
    if (isset($_POST['delete_admin'])) {
        Database::deleteAdmin($_POST['del_user']);
        $activeTab = 'admins';
        header("Refresh:0; url=admin.php?tab=admins");
    }

    // 5. Dynamic Offers & Text
    if (isset($_POST['update_ui_text'])) {
        Database::updateSetting('ui_tagline', $_POST['ui_tagline']);
        $activeTab = 'offers';
        header("Refresh:0; url=admin.php?tab=offers");
    }

    // Early Bird
    if (isset($_POST['add_early_bird'])) {
        $rules = $settings['early_bird_rules'] ?? [];
        $rules[$_POST['eb_date']] = (int)$_POST['eb_percent'];
        Database::updateSetting('early_bird_rules', $rules);
        $activeTab = 'offers';
        header("Refresh:0; url=admin.php?tab=offers");
    }
    if (isset($_POST['del_early_bird'])) {
        $rules = $settings['early_bird_rules'] ?? [];
        unset($rules[$_POST['eb_date_key']]);
        Database::updateSetting('early_bird_rules', $rules);
        $activeTab = 'offers';
        header("Refresh:0; url=admin.php?tab=offers");
    }

    // Bundles
    if (isset($_POST['add_bundle'])) {
        $rules = $settings['bundle_rules'] ?? [];
        $rules[(int)$_POST['bd_qty']] = (int)$_POST['bd_percent'];
        Database::updateSetting('bundle_rules', $rules);
        $activeTab = 'offers';
        header("Refresh:0; url=admin.php?tab=offers");
    }
    if (isset($_POST['del_bundle'])) {
        $rules = $settings['bundle_rules'] ?? [];
        unset($rules[$_POST['bd_qty_key']]);
        Database::updateSetting('bundle_rules', $rules);
        $activeTab = 'offers';
        header("Refresh:0; url=admin.php?tab=offers");
    }

    // Promos
    if (isset($_POST['add_promo'])) {
        $rules = $settings['promo_codes'] ?? [];
        $rules[strtoupper($_POST['promo_code'])] = (int)$_POST['promo_percent'];
        Database::updateSetting('promo_codes', $rules);
        $activeTab = 'offers';
        header("Refresh:0; url=admin.php?tab=offers");
    }
    if (isset($_POST['del_promo'])) {
        $rules = $settings['promo_codes'] ?? [];
        unset($rules[$_POST['promo_code_key']]);
        Database::updateSetting('promo_codes', $rules);
        $activeTab = 'offers';
        header("Refresh:0; url=admin.php?tab=offers");
    }
}

// Refresh settings after post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = Database::getSettings();
    $SLOTS = $settings['slots'] ?? [];
}

// Stats Calculation
$pendingBookings = array_filter($bookings, fn($b) => ($b['status'] ?? 'confirmed') === 'pending');
$totalSales = array_sum(array_column($bookings, 'amount'));
$totalTickets = array_sum(array_column($bookings, 'quantity'));
$totalCapacity = 0;
foreach($SLOTS as $s) $totalCapacity += array_sum($s['capacities']);

$todaySales = 0;
$today = date('Y-m-d');
foreach($bookings as $b) {
    if(substr($b['created_at'], 0, 10) === $today) $todaySales += $b['amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siddhartha Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        slate: { 850: '#1e293b', 900: '#0f172a' },
                        primary: '#8b5cf6',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444'
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .nav-item { @apply flex items-center gap-3 px-4 py-3 text-slate-400 rounded-lg transition-colors cursor-pointer hover:bg-slate-800 hover:text-white; }
            .nav-item.active { @apply bg-primary/10 text-primary font-medium; }
            .card { @apply bg-slate-800 rounded-xl border border-slate-700 p-6; }
            .input-dark { @apply w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-primary transition-colors; }
            .btn-primary { @apply px-4 py-2 bg-primary hover:bg-violet-600 text-white rounded-lg font-medium transition-colors; }
            .btn-danger { @apply px-3 py-1 bg-red-500/10 text-red-500 border border-red-500/50 rounded hover:bg-red-500 hover:text-white transition-colors; }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-50 h-screen overflow-hidden flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-850 border-r border-slate-700 flex flex-col shrink-0 transition-all duration-300" id="sidebar">
        <div class="p-6">
            <h1 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                <i class="fas fa-theater-masks text-primary"></i>
                <span class="sidebar-text">Siddhartha</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 sidebar-text">Admin Dashboard v2.0</p>
        </div>

        <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
            <!-- Workflow Steps -->
            <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider sidebar-text">Step-by-Step Workflow</div>
            
            <a onclick="switchTab('dashboard')" id="nav-dashboard" class="nav-item">
                <i class="fas fa-chart-pie w-5"></i> <span class="sidebar-text">Overview</span>
            </a>
            
            <a onclick="switchTab('pending')" id="nav-pending" class="nav-item justify-between">
                <div class="flex items-center gap-3"><i class="fas fa-clock w-5"></i> <span class="sidebar-text">Pending Approval</span></div>
                <?php if(count($pendingBookings)>0): ?>
                    <span class="bg-warning text-slate-900 text-xs font-bold px-2 py-0.5 rounded-full"><?php echo count($pendingBookings); ?></span>
                <?php endif; ?>
            </a>

            <a onclick="switchTab('events')" id="nav-events" class="nav-item">
                <i class="fas fa-cogs w-5"></i> <span class="sidebar-text">Event & Slots</span>
            </a>

            <a onclick="switchTab('offers')" id="nav-offers" class="nav-item">
                <i class="fas fa-tags w-5"></i> <span class="sidebar-text">Offers & Text</span>
            </a>

            <a onclick="switchTab('bookings')" id="nav-bookings" class="nav-item">
                <i class="fas fa-ticket-alt w-5"></i> <span class="sidebar-text">All Bookings</span>
            </a>

            <!-- Divider -->
            <div class="my-4 border-t border-slate-700"></div>

            <a href="scan.php" target="_blank" class="nav-item">
                <i class="fas fa-qrcode w-5"></i> <span class="sidebar-text">QR Scanner</span> <i class="fas fa-external-link-alt text-xs ml-auto opacity-50"></i>
            </a>

            <div class="px-4 py-2 mt-4 text-xs font-semibold text-slate-500 uppercase tracking-wider sidebar-text">System</div>
            
            <a onclick="switchTab('admins')" id="nav-admins" class="nav-item">
                <i class="fas fa-users-cog w-5"></i> <span class="sidebar-text">Administrators</span>
            </a>
            
            <a href="index.php" target="_blank" class="nav-item">
                <i class="fas fa-globe w-5"></i> <span class="sidebar-text">View Live Site</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-700">
            <a href="logout.php" class="flex items-center gap-2 text-red-400 hover:text-red-300 px-4 py-2 transition-colors">
                <i class="fas fa-sign-out-alt"></i> <span class="sidebar-text">Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-auto bg-slate-900 relative">
        <div class="max-w-7xl mx-auto p-8">
            
            <!-- Dashboard View -->
            <div id="view-dashboard" class="view-section hidden">
                <header class="mb-8">
                    <h2 class="text-2xl font-bold">Dashboard Overview</h2>
                    <p class="text-slate-400">Real-time insights and performance metrics.</p>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Stat Cards -->
                    <div class="card">
                        <div class="text-slate-400 text-sm font-medium uppercase">Total Revenue</div>
                        <div class="text-3xl font-bold text-white mt-2">৳<?php echo number_format($totalSales); ?></div>
                        <div class="text-xs text-slate-500 mt-1">Lifetime sales</div>
                    </div>
                    <div class="card">
                        <div class="text-slate-400 text-sm font-medium uppercase">Today's Sales</div>
                        <div class="text-3xl font-bold text-success mt-2">৳<?php echo number_format($todaySales); ?></div>
                        <div class="text-xs text-slate-500 mt-1">For <?php echo date('M d'); ?></div>
                    </div>
                    <div class="card">
                        <div class="text-slate-400 text-sm font-medium uppercase">Tickets Sold</div>
                        <div class="text-3xl font-bold text-white mt-2"><?php echo $totalTickets; ?></div>
                        <div class="text-xs text-slate-500 mt-1">Across all slots</div>
                    </div>
                    <div class="card">
                        <div class="text-slate-400 text-sm font-medium uppercase">Pending Actions</div>
                        <div class="text-3xl font-bold text-warning mt-2"><?php echo count($pendingBookings); ?></div>
                        <div class="text-xs text-slate-500 mt-1">Requires approval</div>
                    </div>
                </div>

                <!-- Capacity Visuals -->
                <div class="card mb-8">
                    <h3 class="text-lg font-bold mb-4">Slot Capacity Status</h3>
                    <div class="space-y-6">
                        <?php foreach($SLOTS as $s): 
                            $thisSlotSold = 0;
                            foreach($bookings as $b) {
                                if(($b['slot_id'] ?? '') == $s['id']) $thisSlotSold += $b['quantity'];
                            }
                            $slotCap = array_sum($s['capacities']);
                            $pct = ($slotCap > 0) ? round(($thisSlotSold/$slotCap)*100) : 0;
                        ?>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="font-medium"><?php echo htmlspecialchars($s['time']); ?></span>
                                <span class="text-sm text-slate-400"><?php echo $thisSlotSold; ?> / <?php echo $slotCap; ?> (<?php echo $pct; ?>%)</span>
                            </div>
                            <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-primary transition-all duration-1000" style="width: <?php echo $pct; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals View -->
            <div id="view-pending" class="view-section hidden">
                <header class="mb-8 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-warning"><i class="fas fa-clock mr-2"></i>Pending Approvals</h2>
                        <p class="text-slate-400">Verify bKash transactions and confirm bookings.</p>
                    </div>
                </header>

                <div class="card overflow-hidden p-0">
                    <?php if(empty($pendingBookings)): ?>
                        <div class="p-12 text-center text-slate-500">
                            <i class="fas fa-check-circle text-4xl mb-4 text-slate-600"></i>
                            <p>All caught up! No pending bookings.</p>
                        </div>
                    <?php else: ?>
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900/50 border-b border-slate-700 text-xs uppercase text-slate-400">
                                    <th class="p-4">Customer</th>
                                    <th class="p-4">Transaction ID</th>
                                    <th class="p-4">Amount</th>
                                    <th class="p-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <?php foreach($pendingBookings as $p): ?>
                                <tr class="hover:bg-slate-700/30 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-white"><?php echo htmlspecialchars($p['full_name']); ?></div>
                                        <div class="text-sm text-slate-400"><?php echo htmlspecialchars($p['phone']); ?></div>
                                    </td>
                                    <td class="p-4">
                                        <code class="bg-slate-900 px-2 py-1 rounded text-warning font-mono"><?php echo htmlspecialchars($p['txnid']); ?></code>
                                    </td>
                                    <td class="p-4 font-bold">
                                        ৳<?php echo number_format($p['amount']); ?>
                                        <div class="text-xs text-slate-500"><?php echo $p['quantity']; ?> tickets</div>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button onclick="updateStatus('<?php echo $p['txnid']; ?>', 'confirmed', this)" 
                                                class="bg-success hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg shadow-emerald-900/20 transition-all">
                                            <i class="fas fa-check mr-1"></i> Approve
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Events & Slots View -->
            <div id="view-events" class="view-section hidden">
                <header class="mb-8">
                    <h2 class="text-2xl font-bold">Event & Slot Settings</h2>
                    <p class="text-slate-400">Manage global event details and show timings.</p>
                </header>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Global Settings -->
                    <div class="lg:col-span-1 space-y-8">
                        <div class="card">
                            <h3 class="text-lg font-bold mb-4">Event Details</h3>
                            <form method="POST" class="space-y-4">
                                <div>
                                    <label class="block text-sm text-slate-400 mb-1">Event Name</label>
                                    <input type="text" name="event_name" value="<?php echo htmlspecialchars($settings['event_name']); ?>" class="input-dark">
                                </div>
                                <button type="submit" name="update_event_name" class="btn-primary w-full">Save Changes</button>
                            </form>
                        </div>

                        <div class="card bg-slate-800/50 border-dashed">
                             <h3 class="text-lg font-bold mb-4">Add New Slot</h3>
                             <form method="POST" class="space-y-3">
                                <input type="text" name="slot_time" placeholder="Date (e.g. 25 Jan 2026 18:30)" required class="input-dark">
                                <input type="text" name="slot_location" placeholder="Location" required class="input-dark">
                                
                                <div class="grid grid-cols-3 gap-2">
                                    <div><label class="text-xs">Reg Qty</label><input type="number" name="cap_regular" value="300" class="input-dark text-xs p-1"></div>
                                    <div><label class="text-xs">VIP Qty</label><input type="number" name="cap_vip" value="100" class="input-dark text-xs p-1"></div>
                                    <div><label class="text-xs">Frt Qty</label><input type="number" name="cap_front" value="100" class="input-dark text-xs p-1"></div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div><label class="text-xs">Reg $</label><input type="number" name="price_regular" value="500" class="input-dark text-xs p-1"></div>
                                    <div><label class="text-xs">VIP $</label><input type="number" name="price_vip" value="1200" class="input-dark text-xs p-1"></div>
                                    <div><label class="text-xs">Frt $</label><input type="number" name="price_front" value="2500" class="input-dark text-xs p-1"></div>
                                </div>

                                <button type="submit" name="add_slot" class="btn-primary w-full mt-2"><i class="fas fa-plus mr-1"></i> Add Slot</button>
                             </form>
                        </div>
                    </div>

                    <!-- Slot List -->
                    <div class="lg:col-span-2 space-y-4">
                        <h3 class="text-lg font-bold">Active Slots</h3>
                        <?php if(empty($SLOTS)): ?>
                            <div class="p-8 text-center border border-slate-700 border-dashed rounded-xl text-slate-500">No slots configured.</div>
                        <?php endif; ?>
                        
                        <?php foreach($SLOTS as $idx => $s): 
                             // Calculate slot-wise revenue and count
                             $sRevenue = 0;
                             $sSold = 0;
                             foreach($bookings as $b) {
                                 if(($b['slot_id'] ?? '') == $s['id']) {
                                     $sRevenue += $b['amount'];
                                     $sSold += $b['quantity'];
                                 }
                             }
                        ?>
                        <div class="card flex justify-between items-start group hover:border-slate-500 transition-colors">
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <h4 class="text-xl font-bold text-white"><?php echo htmlspecialchars($s['time']); ?></h4>
                                    <!-- Slot Report -->
                                    <div class="text-right mr-4">
                                        <div class="text-xs text-slate-400">REVENUE</div>
                                        <div class="font-bold text-success">৳<?php echo number_format($sRevenue); ?></div>
                                        <div class="text-[10px] text-slate-500"><?php echo $sSold; ?> tix</div>
                                    </div>
                                </div>
                                <p class="text-slate-400 text-sm mb-4"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($s['location']); ?></p>
                                
                                <div class="flex gap-4 text-sm mb-3">
                                    <div class="bg-slate-900 px-3 py-1 rounded border border-slate-700">
                                        <span class="text-primary font-bold">Regular</span> 
                                        <span class="text-slate-400 ml-1"><?php echo $s['capacities']['regular']; ?></span>
                                    </div>
                                    <div class="bg-slate-900 px-3 py-1 rounded border border-slate-700">
                                        <span class="text-warning font-bold">VIP</span> 
                                        <span class="text-slate-400 ml-1"><?php echo $s['capacities']['vip']; ?></span>
                                    </div>
                                </div>
                                
                                <a href="../api/export_slot.php?slot_id=<?php echo $s['id']; ?>" class="inline-flex items-center gap-1 text-xs text-primary hover:text-white border border-primary/30 px-2 py-1 rounded hover:bg-primary transition-colors">
                                    <i class="fas fa-file-download"></i> Export Data
                                </a>
                            </div>
                            <form method="POST" onsubmit="return confirm('Delete this slot? Data will remain but slot will be gone.');">
                                <input type="hidden" name="slot_index" value="<?php echo $idx; ?>">
                                <button type="submit" name="delete_slot" class="text-slate-600 hover:text-red-500 p-2 transition-colors"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Offers & Text View -->
            <div id="view-offers" class="view-section hidden">
                <header class="mb-8">
                    <h2 class="text-2xl font-bold">Offers & UI Text</h2>
                    <p class="text-slate-400">Manage discounts, promo codes and website text.</p>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Global Text -->
                    <div class="card h-fit">
                        <h3 class="font-bold mb-4">Website Tagline</h3>
                        <form method="POST" class="flex gap-2">
                            <input type="text" name="ui_tagline" value="<?php echo htmlspecialchars($settings['ui_tagline'] ?? 'এক কালজয়ী নাট্য গাথা'); ?>" class="input-dark">
                            <button type="submit" name="update_ui_text" class="btn-primary">Update</button>
                        </form>
                    </div>

                    <!-- Early Bird & Bundle -->
                    <div class="card h-fit">
                         <h3 class="font-bold mb-4 flex justify-between">Early Bird Rules <span class="text-xs font-normal text-slate-400">Date Limit -> Discount %</span></h3>
                         <ul class="space-y-2 mb-4">
                             <?php foreach(($settings['early_bird_rules'] ?? []) as $date => $pct): ?>
                             <li class="flex justify-between items-center bg-slate-900 p-2 rounded text-sm">
                                 <span>Up to <strong><?php echo $date; ?></strong>: <span class="text-success"><?php echo $pct; ?>% OFF</span></span>
                                 <form method="POST" class="inline">
                                     <input type="hidden" name="eb_date_key" value="<?php echo $date; ?>">
                                     <button type="submit" name="del_early_bird" class="text-slate-500 hover:text-red-500"><i class="fas fa-trash"></i></button>
                                 </form>
                             </li>
                             <?php endforeach; ?>
                         </ul>
                         <form method="POST" class="flex gap-2">
                             <input type="date" name="eb_date" required class="input-dark w-full text-sm">
                             <input type="number" name="eb_percent" placeholder="%" required class="input-dark w-20 text-sm">
                             <button type="submit" name="add_early_bird" class="btn-primary text-sm"><i class="fas fa-plus"></i></button>
                         </form>
                    </div>

                    <div class="card h-fit">
                         <h3 class="font-bold mb-4 flex justify-between">Bundle Rules <span class="text-xs font-normal text-slate-400">Min Qty -> Discount %</span></h3>
                         <ul class="space-y-2 mb-4">
                             <?php
                               $bundles = $settings['bundle_rules'] ?? [];
                               krsort($bundles); // Show higher qty first
                               foreach($bundles as $qty => $pct): 
                             ?>
                             <li class="flex justify-between items-center bg-slate-900 p-2 rounded text-sm">
                                 <span>Buy <strong><?php echo $qty; ?>+</strong>: <span class="text-success"><?php echo $pct; ?>% OFF</span></span>
                                 <form method="POST" class="inline">
                                     <input type="hidden" name="bd_qty_key" value="<?php echo $qty; ?>">
                                     <button type="submit" name="del_bundle" class="text-slate-500 hover:text-red-500"><i class="fas fa-trash"></i></button>
                                 </form>
                             </li>
                             <?php endforeach; ?>
                         </ul>
                         <form method="POST" class="flex gap-2">
                             <input type="number" name="bd_qty" placeholder="Min Qty" required class="input-dark w-full text-sm">
                             <input type="number" name="bd_percent" placeholder="%" required class="input-dark w-20 text-sm">
                             <button type="submit" name="add_bundle" class="btn-primary text-sm"><i class="fas fa-plus"></i></button>
                         </form>
                    </div>

                     <!-- Promo Codes -->
                     <div class="card h-fit">
                         <h3 class="font-bold mb-4 flex justify-between">Promo Codes <span class="text-xs font-normal text-slate-400">Code -> Discount %</span></h3>
                         <ul class="space-y-2 mb-4">
                             <?php foreach(($settings['promo_codes'] ?? []) as $code => $pct): ?>
                             <li class="flex justify-between items-center bg-slate-900 p-2 rounded text-sm">
                                 <span><code class="text-warning"><?php echo $code; ?></code>: <span class="text-success"><?php echo $pct; ?>% OFF</span></span>
                                 <form method="POST" class="inline">
                                     <input type="hidden" name="promo_code_key" value="<?php echo $code; ?>">
                                     <button type="submit" name="del_promo" class="text-slate-500 hover:text-red-500"><i class="fas fa-trash"></i></button>
                                 </form>
                             </li>
                             <?php endforeach; ?>
                         </ul>
                         <form method="POST" class="flex gap-2">
                             <input type="text" name="promo_code" placeholder="Code (e.g. SAVE10)" required class="input-dark w-full text-sm">
                             <input type="number" name="promo_percent" placeholder="%" required class="input-dark w-20 text-sm">
                             <button type="submit" name="add_promo" class="btn-primary text-sm"><i class="fas fa-plus"></i></button>
                         </form>
                    </div>

                </div>
            </div>

            <!-- All Bookings View -->
            <div id="view-bookings" class="view-section hidden">
                <header class="mb-8 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold">All Bookings</h2>
                        <p class="text-slate-400">Search and manage all ticket sales.</p>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search bookings..." class="input-dark w-64">
                         <a href="../api/export_bookings.php" class="btn-primary"><i class="fas fa-download mr-1"></i> Export CSV</a>
                    </div>
                </header>

                <div class="card p-0 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="bookingsTable">
                            <thead>
                                <tr class="bg-slate-900/50 border-b border-slate-700 text-xs uppercase text-slate-400">
                                    <th class="p-4">Date</th>
                                    <th class="p-4">Customer</th>
                                    <th class="p-4">Ticket Info</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <?php foreach($bookings as $b): 
                                    $statusColor = match($b['status'] ?? 'pending') {
                                        'confirmed' => 'text-success bg-success/10',
                                        'pending' => 'text-warning bg-warning/10',
                                        'checked-in' => 'text-blue-400 bg-blue-400/10',
                                        default => 'text-slate-400'
                                    };
                                ?>
                                <tr class="hover:bg-slate-700/30 transition-colors booking-row" data-search="<?php echo strtolower($b['full_name'] . ' ' . $b['phone'] . ' ' . $b['txnid']); ?>">
                                    <td class="p-4 text-sm text-slate-400">
                                        <?php echo date('M d, H:i', strtotime($b['created_at'])); ?>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-white"><?php echo htmlspecialchars($b['full_name']); ?></div>
                                        <div class="text-sm text-slate-500"><?php echo htmlspecialchars($b['phone']); ?></div>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-sm"><span class="text-white font-medium"><?php echo $b['quantity']; ?>x</span> <?php echo ucfirst($b['tier']); ?></div>
                                        <div class="text-xs text-slate-500 font-mono mt-1"><?php echo $b['txnid']; ?></div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded text-xs font-bold uppercase <?php echo $statusColor; ?>">
                                            <?php echo $b['status'] ?? 'pending'; ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <?php if(($b['status']??'') !== 'checked-in'): ?>
                                            <button onclick="updateStatus('<?php echo $b['txnid']; ?>', 'checked-in', this)" class="text-blue-400 hover:bg-blue-400/10 px-3 py-1 rounded border border-blue-400/30 text-xs transition-colors">
                                                Check In
                                            </button>
                                        <?php else: ?>
                                            <span class="text-slate-500 text-xs italic">Checked In</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Admins View -->
            <div id="view-admins" class="view-section hidden">
                 <header class="mb-8">
                    <h2 class="text-2xl font-bold">Administrator Management</h2>
                    <p class="text-slate-400">Manage access to the admin portal.</p>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="card">
                        <h3 class="font-bold mb-4 border-b border-slate-700 pb-2">Existing Admins</h3>
                        <ul class="space-y-3">
                            <?php foreach($admins as $u => $p): ?>
                            <li class="flex justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary"><i class="fas fa-user-shield"></i></div>
                                    <span class="font-medium"><?php echo htmlspecialchars($u); ?></span>
                                </div>
                                <?php if($u !== $_SESSION['admin_user']): ?>
                                <form method="POST" onsubmit="return confirm('Delete user?');">
                                    <input type="hidden" name="del_user" value="<?php echo $u; ?>">
                                    <button type="submit" name="delete_admin" class="text-slate-500 hover:text-red-500 transition-colors"><i class="fas fa-trash"></i></button>
                                </form>
                                <?php else: ?>
                                    <span class="text-xs text-primary bg-primary/10 px-2 py-1 rounded">You</span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="card bg-slate-800/50 border-dashed h-fit">
                        <h3 class="font-bold mb-4">Add New Admin</h3>
                        <form method="POST" class="space-y-4">
                            <input type="text" name="new_user" placeholder="Username" required class="input-dark">
                            <input type="password" name="new_pass" placeholder="Password" required class="input-dark">
                            <button type="submit" name="add_admin" class="btn-primary w-full">Create Account</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        // Tab Handling
        function switchTab(tabId) {
            // Update Menu
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            if(document.getElementById('nav-' + tabId)) {
                document.getElementById('nav-' + tabId).classList.add('active');
            }

            // Update View
            document.querySelectorAll('.view-section').forEach(el => el.classList.add('hidden'));
            if(document.getElementById('view-' + tabId)) {
                document.getElementById('view-' + tabId).classList.remove('hidden');
            }
            
            // Save state
            history.pushState(null, '', '?tab=' + tabId);
        }

        // Initialize from URL
        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab') || 'dashboard';
            // ensure tab exists
            if(document.getElementById('view-' + tab)) {
                switchTab(tab);
            } else {
                switchTab('dashboard');
            }
        });

        // Search Filter
        function filterTable() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.booking-row').forEach(row => {
                const text = row.getAttribute('data-search');
                if(text.includes(query)) row.style.display = '';
                else row.style.display = 'none';
            });
        }

        // Async Status Update
        async function updateStatus(txnid, status, btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('txnid', txnid);
                formData.append('status', status);
                
                const response = await fetch('../api/update_status.php', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                });
                const res = await response.json();
                
                if(res.success) {
                    location.reload(); // Reload to refresh stats and move items
                } else {
                    alert('Error: ' + (res.message || 'Failed'));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch(e) {
                alert('Connection error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
