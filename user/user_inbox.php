<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }

$user_id = $_SESSION['user_id'];

// Proses kirim pesan balasan
if (isset($_POST['send_msg']) && !empty(trim($_POST['message']))) {
    $owner_id = (int)$_POST['owner_id'];
    $kost_id = (int)$_POST['kost_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    mysqli_query($conn, "INSERT INTO messages (sender_id, receiver_id, kost_id, message) VALUES ($user_id, $owner_id, $kost_id, '$message')");
    header("Location: user_inbox.php?owner_id=$owner_id&kost_id=$kost_id");
    exit;
}

// Ambil daftar Owner yang pernah di-chat sama user ini
$contact_query = mysqli_query($conn, "SELECT DISTINCT u.id as owner_id, u.username as owner_name, k.id as kost_id, k.name as kost_name 
    FROM messages m 
    JOIN users u ON (u.id = m.sender_id OR u.id = m.receiver_id) 
    JOIN kosts k ON m.kost_id = k.id 
    WHERE (m.sender_id = $user_id OR m.receiver_id = $user_id) AND u.id != $user_id");

// Cek apakah User lagi buka chat spesifik
$active_owner_id = isset($_GET['owner_id']) ? (int)$_GET['owner_id'] : 0;
$active_kost_id = isset($_GET['kost_id']) ? (int)$_GET['kost_id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>My Inbox | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fdfaf9; }
        .coral-shadow { box-shadow: 0 20px 40px -15px rgba(255,107,107,0.15); }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <div class="w-full md:w-1/3 bg-slate-50/50 border-r border-slate-100 flex flex-col h-full">
        <div class="p-6 border-b border-slate-100 bg-white flex gap-4 items-center">
            <a href="user_dashboard.php" class="p-2 bg-slate-100 text-slate-500 rounded-xl hover:bg-[#FF6B6B] hover:text-white transition-all"><span class="material-symbols-rounded">arrow_back</span></a>
            <h1 class="text-2xl font-black text-slate-800">Inbox 💬</h1>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-2">
            <?php if(mysqli_num_rows($contact_query) > 0): ?>
                <?php while($c = mysqli_fetch_assoc($contact_query)): 
                    $is_active = ($active_owner_id == $c['owner_id']);
                ?>
                <a href="?owner_id=<?= $c['owner_id'] ?>&kost_id=<?= $c['kost_id'] ?>" class="flex items-center gap-4 p-4 rounded-2xl transition-all <?= $is_active ? 'bg-white coral-shadow border border-red-100' : 'hover:bg-white border border-transparent' ?>">
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($c['owner_name']) ?>" class="w-12 h-12 rounded-full bg-slate-100">
                    <div class="overflow-hidden">
                        <p class="font-bold text-slate-800 truncate"><?= htmlspecialchars($c['owner_name']) ?></p>
                        <p class="text-xs text-slate-400 truncate flex items-center gap-1"><span class="material-symbols-rounded text-[12px] text-[#FF6B6B]">home</span> <?= htmlspecialchars($c['kost_name']) ?></p>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-slate-400 font-bold mt-10">Belum ada obrolan.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="hidden md:flex flex-1 flex-col h-full bg-white relative">
        <?php if($active_owner_id): 
            $chat_history = mysqli_query($conn, "SELECT * FROM messages WHERE ((sender_id = $user_id AND receiver_id = $active_owner_id) OR (sender_id = $active_owner_id AND receiver_id = $user_id)) AND kost_id = $active_kost_id ORDER BY created_at ASC");
            $owner_name_active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE id = $active_owner_id"))['username'];
        ?>
        <div class="p-6 border-b border-slate-100 flex items-center gap-4 bg-white/90 backdrop-blur-sm absolute top-0 w-full z-10">
            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($owner_name_active) ?>" class="w-12 h-12 rounded-full bg-red-50 border border-red-100">
            <div>
                <h2 class="font-black text-slate-800"><?= htmlspecialchars($owner_name_active) ?></h2>
                <p class="text-xs font-bold text-[#FF6B6B]">Property Owner</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8 pt-28 pb-24 bg-slate-50/30" id="chat-box">
            <?php while($msg = mysqli_fetch_assoc($chat_history)): 
                $is_me = ($msg['sender_id'] == $user_id);
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
                <input type="hidden" name="owner_id" value="<?= $active_owner_id ?>">
                <input type="hidden" name="kost_id" value="<?= $active_kost_id ?>">
                <input type="text" name="message" required autocomplete="off" placeholder="Tulis pesan..." class="flex-1 bg-slate-50 border-none outline-none py-4 px-6 rounded-full font-bold text-slate-600 focus:ring-2 focus:ring-red-100">
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
            <h3 class="text-2xl font-black text-slate-800">Inbox</h3>
            <p class="text-slate-400 font-medium mt-2">Pilih obrolan di sebelah kiri untuk melihat pesan.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>