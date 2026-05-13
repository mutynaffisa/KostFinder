<?php
session_start();
include '../db.php';

// Kalau sudah login, langsung lempar ke dashboard masing-masing
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role == 'admin') header("Location: ../admin/admin_dashboard.php");
    elseif ($role == 'owner') header("Location: ../owner/owner_dashboard.php");
    else header("Location: ../user/user_dashboard.php");
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Cek user di database (Asumsi password tidak di-hash, sesuaikan jika pakai MD5/Hash)
    $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND password = '$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];

        if ($data['role'] == 'admin') header("Location: ../admin/admin_dashboard.php");
        elseif ($data['role'] == 'owner') header("Location: ../owner/owner_dashboard.php");
        else header("Location: ../user/user_dashboard.php");
        exit;
    } else {
        $error = "Email atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fa; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="bg-white w-full max-w-md p-8 sm:p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/50">
        
        <div class="flex justify-center mb-6">
            <div class="w-14 h-14 bg-red-50 text-[#FF6B6B] rounded-2xl flex items-center justify-center">
                <span class="material-symbols-rounded text-3xl">home</span>
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-[800] text-slate-800 mb-2">Welcome Back! ✨</h1>
            <p class="text-sm text-slate-500 font-medium">Ready to find your next favorite spot?</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-xl mb-6 text-sm font-bold text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                <input type="email" name="email" required placeholder="admin@gmail.com" 
                       class="w-full bg-[#EEF2F6] border-none text-slate-700 font-semibold px-5 py-4 rounded-xl focus:ring-2 focus:ring-red-200 outline-none transition-all">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       class="w-full bg-[#EEF2F6] border-none text-slate-700 font-semibold px-5 py-4 rounded-xl focus:ring-2 focus:ring-red-200 outline-none transition-all">
            </div>

            <button type="submit" name="login" 
                    class="w-full bg-[#FF6B6B] hover:bg-[#ff5252] text-white font-bold py-4 rounded-xl shadow-lg shadow-red-200 hover:shadow-red-300 transition-all mt-4">
                Sign In
            </button>
        </form>

        <p class="text-center text-sm font-medium text-slate-500 mt-8">
            Don't have an account? 
            <!-- INI LINK YANG DIPERBAIKI -->
            <a href="register.php" class="text-[#FF6B6B] font-bold hover:underline">Create Account</a>
        </p>
    </div>

</body>
</html>