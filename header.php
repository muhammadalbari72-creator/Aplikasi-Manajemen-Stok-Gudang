<?php
// TIGA BARIS INI ADALAH GEMBOK CACHE BROWSER
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Pastikan file ini dipanggil setelah session_start() dan include 'koneksi.php' di file utama
$nama_user = $_SESSION['nama_lengkap'];
$role_user = strtoupper($_SESSION['role']);

// LOGIKA NOTIFIKASI STOK RENDAH
// Mengambil data barang yang stoknya kurang dari atau sama dengan batas minimum
$query_notif = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok <= stok_min");
$jumlah_notif = mysqli_num_rows($query_notif);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Stok - Toko Sembako</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-[#fcfcfc] overflow-y-auto">

    <header class="bg-white border-b border-gray-200 px-8 py-3 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <div class="bg-[#05051a] p-2 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-gray-900 leading-none">Toko Sembako</h1>
                <p class="text-gray-400 text-[10px] uppercase tracking-widest mt-1">Sistem Manajemen Stok</p>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <div class="relative">
                <button id="btnNotif" class="text-gray-500 hover:text-[#05051a] relative p-2 focus:outline-none transition-colors rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    
                    <?php if($jumlah_notif > 0): ?>
                        <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white border-2 border-white"><?= $jumlah_notif ?></span>
                    <?php endif; ?>
                </button>

                <div id="dropdownNotif" class="hidden notif-popup absolute right-0 mt-3 w-80 md:w-96 bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-gray-100 z-50 overflow-hidden flex flex-col">
                    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h4 class="font-bold text-gray-900">Peringatan Stok</h4>
                    </div>
                    
                    <div class="max-h-[360px] overflow-y-auto custom-scrollbar">
                        <?php 
                        if($jumlah_notif > 0): 
                            while($notif = mysqli_fetch_assoc($query_notif)):
                        ?>
                            <div class="block px-5 py-4 hover:bg-gray-50 transition-colors border-b border-gray-50 relative bg-red-50/20">
                                <span class="absolute top-1/2 left-2.5 -translate-y-1/2 w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                <div class="flex gap-4 items-start pl-3">
                                    <div class="bg-red-50 p-2 rounded-full text-red-500 shrink-0 mt-0.5 border border-red-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 mb-0.5"><?= $notif['nama_barang'] ?></p>
                                        <p class="text-xs text-gray-500 leading-relaxed">Sisa stok saat ini hanya <strong class="text-red-600"><?= $notif['stok'] ?></strong>. Segera lakukan pengadaan.</p>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <div class="px-5 py-8 text-center">
                                <p class="text-sm font-semibold text-gray-500">Semua stok barang dalam kondisi aman. Tidak ada peringatan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 border-l pl-6 border-gray-300">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-900 leading-none"><?= $nama_user ?></p>
                    <p class="text-[10px] text-gray-400"><?= $role_user ?></p>
                </div>
                <a href="logout.php" onclick="return confirm('Yakin ingin keluar?')" class="text-gray-900 hover:text-red-600 transition-colors" title="Logout">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>
    </header>