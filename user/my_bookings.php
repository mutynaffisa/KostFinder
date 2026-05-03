<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }

$user_id = $_SESSION['user_id'];

// Ambil data booking milik user ini
$query = "SELECT b.*, k.name as kost_name, k.location, k.thumbnail, k.price, u.username as owner_name 
          FROM bookings b 
          JOIN kosts k ON b.kost_id = k.id 
          JOIN users u ON b.owner_id = u.id 
          WHERE b.user_id = $user_id ORDER BY b.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>My Bookings | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fdfaf9; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.5); }
        .coral-shadow { box-shadow: 0 20px 40px -15px rgba(255,107,107,0.15); }
    </style>
</head>
<body class="p-4 lg:p-10">
    <div class="max-w-4xl mx-auto">
        <a href="user_dashboard.php" class="inline-flex items-center gap-2 text-slate-400 hover:text-[#FF6B6B] font-bold mb-8 transition-all">
            <span class="material-symbols-rounded">arrow_back</span> Back to Explore
        </a>

        <h1 class="text-3xl font-black text-slate-800 mb-8 flex items-center gap-3">
            <span class="material-symbols-rounded text-[#FF6B6B] text-4xl">receipt_long</span> My Bookings
        </h1>

        <div class="space-y-6">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($b = mysqli_fetch_assoc($result)): ?>
                <div class="glass-card rounded-[2rem] p-4 coral-shadow flex flex-col md:flex-row gap-6 items-center">
                    <img src="../<?= htmlspecialchars($b['thumbnail']) ?>" class="w-full md:w-48 h-32 object-cover rounded-[1.5rem] bg-slate-100">
                    <div class="flex-1 w-full">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-xl font-extrabold text-slate-800"><?= htmlspecialchars($b['kost_name']) ?></h3>
                                <p class="text-slate-400 text-sm font-bold flex items-center gap-1"><span class="material-symbols-rounded text-[16px]">location_on</span> <?= htmlspecialchars($b['location']) ?></p>
                            </div>
                            <?php 
                                $status_bg = "bg-orange-50 text-orange-500 border-orange-100";
                                $status_icon = "pending";
                                if($b['status'] == 'approved') { $status_bg = "bg-green-50 text-green-500 border-green-100"; $status_icon = "check_circle"; }
                                if($b['status'] == 'rejected') { $status_bg = "bg-red-50 text-red-500 border-red-100"; $status_icon = "cancel"; }
                            ?>
                            <span class="<?= $status_bg ?> px-3 py-1 border rounded-xl text-xs font-black uppercase flex items-center gap-1">
                                <span class="material-symbols-rounded text-[14px]"><?= $status_icon ?></span> <?= $b['status'] ?>
                            </span>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center w-full">
                            <p class="text-sm font-bold text-slate-500">Owner: <span class="text-slate-700"><?= htmlspecialchars($b['owner_name']) ?></span></p>
                            <p class="text-lg font-black text-[#FF6B6B]">Rp <?= number_format($b['price'], 0, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-20 glass-card rounded-[3rem] border-dashed border-2 border-slate-200">
                    <span class="material-symbols-rounded text-[#FF6B6B] text-5xl mb-4">inventory_2</span>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">No Bookings Yet</h3>
                    <p class="text-slate-500 font-medium">You haven't requested to book any rooms yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>