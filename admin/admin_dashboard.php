<?php
session_start();
include '../db.php';

// Proteksi Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../auth/login.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'] ?? 'Admin';

// --- 🔥 QUICK SWITCH ACCOUNT LOGIC (Beneran Ganti Orang) 🔥 ---
if (isset($_GET['quick_switch'])) {
    $target_role = mysqli_real_escape_string($conn, $_GET['quick_switch']);
    $q_switch = mysqli_query($conn, "SELECT * FROM users WHERE role = '$target_role' LIMIT 1");
    
    if($q_switch && mysqli_num_rows($q_switch) > 0) {
        $sw = mysqli_fetch_assoc($q_switch);
        $_SESSION['user_id'] = $sw['id'];
        $_SESSION['username'] = $sw['username'];
        $_SESSION['role'] = $sw['role'];
        
        // Lempar ke folder dashboard masing-masing
        header("Location: ../" . $target_role . "/" . $target_role . "_dashboard.php");
        exit;
    }
}

// --- LOGIC ACTION (APPROVE/DECLINE) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = ($_GET['action'] === 'approve') ? 'approved' : 'declined';
    
    $k_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT owner_id, name FROM kosts WHERE id = $id"));
    if ($k_data) {
        $owner_id = $k_data['owner_id'];
        $kost_name = $k_data['name'];
        mysqli_query($conn, "INSERT INTO notifications (user_id, title, message) VALUES ($owner_id, 'Status Verifikasi', 'Kost $kost_name kamu telah di-$status oleh Admin.')");
    }

    mysqli_query($conn, "UPDATE kosts SET status = '$status' WHERE id = $id");
    header("Location: admin_dashboard.php"); exit;
}

// --- AMBIL DATA USER LAIN BUAT SWITCH ACCOUNT ---
$demo_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE role = 'user' LIMIT 1"));
$demo_owner = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE role = 'owner' LIMIT 1"));

