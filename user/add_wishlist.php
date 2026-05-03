<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) { 
    header("Location: user_dashboard.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];
$kost_id = (int)$_GET['id'];

// Cek apakah sudah ada di wishlist
$check = mysqli_query($conn, "SELECT id FROM wishlist WHERE user_id = $user_id AND kost_id = $kost_id");

if (mysqli_num_rows($check) > 0) {
    // Jika sudah ada, Hapus (Unlike)
    mysqli_query($conn, "DELETE FROM wishlist WHERE user_id = $user_id AND kost_id = $kost_id");
} else {
    // Jika belum ada, Tambah (Like)
    mysqli_query($conn, "INSERT INTO wishlist (user_id, kost_id) VALUES ($user_id, $kost_id)");
}

// Kembalikan ke halaman detail kost tadi
header("Location: detail_kost.php?id=" . $kost_id);
exit;
?>