<?php
session_start();
include 'db.php'; // Pastikan path db.php bener

if (!isset($_SESSION['user_id'])) { header("Location: auth/login.php"); exit; }

$my_id = $_SESSION['user_id'];
$my_role = $_SESSION['role'];

// --- LOGIKA KIRIM PESAN ---
if (isset($_POST['send_msg'])) {
    $msg = mysqli_real_escape_string($conn, $_POST['message']);
    // Kalau admin yang balas, ambil ID user yang lagi diajak chat dari URL
    $target_user_id = ($my_role === 'admin') ? (int)$_GET['u'] : $my_id;
    
    if(!empty($msg)) {
        mysqli_query($conn, "INSERT INTO support_chats (user_id, sender_id, message) VALUES ($target_user_id, $my_id, '$msg')");
    }
    header("Location: help_center.php" . ($my_role === 'admin' ? "?u=$target_user_id" : ""));
    exit;
}

// --- AMBIL DATA UNTUK ADMIN ---
// Admin melihat daftar orang yang nge-chat
if ($my_role === 'admin') {
    $chat_list = mysqli_query($conn, "SELECT DISTINCT s.user_id, u.username, u.role FROM support_chats s JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC");
}

// --- AMBIL ISI CHAT ---
$active_chat_user = ($my_role === 'admin' && isset($_GET['u'])) ? (int)$_GET['u'] : (($my_role !== 'admin') ? $my_id : 0);
if ($active_chat_user > 0) {
    $chats = mysqli_query($conn, "SELECT * FROM support_chats WHERE user_id = $active_chat_user ORDER BY created_at ASC");
    // Ambil info user yang lagi diajak chat (buat header admin)
    $client_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username, role FROM users WHERE id = $active_chat_user"));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Live Support | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fdfaf9; }
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.5); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="h-screen flex p-6 md:p-10 gap-6">

    <?php if($my_role === 'admin'): ?>
    <aside class="w-80 glass rounded-[2.5rem] shadow-xl flex flex-col overflow-hidden">
        <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
            <h2 class="font-black text-lg">Support Inbox</h2>
            <a href="admin/admin_dashboard.php" class="p-2 bg-white/10 rounded-xl hover:bg-white/20 transition-all"><span class="material-symbols-rounded text-sm">close</span></a>
        </div>
        <div class="flex-1 overflow-y-auto no-scrollbar p-4 space-y-2">
            <?php if(mysqli_num_rows($chat_list) > 0): ?>
                <?php while($c = mysqli_fetch_assoc($chat_list)): ?>
                    <a href="?u=<?= $c['user_id'] ?>" class="flex items-center gap-3 p-4 rounded-2xl transition-all <?= (isset($_GET['u']) && $_GET['u'] == $c['user_id']) ? 'bg-[#FF6B6B] text-white shadow-lg' : 'bg-slate-50 hover:bg-slate-100 text-slate-700' ?>">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($c['username']) ?>" class="w-10 h-10 rounded-full bg-white">
                        <div>
                            <p class="font-bold text-sm"><?= htmlspecialchars($c['username']) ?></p>
                            <p class="text-[10px] uppercase font-black opacity-70"><?= $c['role'] ?></p>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-slate-400 font-bold italic mt-10">Belum ada laporan masuk.</p>
            <?php endif; ?>
        </div>
    </aside>
    <?php endif; ?>

    <main class="flex-1 glass rounded-[2.5rem] shadow-xl flex flex-col relative overflow-hidden">
        
        <?php if($active_chat_user > 0): ?>
            <header class="p-6 border-b border-slate-100 flex justify-between items-center bg-white/50">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-50 text-[#FF6B6B] rounded-2xl flex items-center justify-center">
                        <span class="material-symbols-rounded">support_agent</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-800">
                            <?= $my_role === 'admin' ? "Chat with " . htmlspecialchars($client_info['username']) : "KosFinder Support" ?>
                        </h2>
                        <p class="text-xs font-bold text-green-500 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> Online 24/7</p>
                    </div>
                </div>
                <?php if($my_role !== 'admin'): ?>
                    <?php $back_link = ($my_role === 'owner') ? 'owner/owner_dashboard.php' : 'user/user_dashboard.php'; ?>
                    <a href="<?= $back_link ?>" class="px-5 py-2.5 bg-slate-100 text-slate-500 rounded-xl font-black text-xs hover:bg-slate-200 transition-all flex items-center gap-2"><span class="material-symbols-rounded text-sm">arrow_back</span> Dashboard</a>
                <?php endif; ?>
            </header>

            <div class="flex-1 overflow-y-auto p-6 space-y-4 no-scrollbar flex flex-col" id="chatBox">
                <div class="text-center mb-6">
                    <span class="bg-slate-100 text-slate-400 px-4 py-1 rounded-full text-[10px] font-black uppercase">Pesan dilindungi sistem</span>
                </div>
                
                <?php while($chat = mysqli_fetch_assoc($chats)): 
                    $is_me = ($chat['sender_id'] == $my_id);
                ?>
                    <div class="flex <?= $is_me ? 'justify-end' : 'justify-start' ?>">
                        <div class="max-w-[70%] <?= $is_me ? 'bg-gradient-to-br from-[#FF6B6B] to-[#ff4757] text-white rounded-tl-2xl rounded-tr-2xl rounded-bl-2xl shadow-lg shadow-red-100' : 'bg-white border border-slate-100 text-slate-700 rounded-tl-2xl rounded-tr-2xl rounded-br-2xl shadow-sm' ?> px-6 py-4">
                            <p class="text-sm font-semibold"><?= htmlspecialchars($chat['message']) ?></p>
                            <p class="text-[9px] mt-2 <?= $is_me ? 'text-red-100' : 'text-slate-400' ?> font-black"><?= date('H:i', strtotime($chat['created_at'])) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="p-6 bg-white/50 border-t border-slate-100">
                <form method="POST" class="flex gap-4">
                    <input type="text" name="message" placeholder="Ketik pesan keluhan/bantuan di sini..." class="flex-1 bg-slate-50 border-none outline-none p-4 rounded-2xl font-bold text-slate-700" autocomplete="off" required>
                    <button type="submit" name="send_msg" class="bg-slate-900 text-white p-4 rounded-2xl hover:bg-[#FF6B6B] transition-colors shadow-lg flex items-center justify-center">
                        <span class="material-symbols-rounded">send</span>
                    </button>
                </form>
            </div>
            
            <script>
                // Auto scroll ke pesan terbawah
                const chatBox = document.getElementById("chatBox");
                chatBox.scrollTop = chatBox.scrollHeight;
            </script>
            
        <?php else: ?>
            <div class="flex-1 flex flex-col items-center justify-center text-center p-10">
                <div class="w-32 h-32 bg-slate-100 rounded-full flex items-center justify-center mb-6">
                    <span class="material-symbols-rounded text-6xl text-slate-300">forum</span>
                </div>
                <h2 class="text-2xl font-black text-slate-800 mb-2">Pilih Chat di Kiri</h2>
                <p class="text-slate-400 font-bold">Silakan pilih tiket dari pengguna untuk mulai membalas pesan.</p>
            </div>
        <?php endif; ?>

    </main>
</body>
</html>