// --- STATISTIK ---
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$total_kosts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM kosts"))['c'];
$pending_query = mysqli_query($conn, "SELECT k.*, u.username FROM kosts k JOIN users u ON k.owner_id = u.id WHERE k.status = 'pending'");
$notif_query = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin HQ | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fb; }
        .admin-glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid #eee; }
        .coral-shadow { box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="p-6 md:p-10 flex flex-col gap-10 min-h-screen" style="background-image: linear-gradient(120deg, #fdfbfb 0%, #ebedee 100%);">
    
    <header class="flex flex-col md:flex-row justify-between items-center gap-6 relative z-50">
       <div class="flex items-center gap-4 admin-glass p-2 px-5 rounded-[2rem] coral-shadow">
            
            <button class="p-2.5 hover:bg-slate-50 rounded-full transition-colors text-slate-400 group relative">
                <span class="material-symbols-rounded">support_agent</span>
                <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-all">Laporan</div>
            </button>

            <a href="../user/user_dashboard.php" class="p-2.5 hover:bg-slate-50 rounded-full transition-colors text-slate-400 group relative">
                <span class="material-symbols-rounded">travel_explore</span>
                <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-all whitespace-nowrap">Explore Kost</div>
            </a>
            </div>
            <div>
                <h1 class="text-3xl font-[800] text-slate-800 tracking-tighter leading-none">Admin HQ</h1>
                <p class="text-xs font-bold text-[#FF6B6B] mt-1 uppercase tracking-widest">Main Control Center</p>
            </div>
        </div>
        
        <div class="flex items-center gap-4 admin-glass p-2 px-5 rounded-[2rem] coral-shadow">
            
            <button class="p-2.5 hover:bg-slate-50 rounded-full transition-colors text-slate-400 group relative">
                <span class="material-symbols-rounded">support_agent</span>
                <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-all">Laporan</div>
            </button>

        <div class="relative group cursor-pointer">
                <button class="p-2.5 hover:bg-slate-50 rounded-full transition-colors text-slate-400 relative">
                    <span class="material-symbols-rounded">notifications</span>
                    <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-[#FF6B6B] rounded-full border-2 border-white"></span>
                </button>
                <div class="absolute right-0 mt-3 w-80 bg-white rounded-[2rem] shadow-2xl border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 p-6">
                    <h4 class="text-sm font-black text-slate-800 mb-4 flex justify-between">Notifications <span class="text-[#FF6B6B]">New</span></h4>
                    <div class="space-y-4 max-h-64 overflow-y-auto no-scrollbar">
                        <?php if(mysqli_num_rows($notif_query) > 0): ?>
                            <?php while($n = mysqli_fetch_assoc($notif_query)): ?>
                                <div class="flex gap-3 pb-3 border-b border-slate-50">
                                    <div class="w-2 h-2 bg-[#FF6B6B] rounded-full mt-1.5 flex-shrink-0"></div>
                                    <div>
                                        <p class="text-xs font-black text-slate-700"><?= $n['title'] ?></p>
                                        <p class="text-[11px] text-slate-400 leading-tight"><?= $n['message'] ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-xs text-slate-400 italic text-center">No recent alerts.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="w-[1px] h-8 bg-slate-200 mx-1"></div>

            <div class="relative group cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Admin</p>
                        <p class="text-sm font-bold text-slate-700 group-hover:text-[#FF6B6B] transition-colors"><?= htmlspecialchars($user_name) ?></p>
                    </div>
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($user_name) ?>" class="w-11 h-11 rounded-2xl bg-slate-100 border border-slate-200">
                </div>
                
                <div class="absolute right-0 mt-3 w-72 bg-white rounded-[2rem] shadow-2xl border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                    <div class="p-3">
                        <div class="px-4 py-3 bg-slate-50 rounded-2xl mb-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Account</p>
                            <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($user_name) ?></p>
                        </div>
                        
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 mb-2 mt-4">Switch Account To:</p>
                        
                        <?php if($demo_user): ?>
                        <a href="?quick_switch=user" class="flex items-center gap-4 px-4 py-3 hover:bg-blue-50 rounded-2xl transition-all group/item">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($demo_user['username']) ?>" class="w-10 h-10 rounded-xl bg-blue-100">
                            <div class="text-left">
                                <p class="text-sm font-bold text-slate-700 group-hover/item:text-blue-600"><?= htmlspecialchars($demo_user['username']) ?></p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Renter Account</p>
                            </div>
                        </a>
                        <?php endif; ?>

                        <?php if($demo_owner): ?>
                        <a href="?quick_switch=owner" class="flex items-center gap-4 px-4 py-3 mt-1 hover:bg-orange-50 rounded-2xl transition-all group/item">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($demo_owner['username']) ?>" class="w-10 h-10 rounded-xl bg-orange-100">
                            <div class="text-left">
                                <p class="text-sm font-bold text-slate-700 group-hover/item:text-orange-600"><?= htmlspecialchars($demo_owner['username']) ?></p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Owner Account</p>
                            </div>
                        </a>
                        <?php endif; ?>
                        
                        <div class="h-[1px] bg-slate-100 my-3"></div>
                        <a href="../auth/logout.php" class="flex items-center gap-4 px-4 py-3 text-red-500 font-black text-sm hover:bg-red-50 rounded-2xl transition-all">
                            <span class="material-symbols-rounded">logout</span> Logout Completely
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="admin-glass p-8 rounded-[2.5rem] coral-shadow relative overflow-hidden group">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Total System Kost</p>
            <h2 class="text-4xl font-[900] text-slate-800"><?= $total_kosts ?></h2>
            <span class="material-symbols-rounded text-slate-100 text-8xl absolute -right-4 -bottom-4 group-hover:scale-110 transition-transform">home_work</span>
        </div>
        <div class="bg-[#FF6B6B] p-8 rounded-[2.5rem] shadow-2xl shadow-red-100 text-white relative overflow-hidden group">
            <p class="text-xs font-black opacity-60 uppercase tracking-widest mb-2">Needs Approval</p>
            <h2 class="text-4xl font-[900]"><?= mysqli_num_rows($pending_query) ?></h2>
            <span class="material-symbols-rounded text-white opacity-20 text-8xl absolute -right-4 -bottom-4 group-hover:rotate-12 transition-transform">pending_actions</span>
        </div>
        <div class="bg-indigo-600 p-8 rounded-[2.5rem] text-white shadow-2xl shadow-indigo-100 relative overflow-hidden group">
            <p class="text-xs font-black opacity-60 uppercase tracking-widest mb-2">Total Members</p>
            <h2 class="text-4xl font-[900]"><?= $total_users ?></h2>
            <span class="material-symbols-rounded text-white opacity-20 text-8xl absolute -right-4 -bottom-4">groups</span>
        </div>
        <div class="admin-glass p-8 rounded-[2.5rem] coral-shadow relative overflow-hidden group">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Active Reports</p>
            <h2 class="text-4xl font-[900] text-slate-800">0</h2>
            <span class="material-symbols-rounded text-slate-100 text-8xl absolute -right-4 -bottom-4">report_problem</span>
        </div>
    </div>

    <section class="admin-glass rounded-[3.5rem] p-8 md:p-12 coral-shadow">
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-2xl font-black text-slate-800 border-l-8 border-[#FF6B6B] pl-5 uppercase tracking-tighter">Pending Verifications</h2>
            <div class="bg-red-50 text-[#FF6B6B] px-5 py-2 rounded-2xl text-xs font-black">ACTION REQUIRED</div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <?php if(mysqli_num_rows($pending_query) > 0): ?>
                <?php while($k = mysqli_fetch_assoc($pending_query)): ?>
                <div class="bg-white border border-slate-100 p-7 rounded-[3rem] flex flex-col sm:flex-row gap-6 items-center hover:shadow-xl hover:shadow-slate-100 transition-all group">
                    <div class="relative w-36 h-36 flex-shrink-0">
                        <img src="../<?= htmlspecialchars($k['thumbnail']) ?>" class="w-full h-full rounded-[2.5rem] object-cover bg-slate-50 group-hover:scale-105 transition-transform">
                    </div>
                    <div class="flex-1 w-full text-center sm:text-left">
                        <h3 class="text-xl font-black text-slate-800 mb-1 leading-tight"><?= htmlspecialchars($k['name']) ?></h3>
                        <p class="text-xs font-bold text-slate-400 mb-5 flex items-center gap-1 justify-center sm:justify-start">
                            <span class="material-symbols-rounded text-sm">person</span> Owner: <span class="text-[#FF6B6B]"><?= htmlspecialchars($k['username']) ?></span>
                        </p>
                        <div class="flex gap-3">
                            <a href="?action=approve&id=<?= $k['id'] ?>" class="flex-1 bg-green-500 text-white text-center py-4 rounded-[1.5rem] font-black text-xs shadow-lg shadow-green-100 hover:scale-[0.98] transition-all">APPROVE</a>
                            <a href="?action=decline&id=<?= $k['id'] ?>" class="bg-slate-100 text-slate-400 py-4 px-6 rounded-[1.5rem] font-black text-xs hover:bg-red-50 hover:text-red-500 transition-all">DECLINE</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center flex flex-col items-center">
                    <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mb-5"><span class="material-symbols-rounded text-5xl">task_alt</span></div>
                    <h3 class="text-xl font-black text-slate-400">Database is Clean!</h3>
                    <p class="text-sm text-slate-300 font-bold">No new properties waiting for your approval.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

</body>
</html>