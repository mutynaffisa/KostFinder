<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') { header("Location: ../auth/login.php"); exit; }

$owner_id = $_SESSION['user_id'];
$kost_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Proses Update Data
if (isset($_POST['update_kost'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = (int)$_POST['price'];
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    mysqli_query($conn, "UPDATE kosts SET name='$name', price='$price', location='$location', lifestyle_category='$category' WHERE id=$kost_id AND owner_id=$owner_id");
    header("Location: owner_dashboard.php");
    exit;
}

$kost = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kosts WHERE id=$kost_id AND owner_id=$owner_id"));
if (!$kost) { die("Kost tidak ditemukan atau bukan milikmu."); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Edit Property | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fdfaf9; }</style>
</head>
<body class="p-10">
    <div class="max-w-2xl mx-auto bg-white p-10 rounded-[3rem] shadow-xl border border-red-50">
        <h2 class="text-3xl font-black text-slate-800 mb-8">Edit <span class="text-[#FF6B6B]">Property.</span></h2>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-2">Property Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($kost['name']) ?>" class="w-full bg-slate-50 p-4 rounded-2xl outline-none font-bold text-slate-700" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-2">Price per Month (Rp)</label>
                <input type="number" name="price" value="<?= $kost['price'] ?>" class="w-full bg-slate-50 p-4 rounded-2xl outline-none font-bold text-slate-700" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-2">Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($kost['location']) ?>" class="w-full bg-slate-50 p-4 rounded-2xl outline-none font-bold text-slate-700" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-500 mb-2">Category</label>
                <select name="category" class="w-full bg-slate-50 p-4 rounded-2xl outline-none font-bold text-slate-700">
                    <option value="quiet" <?= $kost['lifestyle_category'] == 'quiet' ? 'selected' : '' ?>>🤫 Quiet Zone</option>
                    <option value="creative" <?= $kost['lifestyle_category'] == 'creative' ? 'selected' : '' ?>>🎨 Creative Hub</option>
                    <option value="social" <?= $kost['lifestyle_category'] == 'social' ? 'selected' : '' ?>>🤝 Social Coliving</option>
                    <option value="budget" <?= $kost['lifestyle_category'] == 'budget' ? 'selected' : '' ?>>💰 Budget Saver</option>
                </select>
            </div>
            
            <div class="flex gap-4 pt-4 border-t border-slate-100">
                <a href="owner_dashboard.php" class="flex-1 text-center bg-slate-100 text-slate-500 py-4 rounded-2xl font-black hover:bg-slate-200 transition-all">CANCEL</a>
                <button type="submit" name="update_kost" class="flex-1 bg-[#FF6B6B] text-white py-4 rounded-2xl font-black shadow-lg hover:scale-[0.98] transition-all">SAVE CHANGES</button>
            </div>
        </form>
    </div>
</body>
</html>