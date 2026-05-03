<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../auth/login.php"); 
    exit;
}

$owner_id = $_SESSION['user_id'];
$owner_name = $_SESSION['username'] ?? 'Owner';

// --- 🔥 QUICK SWITCH ACCOUNT LOGIC 🔥 ---
if (isset($_GET['quick_switch'])) {
    $target_role = mysqli_real_escape_string($conn, $_GET['quick_switch']);
    $q_switch = mysqli_query($conn, "SELECT * FROM users WHERE role = '$target_role' LIMIT 1");
    
    if($q_switch && mysqli_num_rows($q_switch) > 0) {
        $sw = mysqli_fetch_assoc($q_switch);
        $_SESSION['user_id'] = $sw['id'];
        $_SESSION['username'] = $sw['username'];
        $_SESSION['role'] = $sw['role'];
        
        header("Location: ../" . $target_role . "/" . $target_role . "_dashboard.php");
        exit;
    }
}

// --- LOGIKA HAPUS KOST ---
if (isset($_GET['delete_kost_id'])) {
    $del_id = (int)$_GET['delete_kost_id'];
    mysqli_query($conn, "DELETE FROM kosts WHERE id = $del_id AND owner_id = $owner_id");
    header("Location: owner_dashboard.php");
    exit;
}

// --- LOGIKA AKSI BOOKING (APPROVE/REJECT) ---
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $bid = (int)$_GET['booking_id'];
    $status = ($_GET['action'] === 'confirm') ? 'approved' : 'rejected';
    
    $b_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id FROM bookings WHERE id = $bid"));
    if ($b_data) {
        $target_user = $b_data['user_id'];
        $msg_notif = "Booking kamu telah di-$status oleh Owner!";
        // FIX ERROR TITLE DISINI:
        mysqli_query($conn, "INSERT INTO notifications (user_id, title, message) VALUES ($target_user, 'Status Booking', '$msg_notif')");
    }
    
    mysqli_query($conn, "UPDATE bookings SET status = '$status' WHERE id = $bid");
    header("Location: owner_dashboard.php");
    exit;
}

// --- AMBIL DATA USER LAIN BUAT SWITCH ACCOUNT ---
$demo_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE role = 'user' LIMIT 1"));
$demo_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE role = 'admin' LIMIT 1"));

// --- AMBIL DATA STATISTIK & NOTIF ---
$total_listing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM kosts WHERE owner_id = $owner_id"))['c'];
$new_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bookings WHERE owner_id = $owner_id AND status = 'pending'"))['c'];
$unread_chats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM messages WHERE receiver_id = $owner_id"))['c'];
$notif_query = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $owner_id ORDER BY created_at DESC LIMIT 5");

