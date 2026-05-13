<?php
session_start();
include '../db.php';

// Kalau sudah login, langsung lempar ke dashboard masing-masing
if (isset($_SESSION['user_id'])) {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$error = '';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Cek apakah email sudah terdaftar
    $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($check_email) > 0) {
        $error = "Email sudah terdaftar! Gunakan email lain.";
    } else {
        // Masukkan data ke database
        $insert = mysqli_query($conn, "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')");
        
        if ($insert) {
            echo "<script>
                    alert('Akun berhasil dibuat! Silakan Sign In.');
                    window.location.href = 'login.php';
                  </script>";
            exit;
        } else {
            $error = "Gagal membuat akun. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | KosFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fa; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 py-10">

    <div class="bg-white w-full max-w-md p-8 sm:p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/50">
        
        <div class="flex justify-center mb-6">
            <div class="w-14 h-14 bg-red-50 text-[#FF6B6B] rounded-2xl flex items-center justify-center">
                <span class="material-symbols-rounded text-3xl">person_add</span>
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-[800] text-slate-800 mb-2">Create Account 🚀</h1>
            <p class="text-sm text-slate-500 font-medium">Join us and start your journey!</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-xl mb-6 text-sm font-bold text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                <input type="text" name="username" required placeholder="John Doe" 
                       class="w-full bg-[#EEF2F6] border-none text-slate-700 font-semibold px-5 py-4 rounded-xl focus:ring-2 focus:ring-red-200 outline-none transition-all">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                <input type="email" name="email" required placeholder="youremail@gmail.com" 
                       class="w-full bg-[#EEF2F6] border-none text-slate-700 font-semibold px-5 py-4 rounded-xl focus:ring-2 focus:ring-red-200 outline-none transition-all">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       class="w-full bg-[#EEF2F6] border-none text-slate-700 font-semibold px-5 py-4 rounded-xl focus:ring-2 focus:ring-red-200 outline-none transition-all">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">I want to...</label>
                <select name="role" required class="w-full bg-[#EEF2F6] border-none text-slate-700 font-semibold px-5 py-4 rounded-xl focus:ring-2 focus:ring-red-200 outline-none transition-all">
                    <option value="user">Cari Kost (Renter)</option>
                    <option value="owner">Sewakan Kost (Owner)</option>
                </select>
            </div>

            <button type="submit" name="register" 
                    class="w-full bg-slate-800 hover:bg-[#FF6B6B] text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-red-200 transition-all mt-6">
                Create Account
            </button>
        </form>

        <p class="text-center text-sm font-medium text-slate-500 mt-8">
            Already have an account? 
            <a href="login.php" class="text-[#FF6B6B] font-bold hover:underline">Sign In</a>
        </p>
    </div>

</body>
</html>