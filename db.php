<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "kosfinder_db"; // Harus sama dengan nama database di SQL tadi

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
?>