// --- AMBIL DATA BOOKING MASUK ---
$booking_query = mysqli_query($conn, "SELECT b.*, k.name as kost_name, u.username as renter_name, u.email as renter_email 
    FROM bookings b 
    JOIN kosts k ON b.kost_id = k.id 
    JOIN users u ON b.user_id = u.id 
    WHERE b.owner_id = $owner_id ORDER BY b.created_at DESC");

// --- AMBIL DATA KOST MILIK OWNER INI ---
$my_kosts_query = mysqli_query($conn, "SELECT * FROM kosts WHERE owner_id = $owner_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Owner Dashboard | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fdfaf9; }
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.5); }
        .coral-shadow { box-shadow: 0 20px 40px -15px rgba(255,107,107,0.15); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="hidden xl:flex flex-col w-[300px] p-6 sticky top-0 h-screen z-10">
        <div class="glass rounded-[2.5rem] h-full flex flex-col p-8 coral-shadow">
            <div class="flex items-center gap-3 mb-10">
                <div class="w-12 h-12 bg-gradient-to-br from-[#FF6B6B] to-[#ff4757] rounded-2xl flex items-center justify-center text-white shadow-lg shadow-red-200">
                    <span class="material-symbols-rounded text-3xl">real_estate_agent</span>
                </div>
                <span class="text-2xl font-[800] tracking-tight">KosFinder</span>
            </div>
            <nav class="space-y-2 flex-1">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4 mb-4">Management</p>
                
                <a href="#" class="bg-[#FF6B6B] text-white flex items-center gap-4 p-4 rounded-2xl font-bold shadow-lg shadow-red-100">
                    <span class="material-symbols-rounded">dashboard</span> Dashboard
                </a>
                
                <a href="owner_add_kost.php" class="flex items-center gap-4 p-4 text-slate-500 hover:bg-red-50 hover:text-[#FF6B6B] rounded-2xl font-bold transition-all">
                    <span class="material-symbols-rounded">add_business</span> Add New Kost
                </a>
                
                <a href="owner_inbox.php" class="flex items-center justify-between p-4 text-slate-500 hover:bg-red-50 hover:text-[#FF6B6B] rounded-2xl font-bold transition-all">
                    <div class="flex items-center gap-4"><span class="material-symbols-rounded">forum</span> Messages</div>
                    <?php if($unread_chats > 0): ?>
                        <span class="bg-[#FF6B6B] text-white text-[10px] px-2 py-1 rounded-lg"><?= $unread_chats ?></span>
                    <?php endif; ?>
                </a>

                <a href="../user/user_dashboard.php" class="flex items-center gap-4 p-4 text-slate-500 hover:bg-red-50 hover:text-[#FF6B6B] rounded-2xl font-bold transition-all">
                    <span class="material-symbols-rounded">travel_explore</span> Explore & Reviews
                </a>
            </nav>
        </div>
    </aside>

    <main class="flex-1 p-6 xl:p-10 overflow-x-hidden">
        
        <header class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6 relative z-50">
            <div>
                <h1 class="text-4xl font-black text-slate-800">Owner <span class="text-[#FF6B6B]">Center.</span></h1>
                <p class="text-slate-400 font-medium">Hello, <?= htmlspecialchars($owner_name) ?>! Here's your property overview.</p>
            </div>
            
            <div class="flex items-center gap-4 glass p-2 px-4 rounded-[2rem] coral-shadow">
                
                <a href="../help_center.php" class="p-2.5 hover:bg-slate-50 rounded-full transition-colors text-slate-400 group relative">
                    <span class="material-symbols-rounded">support_agent</span>
                </a>

                <div class="relative group cursor-pointer">
                    <button class="p-2.5 hover:bg-slate-50 rounded-full transition-colors text-slate-400 relative">
                        <span class="material-symbols-rounded">notifications</span>
                        <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-[#FF6B6B] rounded-full border-2 border-white"></span>
                    </button>
                    <div class="absolute right-0 mt-3 w-80 bg-white rounded-[2rem] shadow-2xl border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 p-6">
                        <h4 class="text-sm font-black text-slate-800 mb-4 flex justify-between">Notifications</h4>
                        <div class="space-y-4 max-h-64 overflow-y-auto no-scrollbar">
                            <?php if(mysqli_num_rows($notif_query) > 0): ?>
                                <?php while($n = mysqli_fetch_assoc($notif_query)): ?>
                                    <div class="flex gap-3 pb-3 border-b border-slate-50">
                                        <div class="w-2 h-2 bg-[#FF6B6B] rounded-full mt-1.5 flex-shrink-0"></div>
                                        <div>
                                            <p class="text-xs font-black text-slate-700"><?= htmlspecialchars($n['title']) ?></p>
                                            <p class="text-[11px] text-slate-400 leading-tight"><?= htmlspecialchars($n['message']) ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-xs text-slate-400 italic text-center">No new notifications.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="w-[1px] h-8 bg-slate-100 mx-1"></div>

                <div class="relative group cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Owner Profile</p>
                            <p class="text-sm font-bold text-slate-700 group-hover:text-[#FF6B6B] transition-colors"><?= htmlspecialchars($owner_name) ?></p>
                        </div>
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($owner_name) ?>" class="w-11 h-11 rounded-2xl bg-red-50 border border-red-100">
                    </div>
                    
                    <div class="absolute right-0 mt-3 w-72 bg-white rounded-[2rem] shadow-2xl border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                        <div class="p-3">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 mb-2 mt-2">Switch Account To:</p>
                            
                            <?php if($demo_user): ?>
                            <a href="?quick_switch=user" class="flex items-center gap-4 px-4 py-3 hover:bg-blue-50 rounded-2xl transition-all group/item">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($demo_user['username']) ?>" class="w-10 h-10 rounded-xl bg-blue-100">
                                <div class="text-left">
                                    <p class="text-sm font-bold text-slate-700 group-hover/item:text-blue-600"><?= htmlspecialchars($demo_user['username']) ?></p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">Renter Account</p>
                                </div>
                            </a>
                            <?php endif; ?>

                            <?php if($demo_admin): ?>
                            <a href="?quick_switch=admin" class="flex items-center gap-4 px-4 py-3 mt-1 hover:bg-indigo-50 rounded-2xl transition-all group/item">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($demo_admin['username']) ?>" class="w-10 h-10 rounded-xl bg-indigo-100">
                                <div class="text-left">
                                    <p class="text-sm font-bold text-slate-700 group-hover/item:text-indigo-600"><?= htmlspecialchars($demo_admin['username']) ?></p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">Admin Account</p>
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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12 relative z-0">
            <div class="glass p-8 rounded-[2.5rem] coral-shadow">
                <span class="material-symbols-rounded text-[#FF6B6B] text-4xl mb-4">home</span>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">My Properties</p>
                <h2 class="text-4xl font-black text-slate-800"><?= $total_listing ?></h2>
            </div>
            <div class="bg-[#FF6B6B] p-8 rounded-[2.5rem] shadow-2xl shadow-red-100 text-white">
                <span class="material-symbols-rounded text-white text-4xl mb-4">notification_important</span>
                <p class="text-xs font-black text-white/70 uppercase tracking-widest">Pending Bookings</p>
                <h2 class="text-4xl font-black"><?= $new_bookings ?></h2>
            </div>
            <div class="glass p-8 rounded-[2.5rem] coral-shadow">
                <span class="material-symbols-rounded text-[#FF6B6B] text-4xl mb-4">mail</span>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Inquiries</p>
                <h2 class="text-4xl font-black text-slate-800"><?= $unread_chats ?></h2>
            </div>
        </div>

        <section class="glass rounded-[3rem] p-8 md:p-10 coral-shadow mb-12 relative z-0">
            <h2 class="text-2xl font-black text-slate-800 mb-8 flex items-center gap-2">
                <span class="material-symbols-rounded text-[#FF6B6B]">assignment_turned_in</span> Booking Requests
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="pb-4 px-2">Property</th>
                            <th class="pb-4 px-2">Renter</th>
                            <th class="pb-4 px-2">Status</th>
                            <th class="pb-4 px-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(mysqli_num_rows($booking_query) > 0): ?>
                            <?php while($b = mysqli_fetch_assoc($booking_query)): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-6 px-2">
                                    <p class="font-bold text-slate-700"><?= htmlspecialchars($b['kost_name']) ?></p>
                                    <p class="text-[10px] text-slate-400"><?= date('d M Y', strtotime($b['created_at'])) ?></p>
                                </td>
                                <td class="py-6 px-2">
                                    <p class="font-bold text-slate-700"><?= htmlspecialchars($b['renter_name']) ?></p>
                                    <p class="text-xs text-slate-400"><?= htmlspecialchars($b['renter_email']) ?></p>
                                </td>
                                <td class="py-6 px-2">
                                    <?php 
                                        $style = "bg-orange-100 text-orange-500";
                                        if($b['status'] === 'approved') $style = "bg-green-100 text-green-500";
                                        if($b['status'] === 'rejected') $style = "bg-red-100 text-red-500";
                                    ?>
                                    <span class="<?= $style ?> px-3 py-1 rounded-lg text-[10px] font-black uppercase"><?= $b['status'] ?></span>
                                </td>
                                <td class="py-6 px-2 text-center">
                                    <?php if($b['status'] === 'pending'): ?>
                                        <div class="flex gap-2 justify-center">
                                            <a href="?action=confirm&booking_id=<?= $b['id'] ?>" class="bg-green-500 text-white p-2 rounded-xl hover:scale-110 transition-all shadow-lg shadow-green-100">
                                                <span class="material-symbols-rounded text-sm">check</span>
                                            </a>
                                            <a href="?action=reject&booking_id=<?= $b['id'] ?>" class="bg-red-100 text-red-500 p-2 rounded-xl hover:scale-110 transition-all">
                                                <span class="material-symbols-rounded text-sm">close</span>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-300">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-10 text-center text-slate-400 font-bold italic">No booking requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="glass rounded-[3rem] p-8 md:p-10 coral-shadow relative z-0">
            <h2 class="text-2xl font-black text-slate-800 mb-8 flex items-center gap-2">
                <span class="material-symbols-rounded text-[#FF6B6B]">real_estate_agent</span> My Properties
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if(mysqli_num_rows($my_kosts_query) > 0): ?>
                    <?php while($mk = mysqli_fetch_assoc($my_kosts_query)): ?>
                    <div class="bg-white border border-slate-100 rounded-[2rem] p-4 flex flex-col hover:shadow-xl hover:border-red-100 transition-all">
                        <img src="../<?= htmlspecialchars($mk['thumbnail']) ?>" class="w-full h-40 object-cover rounded-2xl mb-4 bg-slate-50">
                        <h3 class="text-lg font-black text-slate-800 mb-1 truncate"><?= htmlspecialchars($mk['name']) ?></h3>
                        <p class="text-xs text-slate-400 font-bold mb-4 flex items-center gap-1"><span class="material-symbols-rounded text-[14px]">location_on</span> <?= htmlspecialchars($mk['location']) ?></p>
                        
                        <div class="mt-auto flex gap-2">
                            <a href="owner_edit_kost.php?id=<?= $mk['id'] ?>" class="flex-1 bg-slate-100 text-slate-600 text-center py-2.5 rounded-xl font-black text-xs hover:bg-[#FF6B6B] hover:text-white transition-all">EDIT</a>
                            <a href="?delete_kost_id=<?= $mk['id'] ?>" onclick="return confirm('Yakin ingin menghapus kost ini?');" class="bg-red-50 text-red-500 px-4 py-2.5 rounded-xl font-black text-xs hover:bg-red-500 hover:text-white transition-all flex items-center justify-center">
                                <span class="material-symbols-rounded text-[16px]">delete</span>
                            </a>
                        </div>
                        <?php 
                            $status_bg = "bg-orange-50 text-orange-500";
                            if($mk['status'] == 'approved') $status_bg = "bg-green-50 text-green-500";
                            if($mk['status'] == 'declined') $status_bg = "bg-red-50 text-red-500";
                        ?>
                        <div class="mt-3 text-center <?= $status_bg ?> py-1 rounded-lg text-[10px] font-black uppercase">
                            Status: <?= $mk['status'] ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full py-10 text-center">
                        <p class="text-slate-400 font-bold italic mb-4">You haven't added any properties yet.</p>
                        <a href="owner_add_kost.php" class="bg-[#FF6B6B] text-white px-6 py-2.5 rounded-full font-black shadow-lg shadow-red-200">Add Property</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>
</body>
</html>