<?php
// Mengambil data otomatis dari variabel yang kamu pasang di Railway tadi
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: 3306; // Tambahkan port standar Railway

// Melakukan koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    // Menampilkan pesan error jika koneksi gagal
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// Opsional: Pesan sukses (bisa kamu hapus nanti jika sudah lancar)
// echo "Koneksi Berhasil!"; 
?>