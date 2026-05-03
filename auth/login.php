<?php
session_start();
include '../db.php'; // Ini sudah benar karena db.php ada di luar folder auth

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['username'] = $row['username'];

            // PERBAIKAN DI SINI:
            // Kita harus naik satu folder (../) lalu masuk ke folder sesuai role
            // Kita keluar folder 'auth', lalu masuk ke folder saudaranya
       $dashboard = [
    'admin' => '../admin/admin_dashboard.php',
    'owner' => '../owner/owner_add_kost.php',
    'user'  => '../user/user_dashboard.php' // <-- Pastikan ada '/user/' nya!
];

            header("Location: " . ($dashboard[$row['role']] ?? '../user/user_dashboard.php'));
            exit;
        }
    }
    $error = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DreamRoom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 md:p-12 rounded-[2.5rem] shadow-2xl shadow-blue-100 w-full max-w-md border border-white">
        <div class="text-center mb-10">
            <div class="inline-block p-4 bg-red-50 rounded-2xl mb-4 text-[#FF6B6B]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Welcome Back! ✨</h2>
            <p class="text-gray-500 mt-2">Ready to find your next favorite spot?</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-xl text-sm mb-6 border border-red-100 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Email atau password salah!
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Email Address</label>
                <input type="email" name="email" placeholder="name@email.com"
                    class="w-full p-4 bg-gray-50 rounded-2xl border-2 border-transparent focus:border-[#FF6B6B] focus:bg-white transition-all outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Password</label>
                <input type="password" name="password" placeholder="••••••••"
                    class="w-full p-4 bg-gray-50 rounded-2xl border-2 border-transparent focus:border-[#FF6B6B] focus:bg-white transition-all outline-none" required>
            </div>
            <button name="login"
                class="w-full bg-[#FF6B6B] text-white p-4 rounded-2xl font-bold shadow-lg shadow-red-200 hover:bg-[#ff5252] transition-all transform hover:-translate-y-1">
                Sign In
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-gray-500">Don't have an account? 
                <a href="register.php" class="text-[#FF6B6B] font-bold hover:underline">Create Account</a>
            </p>
        </div>
    </div>
</body>
</html>