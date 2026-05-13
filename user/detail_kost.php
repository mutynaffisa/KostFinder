<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }
if (!isset($_GET['id'])) { header("Location: user_dashboard.php"); exit; }

$kost_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$msg = "";

// Ambil data detail kost
$query = "SELECT k.*, u.id as owner_id, u.username as owner_name, u.email as owner_email 
          FROM kosts k JOIN users u ON k.owner_id = u.id 
          WHERE k.id = $kost_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) { 
    echo "<h2 style='text-align:center; margin-top:50px;'>Kost tidak ditemukan!</h2>"; exit; 
}
$kost = mysqli_fetch_assoc($result);

// --- LOGIKA BOOKING KOST ---
if (isset($_POST['book_now'])) {
    $owner_id = $kost['owner_id'];
    // Cek apakah sudah pernah booking dan masih pending
    $check_booking = mysqli_query($conn, "SELECT id FROM bookings WHERE user_id = $user_id AND kost_id = $kost_id AND status = 'pending'");
    
    if(mysqli_num_rows($check_booking) > 0) {
        $msg = "already_booked";
    } else {
        $insert_booking = "INSERT INTO bookings (user_id, kost_id, owner_id, status) VALUES ($user_id, $kost_id, $owner_id, 'pending')";
        if(mysqli_query($conn, $insert_booking)) {
            // SUNTIK NOTIF KE OWNER
            mysqli_query($conn, "INSERT INTO notifications (user_id, title, message) VALUES ($owner_id, 'Booking Request Baru', 'Seseorang telah mengajukan sewa untuk kost kamu!')");
            
            // REDIRECT OTOMATIS KE HALAMAN MY BOOKINGS
            echo "<script>
                    alert('Booking request sent! Menunggu persetujuan Owner.');
                    window.location.href = 'my_bookings.php';
                  </script>";
            exit;
        }
    }
}

// --- LOGIKA TAMBAH REVIEW ---
if (isset($_POST['submit_review'])) {
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    // Cek apakah user ini sudah pernah kasih review di kost ini
    $check_review = mysqli_query($conn, "SELECT id FROM reviews WHERE user_id = $user_id AND kost_id = $kost_id");
    if(mysqli_num_rows($check_review) > 0) {
        $msg = "already_reviewed";
    } else {
        $insert_review = "INSERT INTO reviews (user_id, kost_id, rating, comment) VALUES ($user_id, $kost_id, $rating, '$comment')";
        if(mysqli_query($conn, $insert_review)) {
            // Refresh halaman biar review langsung muncul
            header("Location: detail_kost.php?id=$kost_id");
            exit;
        }
    }
}

// Cek Wishlist
$wishlist_query = mysqli_query($conn, "SELECT id FROM wishlist WHERE user_id = $user_id AND kost_id = $kost_id");
$is_wish = ($wishlist_query && mysqli_num_rows($wishlist_query) > 0);

$reviews_query = mysqli_query($conn, "SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.kost_id = $kost_id ORDER BY r.created_at DESC");
$avg_rating_q = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg FROM reviews WHERE kost_id = $kost_id"));
$avg_rating = round($avg_rating_q['avg'], 1) ?: "0";
$facilities_array = !empty($kost['facilities']) ? explode(', ', $kost['facilities']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title><?= htmlspecialchars($kost['name']) ?> | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fdfaf9; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.5); }
        .coral-shadow { box-shadow: 0 25px 50px -12px rgba(255,107,107,0.2); }
    </style>
