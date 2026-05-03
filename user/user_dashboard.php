<?php
session_start();
include '../db.php';

// Proteksi akses
if (!isset($_SESSION['user_id'])) { 
    header("Location: ../auth/login.php"); 
    exit; 
}

// --- 🔥 DEV FEATURE: QUICK SWITCH ACCOUNT 🔥 ---
if (isset($_GET['quick_switch'])) {
    $target_role = mysqli_real_escape_string($conn, $_GET['quick_switch']);
    $q_switch = mysqli_query($conn, "SELECT * FROM users WHERE role = '$target_role' LIMIT 1");
    
    if($q_switch && mysqli_num_rows($q_switch) > 0) {
        $switched_user = mysqli_fetch_assoc($q_switch);
        $_SESSION['user_id'] = $switched_user['id'];
        $_SESSION['username'] = $switched_user['username'];
        $_SESSION['role'] = $switched_user['role'];
        
        if ($target_role === 'owner') { header("Location: ../owner/owner_dashboard.php"); } 
        elseif ($target_role === 'admin') { header("Location: ../admin/admin_dashboard.php"); }
        exit;
    } else {
        $switch_error = "Belum ada akun dengan role $target_role di database!";
    }
}
// ------------------------------------------------

$user_name = $_SESSION['username'] ?? 'Creative Soul';

// --- AMBIL DATA NAMA AKUN LAIN BUAT MENU SWITCH ---
$q_owner = mysqli_query($conn, "SELECT username FROM users WHERE role = 'owner' LIMIT 1");
$demo_owner = $q_owner ? mysqli_fetch_assoc($q_owner) : null;

$q_admin = mysqli_query($conn, "SELECT username FROM users WHERE role = 'admin' LIMIT 1");
$demo_admin = $q_admin ? mysqli_fetch_assoc($q_admin) : null;

// Tangkap input pencarian
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$loc = isset($_GET['location']) ? mysqli_real_escape_string($conn, $_GET['location']) : '';
$cat = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

$sql = "SELECT * FROM kosts WHERE status = 'approved'";
if ($q) { $sql .= " AND (name LIKE '%$q%' OR description LIKE '%$q%')"; }
if ($loc) { $sql .= " AND location LIKE '%$loc%'"; }
if ($cat) { $sql .= " AND lifestyle_category = '$cat'"; }

