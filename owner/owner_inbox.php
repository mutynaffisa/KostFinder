<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../auth/login.php"); exit;
}

$owner_id = $_SESSION['user_id'];
$owner_name = $_SESSION['username'] ?? 'Owner';

// Proses kirim pesan balasan
if (isset($_POST['send_msg']) && !empty(trim($_POST['message']))) {
    $receiver_id = (int)$_POST['receiver_id'];
    $kost_id = (int)$_POST['kost_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    mysqli_query($conn, "INSERT INTO messages (sender_id, receiver_id, kost_id, message) VALUES ($owner_id, $receiver_id, $kost_id, '$message')");
    
    // Bikin notif buat si User kalau pesannya dibales
    mysqli_query($conn, "INSERT INTO notifications (user_id, title, message) VALUES ($receiver_id, 'Pesan Baru', 'Owner membalas pesanmu terkait kost.')");
    
    header("Location: owner_inbox.php?renter_id=$receiver_id&kost_id=$kost_id");
    exit;
}

// Ambil daftar user yang pernah nge-chat Owner ini
$contact_query = mysqli_query($conn, "SELECT DISTINCT u.id as renter_id, u.username as renter_name, k.id as kost_id, k.name as kost_name 
    FROM messages m 
    JOIN users u ON (u.id = m.sender_id OR u.id = m.receiver_id) 
    JOIN kosts k ON m.kost_id = k.id 
    WHERE (m.sender_id = $owner_id OR m.receiver_id = $owner_id) AND u.id != $owner_id");

// Cek apakah Owner lagi buka chat spesifik
$active_renter_id = isset($_GET['renter_id']) ? (int)$_GET['renter_id'] : 0;
$active_kost_id = isset($_GET['kost_id']) ? (int)$_GET['kost_id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Owner Inbox | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fdfaf9; }
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.5); }
        .coral-shadow { box-shadow: 0 20px 40px -15px rgba(255,107,107,0.15); }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <aside class="hidden xl:flex flex-col w-24 p-6 border-r border-slate-100 bg-white items-center">
        <div class="w-12 h-12 bg-[#FF6B6B] rounded-2xl flex items-center justify-center text-white shadow-lg mb-10"><span class="material-symbols-rounded">real_estate_agent</span></div>
        <nav class="space-y-6 flex-1 flex flex-col items-center">
            <a href="owner_dashboard.php" class="text-slate-400 hover:text-[#FF6B6B] transition-colors"><span class="material-symbols-rounded text-3xl">dashboard</span></a>
            <a href="#" class="text-[#FF6B6B] bg-red-50 p-3 rounded-2xl transition-colors"><span class="material-symbols-rounded text-3xl">forum</span></a>
        </nav>
    </aside>

    <div class="w-full md:w-1/3 bg-slate-50/50 border-r border-slate-100 flex flex-col h-full">
        <div class="p-6 border-b border-slate-100 bg-white">
            <h1 class="text-2xl font-black text-slate-800">Messages 💬</h1>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-2">
            <?php if(mysqli_num_rows($contact_query) > 0): ?>
                <?php while($c = mysqli_fetch_assoc($contact_query)): 
                    $is_active = ($active_renter_id == $c['renter_id']);
                ?>
                <a href="?renter_id=<?= $c['renter_id'] ?>&kost_id=<?= $c['kost_id'] ?>" class="flex items-center gap-4 p-4 rounded-2xl transition-all <?= $is_active ? 'bg-white coral-shadow border border-red-100' : 'hover:bg-white border border-transparent' ?>">
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($c['renter_name']) ?>" class="w-12 h-12 rounded-full bg-slate-100">
                    <div class="overflow-hidden">
                        <p class="font-bold text-slate-800 truncate"><?= htmlspecialchars($c['renter_name']) ?></p>
                        <p class="text-xs text-slate-400 truncate flex items-center gap-1"><span class="material-symbols-rounded text-[12px] text-[#FF6B6B]">home</span> <?= htmlspecialchars($c['kost_name']) ?></p>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-slate-400 font-bold mt-10">Belum ada pesan masuk.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="hidden md:flex flex-1 flex-col h-full bg-white relative">
        <?php if($active_renter_id): 
            
            // --- EKSEKUSI PENGHAPUSAN NOTIF (UPDATE STATUS IS_READ JADI 1) ---
            mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE receiver_id = $owner_id AND sender_id = $active_renter_id");

            $chat_history = mysqli_query($conn, "SELECT * FROM messages WHERE ((sender_id = $owner_id AND receiver_id = $active_renter_id) OR (sender_id = $active_renter_id AND receiver_id = $owner_id)) AND kost_id = $active_kost_id ORDER BY created_at ASC");
            $renter_name_active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE id = $active_renter_id"))['username'];
        ?>
        <div class="p-6 border-b border-slate-100 flex items-center gap-4 bg-white/90 backdrop-blur-sm absolute top-0 w-full z-10">
            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($renter_name_active) ?>" class="w-12 h-12 rounded-full bg-red-50 border border-red-100">
            <div>
                <h2 class="font-black text-slate-800"><?= htmlspecialchars($renter_name_active) ?></h2>
                <p class="text-xs font-bold text-green-500">Renter</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8 pt-28 pb-24 bg-slate-50/30" id="chat-box">
            <?php while($msg = mysqli_fetch_assoc($chat_history)): 
                $is_me = ($msg['sender_id'] == $owner_id);
            ?>
            <div class="flex <?= $is_me ? 'justify-end' : 'justify-start' ?> mb-4">
                <div class="max-w-[70%] p-4 <?= $is_me ? 'bg-[#FF6B6B] text-white rounded-t-2xl rounded-l-2xl' : 'bg-white border border-slate-100 text-slate-700 rounded-t-2xl rounded-r-2xl shadow-sm' ?>">
                    <p class="font-medium text-sm"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="p-6 bg-white border-t border-slate-100 absolute bottom-0 w-full">
            <form method="POST" class="flex gap-4">
                <input type="hidden" name="receiver_id" value="<?= $active_renter_id ?>">
                <input type="hidden" name="kost_id" value="<?= $active_kost_id ?>">
                <input type="text" name="message" required autocomplete="off" placeholder="Tulis balasan..." class="flex-1 bg-slate-50 border-none outline-none py-4 px-6 rounded-full font-bold text-slate-600 focus:ring-2 focus:ring-red-100">
                <button type="submit" name="send_msg" class="bg-[#FF6B6B] text-white w-14 h-14 rounded-full flex justify-center items-center shadow-lg shadow-red-200 hover:scale-105 transition-all"><span class="material-symbols-rounded">send</span></button>
            </form>
        </div>

        <script>
            const chatBox = document.getElementById('chat-box');
            chatBox.scrollTop = chatBox.scrollHeight;
        </script>
        <?php else: ?>
        <div class="m-auto text-center">
            <span class="material-symbols-rounded text-slate-200 text-6xl mb-4">forum</span>
            <h3 class="text-2xl font-black text-slate-800">Inbox Kosong</h3>
            <p class="text-slate-400 font-medium mt-2">Pilih obrolan di sebelah kiri untuk membalas pesan.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>