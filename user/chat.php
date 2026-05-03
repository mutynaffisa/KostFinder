<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['owner_id']) || !isset($_GET['kost_id'])) { 
    header("Location: user_dashboard.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];
$owner_id = (int)$_GET['owner_id'];
$kost_id = (int)$_GET['kost_id'];

// Proses kirim pesan
if (isset($_POST['send_msg']) && !empty(trim($_POST['message']))) {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    mysqli_query($conn, "INSERT INTO messages (sender_id, receiver_id, kost_id, message) VALUES ($user_id, $owner_id, $kost_id, '$message')");
    // Refresh halaman biar pesan baru muncul
    header("Location: chat.php?owner_id=$owner_id&kost_id=$kost_id");
    exit;
}

// Ambil info Owner & Kost untuk header chat
$owner_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE id = $owner_id"));
$kost_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM kosts WHERE id = $kost_id"));

// Ambil riwayat chat antara user ini dan owner ini terkait kost ini
$chat_query = mysqli_query($conn, "SELECT * FROM messages WHERE 
    ((sender_id = $user_id AND receiver_id = $owner_id) OR (sender_id = $owner_id AND receiver_id = $user_id)) 
    AND kost_id = $kost_id ORDER BY created_at ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Inbox | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fdfaf9; }
        .coral-shadow { box-shadow: 0 20px 40px -15px rgba(255,107,107,0.15); }
    </style>
</head>
<body class="h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-3xl bg-white h-[85vh] rounded-[3rem] coral-shadow border border-slate-100 flex flex-col overflow-hidden">
        
        <div class="bg-slate-50 p-6 border-b border-slate-100 flex items-center gap-4">
            <a href="detail_kost.php?id=<?= $kost_id ?>" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-400 hover:text-[#FF6B6B] shadow-sm">
                <span class="material-symbols-rounded">arrow_back</span>
            </a>
            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($owner_info['username']) ?>" class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-slate-100">
            <div>
                <h2 class="font-black text-slate-800 leading-tight"><?= htmlspecialchars($owner_info['username']) ?></h2>
                <p class="text-xs font-bold text-slate-400 flex items-center gap-1">
                    <span class="material-symbols-rounded text-[14px] text-[#FF6B6B]">home</span> Inquiring: <?= htmlspecialchars($kost_info['name']) ?>
                </p>
            </div>
        </div>

        <div class="flex-1 p-6 overflow-y-auto flex flex-col gap-4 bg-slate-50/30" id="chat-box">
            <?php if(mysqli_num_rows($chat_query) > 0): ?>
                <?php while($c = mysqli_fetch_assoc($chat_query)): 
                    $is_me = ($c['sender_id'] == $user_id);
                ?>
                    <div class="flex <?= $is_me ? 'justify-end' : 'justify-start' ?>">
                        <div class="max-w-[70%] p-4 <?= $is_me ? 'bg-[#FF6B6B] text-white rounded-t-2xl rounded-l-2xl' : 'bg-white border border-slate-100 text-slate-700 rounded-t-2xl rounded-r-2xl shadow-sm' ?>">
                            <p class="font-medium text-sm"><?= nl2br(htmlspecialchars($c['message'])) ?></p>
                            <p class="text-[10px] mt-2 <?= $is_me ? 'text-white/70 text-right' : 'text-slate-400 text-left' ?> font-bold">
                                <?= date('H:i', strtotime($c['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="m-auto text-center">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-rounded text-[#FF6B6B] text-3xl">waving_hand</span>
                    </div>
                    <p class="font-bold text-slate-400">Say hello to the owner!</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="p-6 bg-white border-t border-slate-100">
            <form method="POST" class="flex gap-3 relative">
                <input type="text" name="message" required autocomplete="off" placeholder="Type your message here..." class="w-full bg-slate-50 border-none outline-none py-4 px-6 rounded-full font-bold text-slate-600 focus:ring-2 focus:ring-red-100">
                <button type="submit" name="send_msg" class="bg-[#FF6B6B] text-white w-14 h-14 rounded-full flex justify-center items-center hover:scale-105 transition-transform shadow-lg shadow-red-200 shrink-0">
                    <span class="material-symbols-rounded">send</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Auto-scroll ke pesan paling bawah saat halaman di-load
        const chatBox = document.getElementById('chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;
    </script>
</body>
</html>