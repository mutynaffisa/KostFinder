<?php
session_start();
include '../db.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }
$user_id = $_SESSION['user_id'];

// Ambil data kost yang di-join dengan tabel wishlist
$sql = "SELECT k.*, w.id as wishlist_id FROM kosts k 
        JOIN wishlist w ON k.id = w.kost_id 
        WHERE w.user_id = $user_id ORDER BY w.created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Wishlist | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fdfaf9; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.5); }
        .coral-shadow { box-shadow: 0 20px 40px -15px rgba(255,107,107,0.15); }
        .card-hover:hover { transform: translateY(-8px) scale(1.01); box-shadow: 0 25px 50px -12px rgba(255,107,107,0.25); }
    </style>
</head>
<body class="p-6 md:p-12 text-slate-800">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex items-center gap-4 mb-10">
            <a href="user_dashboard.php" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-400 hover:text-[#FF6B6B] transition-colors border border-slate-100 shadow-sm">
                <span class="material-symbols-rounded">arrow_back</span>
            </a>
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800">Saved Spaces ❤️</h1>
                <p class="text-slate-400 font-medium">Your personal collection of dream rooms.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-8">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($k = mysqli_fetch_assoc($result)): ?>
                <a href="detail_kost.php?id=<?= $k['id'] ?>" class="block glass-card rounded-[3rem] p-4 card-hover transition-all duration-500 relative">
                    <div class="relative h-64 mb-5 overflow-hidden rounded-[2.5rem]">
                        <img src="../<?= htmlspecialchars($k['thumbnail']) ?>" class="w-full h-full object-cover">
                        <object><a href="add_wishlist.php?id=<?= $k['id'] ?>" class="absolute top-4 right-4 p-3 bg-white/90 backdrop-blur-md rounded-2xl text-red-500 transition-all shadow-sm z-10 hover:bg-red-50">
                            <span class="material-symbols-rounded" style="font-variation-settings: 'FILL' 1">favorite</span>
                        </a></object>
                    </div>
                    <div class="px-3 pb-3">
                        <h3 class="text-xl font-extrabold text-slate-800 mb-1 line-clamp-1"><?= htmlspecialchars($k['name']) ?></h3>
                        <p class="text-slate-400 text-sm font-bold flex items-center gap-1 mb-5">
                            <span class="material-symbols-rounded text-[16px]">location_on</span> <?= htmlspecialchars($k['location']) ?>
                        </p>
                        <p class="text-2xl font-black text-[#FF6B6B]">Rp <?= number_format($k['price'], 0, ',', '.') ?><span class="text-sm text-slate-300">/mo</span></p>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center">
                    <span class="material-symbols-rounded text-slate-200 text-6xl mb-4">heart_broken</span>
                    <h3 class="text-2xl font-black text-slate-800">Your wishlist is empty</h3>
                    <p class="text-slate-500 font-medium mt-2">Start exploring and save your favorite spaces here!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>