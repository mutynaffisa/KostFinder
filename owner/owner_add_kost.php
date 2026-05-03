<?php
session_start();
include '../db.php';
if ($_SESSION['role'] !== 'owner') { header("Location: ../auth/login.php"); exit; }

$owner_name = $_SESSION['username'] ?? 'Owner';
$msg = "";

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = (float)$_POST['price'];
    $loc = mysqli_real_escape_string($conn, $_POST['location']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $cat = $_POST['category'];
    $owner_id = $_SESSION['user_id'];
    
    // Tangkap array fasilitas dari checkbox, gabungkan jadi string
    $facilities = isset($_POST['facilities']) ? implode(', ', $_POST['facilities']) : '';

    // --- LOGIKA UPLOAD PINTAR ---
    $target_dir = "../uploads/";
    
    // 1. Cek apakah folder uploads sudah ada? Kalau belum, otomatis bikin!
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES["thumbnail"]["name"]);
    $target_file = $target_dir . $file_name;
    $db_path = "uploads/" . $file_name;

    // 2. Cek apakah ada error dari file yang diupload (misal: kebesaran)
    if ($_FILES['thumbnail']['error'] === 0) {
        if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_file)) {
            // Jangan lupa masukkan $facilities ke dalam query
            $query = "INSERT INTO kosts (owner_id, name, price, location, description, facilities, thumbnail, lifestyle_category, status) 
                      VALUES ('$owner_id', '$name', '$price', '$loc', '$desc', '$facilities', '$db_path', '$cat', 'pending')";
            
            if(mysqli_query($conn, $query)){
                $msg = "success";
            } else {
                $msg = "error"; // Error dari query database
            }
        } else {
            $msg = "upload_failed"; // Error gagal mindahin file (masalah permission server)
        }
    } else {
        $msg = "upload_failed"; // Error file terlalu besar (melebihi limit php.ini) atau corrupt
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Owner Studio | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #fdfaf9;
            background-image: radial-gradient(at 100% 0%, rgba(255,107,107,0.1) 0px, transparent 50%), radial-gradient(at 0% 100%, rgba(255,107,107,0.05) 0px, transparent 50%);
        }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.8); }
        .coral-shadow { box-shadow: 0 30px 60px -15px rgba(255,107,107,0.15); }
        input[type="text"], input[type="number"], select, textarea {
            background: #f8fafc; border: 2px solid transparent; transition: all 0.3s ease;
        }
        input:focus, select:focus, textarea:focus {
            background: white; border-color: #FF6B6B; outline: none; box-shadow: 0 10px 20px -5px rgba(255,107,107,0.1);
        }
    </style>
