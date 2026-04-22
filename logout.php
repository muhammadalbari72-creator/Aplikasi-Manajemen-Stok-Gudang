<?php 
// Mulai sesi untuk mengenali user mana yang sedang aktif
session_start();

// Hapus semua data sesi (menghancurkan tiket login)
session_destroy();

// Arahkan kembali ke halaman login
header("location:login.php");
exit();
?>