</head>
<body class="p-4 lg:p-10">
    <div class="max-w-6xl mx-auto">
        <a href="user_dashboard.php" class="inline-flex items-center gap-2 text-slate-400 hover:text-[#FF6B6B] font-bold mb-8 transition-all">
            <span class="material-symbols-rounded">arrow_back</span> Back to Explore
        </a>

        <?php if($msg === 'already_booked'): ?>
            <div class="bg-orange-50 text-orange-600 p-4 rounded-2xl mb-8 font-bold border border-orange-100 flex items-center gap-2">
                <span class="material-symbols-rounded">info</span> Kamu sudah mengajukan booking untuk kost ini dan sedang diproses.
            </div>
        <?php elseif($msg === 'already_reviewed'): ?>
            <div class="bg-blue-50 text-blue-600 p-4 rounded-2xl mb-8 font-bold border border-blue-100 flex items-center gap-2">
                <span class="material-symbols-rounded">info</span> Kamu sudah memberikan ulasan untuk kost ini sebelumnya.
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- KOLOM KIRI (KONTEN UTAMA) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Gambar Kost -->
                <div class="relative h-[450px] rounded-[3rem] overflow-hidden coral-shadow border border-slate-100 bg-slate-100">
                    <img src="../<?= htmlspecialchars($kost['thumbnail']) ?>" class="w-full h-full object-cover">
                    <a href="add_wishlist.php?id=<?= $kost['id'] ?>" class="absolute top-6 right-6 p-4 glass rounded-2xl <?= $is_wish ? 'text-red-500' : 'text-slate-400' ?> hover:text-red-500 hover:scale-110 transition-all shadow-sm">
                        <span class="material-symbols-rounded" style="font-variation-settings: 'FILL' <?= $is_wish ? 1 : 0 ?>">favorite</span>
                    </a>
                </div>

                <!-- Deskripsi & Fasilitas -->
                <div class="glass p-8 lg:p-10 rounded-[3rem] coral-shadow">
                    <h2 class="text-2xl font-black mb-6 flex items-center gap-2 text-slate-800">
                        <span class="material-symbols-rounded text-[#FF6B6B]">description</span> Property Description
                    </h2>
                    <p class="text-slate-500 leading-relaxed text-lg mb-8"><?= nl2br(htmlspecialchars($kost['description'])) ?></p>
                    
                    <h2 class="text-xl font-black mb-6 flex items-center gap-2 text-slate-800">
                        <span class="material-symbols-rounded text-[#FF6B6B]">star</span> Amenities
                    </h2>
                    <div class="flex flex-wrap gap-3">
                        <?php if (count($facilities_array) > 0): ?>
                            <?php foreach($facilities_array as $fac): ?>
                            <div class="bg-white px-5 py-3 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-2">
                                <span class="material-symbols-rounded text-[#FF6B6B] text-sm">check_circle</span>
                                <span class="text-sm font-bold text-slate-600"><?= htmlspecialchars($fac) ?></span>
                            </div>
                            <?php endforeach; else: ?>
                            <p class="text-slate-400 italic">Fasilitas tidak dicantumkan.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- REVIEWS (Dipindah ke sini agar lebih lebar dan simetris) -->
                <div class="glass p-8 lg:p-10 rounded-[3rem] coral-shadow">
                    <h2 class="text-2xl font-black mb-8 flex items-center gap-2 text-slate-800">
                        <span class="material-symbols-rounded text-[#FF6B6B]">reviews</span> Reviews & Ratings (★ <?= $avg_rating ?>)
                    </h2>
                    
                    <!-- List Ulasan (Berbentuk Grid) -->
                    <div class="mb-10">
                        <?php if(mysqli_num_rows($reviews_query) > 0): ?>
                            <?php mysqli_data_seek($reviews_query, 0); ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php while($rev = mysqli_fetch_assoc($reviews_query)): ?>
                                    <div class="p-5 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-center mb-2">
                                            <p class="font-bold text-slate-800"><?= htmlspecialchars($rev['username']) ?></p>
                                            <div class="flex text-[#FF6B6B] text-sm">
                                                <?php for($i=0; $i<$rev['rating']; $i++) echo "★"; ?>
                                            </div>
                                        </div>
                                        <p class="text-sm text-slate-500 leading-relaxed"><?= htmlspecialchars($rev['comment']) ?></p>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-10 bg-slate-50 rounded-[2rem] border border-slate-100 text-center">
                                <span class="material-symbols-rounded text-slate-300 text-5xl mb-3">rate_review</span>
                                <p class="text-slate-500 font-bold text-lg">Belum ada ulasan.</p>
                                <p class="text-slate-400 text-sm mt-1">Jadilah yang pertama memberi bintang untuk kost ini!</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Form Tulis Ulasan -->
                    <?php if($msg !== 'already_reviewed'): ?>
                    <div class="bg-red-50/50 p-6 md:p-8 rounded-[2rem] border border-red-100">
                        <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                            <span class="material-symbols-rounded text-[#FF6B6B]">edit_square</span> Tulis Pengalamanmu
                        </h3>
                        <form method="POST" class="space-y-4">
                            <div>
                                <select name="rating" required class="w-full bg-white border border-slate-200 p-4 rounded-2xl outline-none focus:border-[#FF6B6B] focus:ring-2 focus:ring-red-100 text-sm font-bold text-slate-600 transition-all shadow-sm">
                                    <option value="" disabled selected>Pilih Rating (1-5 Bintang)</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Sangat Nyaman!</option>
                                    <option value="4">⭐⭐⭐⭐ Bagus & Bersih</option>
                                    <option value="3">⭐⭐⭐ Cukup Oke</option>
                                    <option value="2">⭐⭐ Ada yang Perlu Diperbaiki</option>
                                    <option value="1">⭐ Sangat Mengecewakan</option>
                                </select>
                            </div>
                            <div>
                                <textarea name="comment" required rows="4" class="w-full bg-white border border-slate-200 p-4 rounded-2xl outline-none focus:border-[#FF6B6B] focus:ring-2 focus:ring-red-100 text-sm font-medium text-slate-600 placeholder:text-slate-400 transition-all shadow-sm" placeholder="Ceritakan bagaimana fasilitas, keamanan, dan suasananya..."></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" name="submit_review" class="bg-slate-800 text-white px-8 py-4 rounded-2xl font-black hover:bg-[#FF6B6B] hover:shadow-lg hover:shadow-red-200 transition-all flex items-center gap-2">
                                    Kirim Ulasan <span class="material-symbols-rounded text-sm">send</span>
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- KOLOM KANAN (SIDEBAR STICKY) -->
            <div class="lg:col-span-1">
                <div class="glass p-8 rounded-[3rem] coral-shadow sticky top-10 space-y-8">
                    <div>
                        <h1 class="text-3xl font-black text-slate-800 leading-tight mb-2"><?= htmlspecialchars($kost['name']) ?></h1>
                        <p class="flex items-center gap-1 text-slate-400 font-bold">
                            <span class="material-symbols-rounded text-[#FF6B6B] text-lg">location_on</span> <?= htmlspecialchars($kost['location']) ?>
                        </p>
                    </div>

                    <div class="py-6 border-y border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Monthly Rent</p>
                        <p class="text-4xl font-black text-[#FF6B6B]">Rp <?= number_format($kost['price'],0,',','.') ?><span class="text-sm text-slate-400 font-bold">/mo</span></p>
                    </div>

                    <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-[2rem] border border-slate-100">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($kost['owner_name']) ?>" class="w-14 h-14 rounded-2xl bg-white border border-slate-200 shadow-sm">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Property Owner</p>
                            <p class="font-bold text-slate-700 text-lg"><?= htmlspecialchars($kost['owner_name']) ?></p>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4">
                        <a href="chat.php?owner_id=<?= $kost['owner_id'] ?>&kost_id=<?= $kost['id'] ?>" class="w-full bg-[#FF6B6B] text-white py-5 rounded-[2rem] font-black text-center shadow-xl shadow-red-200 hover:bg-slate-800 hover:shadow-slate-200 transition-all flex items-center justify-center gap-2 transform hover:-translate-y-1">
                            <span class="material-symbols-rounded">chat</span> Chat in Inbox
                        </a>
                        
                        <form method="POST">
                            <button type="submit" name="book_now" class="w-full bg-slate-900 border-2 border-slate-900 text-white py-5 rounded-[2rem] font-black hover:bg-transparent hover:text-slate-900 transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-rounded">assignment</span> Request Booking
                            </button>
                        </form>
                    </div>

                    <div class="glass p-8 rounded-[3rem] coral-shadow mt-8">
                        <h2 class="text-xl font-black mb-6 flex items-center gap-2">
                            <span class="material-symbols-rounded text-[#FF6B6B]">map</span> Location
                        </h2>
                        <div class="rounded-3xl overflow-hidden h-64 bg-slate-100 border border-slate-100">
                            <iframe width="100%" height="100%" frameborder="0" style="border:0" 
                                src="https://maps.google.com/maps?q=<?= urlencode($kost['location']) ?>&t=&z=15&ie=UTF8&iwloc=&output=embed" allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>