</head>
<body class="p-4 md:p-8 flex flex-col items-center min-h-screen">
    
    <div class="w-full max-w-5xl">
        <header class="flex justify-between items-center mb-10 bg-white/50 backdrop-blur-md p-4 px-8 rounded-full border border-white coral-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#FF6B6B] rounded-xl flex items-center justify-center text-white shadow-lg shadow-red-200">
                    <span class="material-symbols-rounded">storefront</span>
                </div>
                <h1 class="text-2xl font-[800] text-slate-800 tracking-tight hidden sm:block">Owner Studio</h1>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-right hidden md:block">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Partner</p>
                    <p class="text-sm font-bold text-slate-700"><?= htmlspecialchars($owner_name) ?></p>
                </div>
                <a href="../auth/logout.php" class="bg-red-50 text-[#FF6B6B] px-5 py-2 rounded-xl font-bold hover:bg-[#FF6B6B] hover:text-white transition-all text-sm">LOGOUT</a>
            </div>
        </header>

        <div class="glass-card p-8 md:p-12 rounded-[3rem] coral-shadow relative overflow-hidden">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-gradient-to-br from-red-100 to-orange-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

            <div class="mb-10">
                <h2 class="text-3xl font-black text-slate-800 mb-2">Publish New Property 🚀</h2>
                <p class="text-slate-500 font-medium">Fill in the details below to list your space on KosFinder.</p>
            </div>
            
            <?php if($msg === 'success'): ?>
                <div class="bg-green-50 border-2 border-green-100 text-green-600 p-6 rounded-3xl mb-10 font-bold flex items-center gap-4 animate-bounce">
                    <span class="material-symbols-rounded text-3xl">check_circle</span>
                    <div>
                        <p class="text-lg">Property Submitted!</p>
                        <p class="text-sm font-medium text-green-500">Your listing is now pending Admin verification.</p>
                    </div>
                </div>
            <?php elseif($msg === 'error' || $msg === 'upload_failed'): ?>
                <div class="bg-red-50 border-2 border-red-100 text-red-600 p-6 rounded-3xl mb-10 font-bold flex items-center gap-4">
                    <span class="material-symbols-rounded text-3xl">error</span>
                    Failed to submit. Please check your data and image size.
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-10 relative z-10">
                
                <div class="bg-white/60 p-8 rounded-[2.5rem] border border-slate-100">
                    <h3 class="text-sm font-black text-slate-300 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="material-symbols-rounded text-[#FF6B6B]">info</span> Basic Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="text-xs font-black text-slate-700 uppercase tracking-wider ml-2 mb-2 block">Property Name</label>
                            <input type="text" name="name" required placeholder="e.g. Lavender Creative Suite" class="w-full p-4 rounded-2xl font-bold text-slate-700 placeholder:text-slate-300">
                        </div>
                        <div>
                            <label class="text-xs font-black text-slate-700 uppercase tracking-wider ml-2 mb-2 block">Monthly Price (Rp)</label>
                            <input type="number" name="price" required placeholder="1500000" class="w-full p-4 rounded-2xl font-bold text-slate-700 placeholder:text-slate-300">
                        </div>
                        <div>
                            <label class="text-xs font-black text-slate-700 uppercase tracking-wider ml-2 mb-2 block">Vibe Category</label>
                            <select name="category" required class="w-full p-4 rounded-2xl font-bold text-slate-600 appearance-none cursor-pointer">
                                <option value="quiet">🤫 Quiet Study</option>
                                <option value="creative">🎨 Creative Studio</option>
                                <option value="social">🤝 Social Space</option>
                                <option value="budget">💰 Economic Budget</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-black text-slate-700 uppercase tracking-wider ml-2 mb-2 block">Full Address</label>
                            <input type="text" name="location" required placeholder="Jatiwangi, Majalengka..." class="w-full p-4 rounded-2xl font-bold text-slate-700 placeholder:text-slate-300">
                        </div>
                    </div>
                </div>

                <div class="bg-white/60 p-8 rounded-[2.5rem] border border-slate-100">
                    <h3 class="text-sm font-black text-slate-300 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="material-symbols-rounded text-[#FF6B6B]">star</span> Amenities & Facilities
                    </h3>
                    <p class="text-sm text-slate-500 font-medium mb-4">Select all that apply to help users filter your property.</p>
                    
                    <div class="flex flex-wrap gap-3">
                        <?php 
                        $fcl = [
                            'WiFi' => '📶 Fast WiFi', 'AC' => '❄️ AC', 'Kamar Mandi Dalam' => '🚿 Private Bath', 
                            'Dapur Bersama' => '🍳 Shared Kitchen', 'Parkir Motor' => '🏍️ Parking', 
                            'Listrik Termasuk' => '⚡ Free Electricity', 'CCTV' => '📹 CCTV Security'
                        ];
                        foreach($fcl as $val => $label): ?>
                        <label class="cursor-pointer">
                            <input type="checkbox" name="facilities[]" value="<?= $val ?>" class="peer sr-only">
                            <div class="px-5 py-3 rounded-2xl bg-slate-50 border-2 border-transparent text-slate-500 font-bold peer-checked:bg-[#FF6B6B] peer-checked:text-white peer-checked:border-red-400 hover:bg-slate-100 transition-all shadow-sm flex items-center gap-2">
                                <?= $label ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white/60 p-8 rounded-[2.5rem] border border-slate-100">
                    <h3 class="text-sm font-black text-slate-300 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="material-symbols-rounded text-[#FF6B6B]">mms</span> Media & Description
                    </h3>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="text-xs font-black text-slate-700 uppercase tracking-wider ml-2 mb-2 block">Cover Photo</label>
                            
                            <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-[2rem] p-10 text-center relative hover:border-[#FF6B6B] hover:bg-red-50/50 transition-all group cursor-pointer">
                                <input type="file" name="thumbnail" required accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                onchange="
                                    document.getElementById('file-name').innerText = this.files[0].name; 
                                    document.getElementById('upload-icon').innerText = 'check_circle'; 
                                    document.getElementById('upload-icon').classList.replace('text-[#FF6B6B]', 'text-green-500');
                                ">
                                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm group-hover:scale-110 transition-transform">
                                    <span id="upload-icon" class="material-symbols-rounded text-[#FF6B6B] text-3xl">add_photo_alternate</span>
                                </div>
                                <p id="file-name" class="text-sm font-black text-slate-600 mb-1">Upload Property Image</p>
                                <p class="text-xs font-medium text-slate-400">PNG, JPG up to 5MB</p>
                            </div>
                            </div>
                        <div>
                            <label class="text-xs font-black text-slate-700 uppercase tracking-wider ml-2 mb-2 block">Detailed Description</label>
                            <textarea name="description" rows="5" required placeholder="Tell potential renters what makes your space special..." class="w-full p-5 rounded-2xl font-bold text-slate-700 placeholder:text-slate-300 resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" name="submit" class="w-full bg-slate-900 text-white p-6 rounded-[2rem] font-black text-xl shadow-2xl shadow-slate-200 hover:bg-[#FF6B6B] hover:shadow-red-200 transition-all transform hover:-translate-y-1 flex justify-center items-center gap-3">
                    <span class="material-symbols-rounded">publish</span> PUBLISH LISTING
                </button>
            </form>
        </div>
    </div>
</body>
</html>