<?php
$host     = "localhost";
$user     = "root"; 
$password = "";     
$database = "db_stok_gudang"; // Pastikan ini sesuai dengan nama database di phpMyAdmin kamu

$koneksi = mysqli_connect($host, $user, $password, $database);

if (mysqli_connect_error()) {
    echo "Koneksi database gagal : " . mysqli_connect_error();
    exit();
}
?>