$sql .= " ORDER BY is_promoted DESC, created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KosFinder | Explore & Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #fdfaf9;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255,107,107,0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(255,107,107,0.05) 0px, transparent 50%);
        }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.5); }
        .coral-shadow { box-shadow: 0 20px 40px -15px rgba(255,107,107,0.15); }
        .sidebar-item:hover { background: #fff1f1; color: #FF6B6B; transform: translateX(5px); }
        .sidebar-active { background: #FF6B6B; color: white; box-shadow: 0 10px 20px -5px rgba(255,107,107,0.4); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .card-hover:hover { transform: translateY(-8px) scale(1.01); box-shadow: 0 25px 50px -12px rgba(255,107,107,0.25); border-color: #ffe4e4; }
    </style>
</head>
<body class="flex min-h-screen text-slate-800">

    <aside class="hidden xl:flex flex-col w-[300px] p-6 sticky top-0 h-screen z-10">
        <div class="glass-card rounded-[2.5rem] h-full flex flex-col p-8 coral-shadow">
            <div class="flex items-center gap-3 mb-10">
                <div class="w-12 h-12 bg-gradient-to-br from-[#FF6B6B] to-[#ff4757] rounded-2xl flex items-center justify-center text-white shadow-lg shadow-red-200">
                    <span class="material-symbols-rounded text-3xl">real_estate_agent</span>
                </div>
                <span class="text-2xl font-[800] tracking-tight">KosFinder</span>
            </div>

            <nav class="space-y-2 flex-1">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4 mb-4">Discover</p>
                <a href="user_dashboard.php" class="sidebar-active flex items-center gap-4 p-4 rounded-2xl font-bold">
                    <span class="material-symbols-rounded">explore</span> Explore Rooms
                </a>
                <a href="wishlist.php" class="sidebar-item flex items-center justify-between p-4 text-slate-500 rounded-2xl transition-all font-bold">
                    <div class="flex items-center gap-4"><span class="material-symbols-rounded">favorite</span> Wishlist</div>
                </a>
                
                <a href="user_inbox.php" class="sidebar-item flex items-center justify-between p-4 text-slate-500 rounded-2xl transition-all font-bold">
                    <div class="flex items-center gap-4"><span class="material-symbols-rounded">forum</span> Inbox</div>
                    <span class="bg-[#FF6B6B] text-white text-[10px] px-2 py-1 rounded-lg animate-pulse">New</span>
                </a>
                <a href="my_bookings.php" class="sidebar-item flex items-center gap-4 p-4 text-slate-500 rounded-2xl transition-all font-bold">
                    <span class="material-symbols-rounded">receipt_long</span> My Bookings
                </a>

                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4 mt-8 mb-4">Support</p>
                <a href="#" class="sidebar-item flex items-center gap-4 p-4 text-slate-500 rounded-2xl transition-all font-bold">
                    <span class="material-symbols-rounded">support_agent</span> Help Center
                </a>
            </nav>
        </div>
    </aside>

    <main class="flex-1 p-6 xl:p-10 w-full overflow-x-hidden relative z-0">
        
        <?php if(isset($switch_error)): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-2xl mb-6 font-bold flex items-center gap-2 border border-red-100">
                <span class="material-symbols-rounded">error</span> <?= $switch_error ?>
            </div>
        <?php endif; ?>

        <header class="flex flex-col md:flex-row justify-between items-center gap-6 mb-10 relative z-50">
            <div>
                <h1 class="text-4xl font-[800] mb-2 leading-tight">Find the space that <br><span class="text-[#FF6B6B]">sparks your joy. ✨</span></h1>
            </div>
            
            <div class="flex items-center gap-4 glass-card p-2 px-4 rounded-3xl coral-shadow border border-slate-100">
                <button class="p-2 hover:bg-slate-50 rounded-full transition-colors text-slate-400 relative">
                    <span class="material-symbols-rounded">notifications</span>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-[#FF6B6B] rounded-full border border-white"></span>
                </button>
                
                <div class="w-[1px] h-8 bg-slate-100"></div>
                
                <div class="relative group cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Renter Profile</p>
                            <p class="text-sm font-bold text-slate-700 group-hover:text-[#FF6B6B] transition-colors"><?= htmlspecialchars($user_name) ?></p>
                        </div>
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($user_name) ?>" class="w-12 h-12 rounded-[1rem] bg-red-50 border border-red-100 group-hover:ring-4 ring-red-100 transition-all">
                    </div>
                    
                    <div class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-right translate-y-4 group-hover:translate-y-0 z-50 overflow-hidden">
                        <div class="p-2">
                            <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:text-[#FF6B6B] hover:bg-red-50 rounded-xl transition-colors">
                                <span class="material-symbols-rounded text-lg">person</span> My Profile
                            </a>
                            
                            <div class="my-2 border-t border-slate-100 pt-3">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-4 mb-2">Switch Account</p>
                                
                                <?php if($demo_owner): ?>
                                <a href="?quick_switch=owner" class="flex items-center gap-3 px-4 py-2 text-sm font-bold text-slate-600 hover:text-orange-500 hover:bg-orange-50 rounded-xl transition-colors">
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($demo_owner['username']) ?>" class="w-8 h-8 rounded-full bg-slate-100">
                                    <div class="leading-tight text-left">
                                        <p class="text-xs font-bold"><?= htmlspecialchars($demo_owner['username']) ?></p>
                                        <p class="text-[9px] text-slate-400 uppercase tracking-widest">Owner Account</p>
                                    </div>
                                </a>
                                <?php endif; ?>

                                <?php if($demo_admin): ?>
                                <a href="?quick_switch=admin" class="flex items-center gap-3 px-4 py-2 mt-1 text-sm font-bold text-slate-600 hover:text-indigo-500 hover:bg-indigo-50 rounded-xl transition-colors">
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($demo_admin['username']) ?>" class="w-8 h-8 rounded-full bg-slate-100">
                                    <div class="leading-tight text-left">
                                        <p class="text-xs font-bold"><?= htmlspecialchars($demo_admin['username']) ?></p>
                                        <p class="text-[9px] text-slate-400 uppercase tracking-widest">Admin Account</p>
                                    </div>
                                </a>
                                <?php endif; ?>
                            </div>

                            <div class="h-[1px] bg-slate-100 my-2"></div>
                            
                            <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-500 hover:bg-red-50 rounded-xl transition-colors">
                                <span class="material-symbols-rounded text-lg">logout</span> Logout Completely
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <form method="GET" action="user_dashboard.php" class="glass-card p-3 rounded-[2.5rem] coral-shadow mb-10 flex flex-col lg:flex-row gap-2 border border-slate-100 relative z-10">
            <div class="flex-1 flex items-center gap-3 px-6 py-2 bg-slate-50/50 rounded-[2rem] hover:bg-slate-50 transition-colors">
                <span class="material-symbols-rounded text-[#FF6B6B]">search</span>
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search by name, facilities, or vibe..." class="w-full bg-transparent border-none outline-none focus:ring-0 font-bold text-slate-600 placeholder:text-slate-400">
            </div>
            <div class="flex-1 flex items-center gap-3 px-6 py-2 bg-slate-50/50 rounded-[2rem] hover:bg-slate-50 transition-colors">
                <span class="material-symbols-rounded text-[#FF6B6B]">location_on</span>
                <input type="text" name="location" value="<?= htmlspecialchars($loc) ?>" placeholder="e.g. Jatiwangi or near campus..." class="w-full bg-transparent border-none outline-none focus:ring-0 font-bold text-slate-600 placeholder:text-slate-400">
            </div>
            <?php if($cat): ?> <input type="hidden" name="category" value="<?= htmlspecialchars($cat) ?>"> <?php endif; ?>
            <button type="submit" class="bg-gradient-to-r from-[#FF6B6B] to-[#ff4757] text-white px-10 py-4 rounded-[2rem] font-black hover:scale-[0.98] transition-all shadow-xl shadow-red-200 flex items-center gap-2">
                <span class="material-symbols-rounded">manage_search</span> Search
            </button>
        </form>

        <div class="flex items-center justify-between mb-8 relative z-10">
            <div class="flex gap-3 overflow-x-auto no-scrollbar pb-2">
                <a href="user_dashboard.php" class="px-6 py-3 rounded-2xl font-bold transition-all whitespace-nowrap <?= empty($cat) ? 'bg-slate-800 text-white' : 'glass-card text-slate-500 hover:text-[#FF6B6B] hover:bg-white' ?>">🔥 All Spaces</a>
                <?php 
                $cats = ['quiet' => '🤫 Quiet Zone', 'creative' => '🎨 Creative Hub', 'social' => '🤝 Social Coliving', 'budget' => '💰 Budget Saver'];
                foreach($cats as $k => $v): 
                    $link = "?category=$k";
                    if($q) $link .= "&q=" . urlencode($q);
                    if($loc) $link .= "&location=" . urlencode($loc);
                ?>
                    <a href="<?= $link ?>" class="px-6 py-3 rounded-2xl font-bold transition-all whitespace-nowrap <?= $cat === $k ? 'bg-slate-800 text-white shadow-lg' : 'glass-card text-slate-500 hover:text-[#FF6B6B] hover:bg-white' ?>">
                        <?= $v ?>
                    </a>
                <?php endforeach; ?>
                
                <?php if($q || $loc || $cat): ?>
                    <a href="user_dashboard.php" class="px-4 py-3 text-[#FF6B6B] font-bold hover:underline whitespace-nowrap flex items-center gap-1">
                        <span class="material-symbols-rounded text-sm">close</span> Clear
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-8 relative z-0">
            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php while($k = mysqli_fetch_assoc($result)): ?>
                
                <div class="glass-card rounded-[3rem] p-4 card-hover transition-all duration-500 group relative">
                    <div class="relative h-64 mb-5 overflow-hidden rounded-[2.5rem]">
                        <a href="detail_kost.php?id=<?= $k['id'] ?>" class="block w-full h-full">
                            <img src="../<?= htmlspecialchars($k['thumbnail']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        </a>
                        
                        <?php if(isset($k['is_promoted']) && $k['is_promoted']): ?>
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-4 py-2 rounded-xl flex items-center gap-1 shadow-sm border border-white pointer-events-none">
                            <span class="material-symbols-rounded text-orange-400 text-[14px]" style="font-variation-settings: 'FILL' 1">stars</span>
                            <span class="text-[10px] font-black text-slate-800 uppercase tracking-widest">Top Pick</span>
                        </div>
                        <?php endif; ?>

                        <a href="add_wishlist.php?id=<?= $k['id'] ?>" class="absolute top-4 right-4 p-3 bg-white/50 backdrop-blur-md rounded-2xl text-white hover:text-red-500 hover:bg-white transition-all shadow-sm border border-white/50 z-10">
                            <span class="material-symbols-rounded" style="font-variation-settings: 'FILL' 0">favorite</span>
                        </a>
                    </div>

                    <div class="px-3 pb-3">
                        <div class="flex justify-between items-start mb-2">
                            <a href="detail_kost.php?id=<?= $k['id'] ?>" class="hover:text-[#FF6B6B] transition-colors">
                                <h3 class="text-xl font-extrabold text-slate-800 mb-1 line-clamp-1"><?= htmlspecialchars($k['name']) ?></h3>
                            </a>
                            <div class="bg-orange-50 px-2 py-1 rounded-lg text-orange-500 font-black text-xs flex items-center gap-1 border border-orange-100 flex-shrink-0">
                                <span class="material-symbols-rounded text-[12px]" style="font-variation-settings: 'FILL' 1">star</span> 4.9
                            </div>
                        </div>

                        <p class="text-slate-400 text-sm font-bold flex items-center gap-1 mb-5">
                            <span class="material-symbols-rounded text-[16px] text-slate-300">location_on</span> <?= htmlspecialchars($k['location']) ?>
                        </p>

                        <div class="flex justify-between items-center pt-5 border-t border-slate-100/50">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Start From</p>
                                <p class="text-2xl font-black text-[#FF6B6B]">Rp <?= number_format($k['price'], 0, ',', '.') ?><span class="text-sm text-slate-300 font-bold">/mo</span></p>
                            </div>
                            <a href="detail_kost.php?id=<?= $k['id'] ?>" class="w-12 h-12 bg-slate-800 text-white rounded-[1.2rem] flex items-center justify-center hover:bg-[#FF6B6B] hover:shadow-lg hover:shadow-red-200 transition-all">
                                <span class="material-symbols-rounded">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center glass-card rounded-[3rem] border-dashed border-2 border-slate-200">
                    <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-rounded text-[#FF6B6B] text-5xl">location_off</span>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">No Spaces Found</h3>
                    <p class="text-slate-500 font-medium mb-6">We couldn't find any rooms matching your current filters.</p>
                    <a href="user_dashboard.php" class="inline-block bg-slate-800 text-white px-8 py-3 rounded-2xl font-bold hover:bg-[#FF6B6B] transition-colors shadow-lg">Clear All Filters</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>