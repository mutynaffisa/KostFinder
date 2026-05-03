<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }

$user_name = $_SESSION['username'];

// Logic Search & Category
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$cat = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT * FROM kosts WHERE status = 'approved'";
if ($q) $sql .= " AND (name LIKE '%$q%' OR location LIKE '%$q%')";
if ($cat) $sql .= " AND lifestyle_category = '$cat'";
$sql .= " ORDER BY is_promoted DESC, created_at DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/><title>KosFinder - Discover</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8f9fb; }</style>
</head>
<body class="pb-24">

<header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 p-6 flex justify-between items-center border-b border-gray-100">
    <div class="text-2xl font-black text-[#ff6b6b]">KosFinder</div>
    <div class="flex items-center gap-4 italic text-sm">Hi, <?= $user_name ?>! <a href="../auth/logout.php" class="text-red-400 not-italic font-bold">Logout</a></div>
</header>

<main class="max-w-7xl mx-auto p-6 space-y-10">
    <form class="relative group">
        <span class="material-symbols-outlined absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#ff6b6b]">search</span>
        <input type="text" name="q" value="<?= $q ?>" placeholder="Where do you want to live?" class="w-full p-6 pl-14 bg-white rounded-[2rem] shadow-sm border-none focus:ring-2 focus:ring-[#ff6b6b] outline-none text-lg">
    </form>

    <div class="flex gap-4 overflow-x-auto pb-4">
        <?php 
        $categories = ['quiet' => 'Quiet', 'creative' => 'Creative', 'social' => 'Social', 'budget' => 'Budget'];
        foreach($categories as $key => $val): ?>
            <a href="?category=<?= $key ?>" class="px-8 py-4 rounded-2xl font-bold whitespace-nowrap transition-all <?= $cat === $key ? 'bg-[#ff6b6b] text-white shadow-lg' : 'bg-white text-gray-500 hover:bg-gray-50' ?>">
                <?= $val ?>
            </a>
        <?php endforeach; ?>
        <?php if($cat): ?> <a href="user_dashboard.php" class="px-8 py-4 text-red-500 font-bold">Reset</a> <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($k = mysqli_fetch_assoc($result)): ?>
            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-xl transition-all group border border-gray-50">
                <div class="relative h-64">
                    <img src="../<?= $k['thumbnail'] ?>" class="w-full h-full object-cover group-hover:scale-105 transition-all">
                    <?php if($k['is_promoted']): ?>
                        <div class="absolute top-4 left-4 bg-orange-400 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase">PROMOTED</div>
                    <?php endif; ?>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-1"><?= $k['name'] ?></h3>
                    <p class="text-gray-400 text-sm flex items-center gap-1 mb-4 italic"><span class="material-symbols-outlined text-sm">location_on</span> <?= $k['location'] ?></p>
                    <div class="flex justify-between items-center">
                        <p class="text-[#ff6b6b] font-black text-xl">Rp <?= number_format($k['price'], 0, ',', '.') ?><span class="text-xs text-gray-400 font-normal">/mo</span></p>
                        <button class="bg-gray-100 p-3 rounded-2xl hover:bg-[#ff6b6b] hover:text-white transition-all"><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="col-span-full text-center text-gray-400 italic py-20">No properties found matching your search.</p>
        <?php endif; ?>
    </div>
</main>

</body>
</html>