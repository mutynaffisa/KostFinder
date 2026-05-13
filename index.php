<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
} else {
    // Kalau sudah login, lempar ke dashboard sesuai role
    $role = $_SESSION['role'];
    if ($role == 'admin') header("Location: admin/admin_dashboard.php");
    elseif ($role == 'owner') header("Location: owner/owner_dashboard.php");
    else header("Location: user/user_dashboard.php");
    exit;
}
?>