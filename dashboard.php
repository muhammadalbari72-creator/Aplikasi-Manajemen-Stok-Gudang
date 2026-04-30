<?php
session_start();
// Kunci Pintu: Jika belum login, tendang kembali ke login.php
if (!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login") {
    header("location:login.php");
    exit();
}

// Hubungkan ke database
include 'koneksi.php';

// Ambil data user yang sedang login
$nama_user = $_SESSION['nama_lengkap'];
$role_user = strtoupper($_SESSION['role']);

// --------------------------------------------------------
// MENGAMBIL DATA STATISTIK DARI DATABASE
// --------------------------------------------------------
// 1. Total Barang
$query_barang = mysqli_query($koneksi, "SELECT * FROM barang");
$jml_barang   = mysqli_num_rows($query_barang);

// 2. Stok Kritis (Barang yang stoknya <= batas minimum)
$query_kritis = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok <= stok_min");
$jml_kritis   = mysqli_num_rows($query_kritis);

// 3. Total Transaksi Masuk
$query_masuk  = mysqli_query($koneksi, "SELECT * FROM transaksi_masuk");
$jml_masuk    = mysqli_num_rows($query_masuk);

// 4. Total Transaksi Keluar
$query_keluar = mysqli_query($koneksi, "SELECT * FROM transaksi_keluar");
$jml_keluar   = mysqli_num_rows($query_keluar);

// 5. Transaksi Terbaru (5 data terakhir)
$query_trx_baru = mysqli_query($koneksi, "SELECT t.tanggal, b.nama_barang, t.jumlah FROM transaksi_masuk t JOIN barang b ON t.id_barang = b.id_barang ORDER BY t.id_masuk DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Toko Sembako</title>
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
                    
                    <?php if($jml_kritis > 0): ?>
                        <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white border-2 border-white"><?= $jml_kritis ?></span>
                    <?php endif; ?>
                </button>

                <div id="dropdownNotif" class="hidden notif-popup absolute right-0 mt-3 w-80 md:w-96 bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-gray-100 z-50 overflow-hidden flex flex-col">
                    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h4 class="font-bold text-gray-900">Notifikasi Stok</h4>
                    </div>
                    <div class="max-h-[360px] overflow-y-auto custom-scrollbar">
                        <?php 
                        if($jml_kritis > 0): 
                            // Tampilkan barang yang stoknya kritis di notifikasi
                            mysqli_data_seek($query_kritis, 0); // Reset pointer query
                            while($notif = mysqli_fetch_assoc($query_kritis)):
                        ?>
                            <a href="#" class="block px-5 py-4 hover:bg-gray-50 transition-colors border-b border-gray-50 relative bg-red-50/10">
                                <span class="absolute top-1/2 left-2.5 -translate-y-1/2 w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                <div class="flex gap-4 items-start pl-3">
                                    <div class="bg-red-50 p-2 rounded-full text-red-500 shrink-0 mt-0.5 border border-red-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 mb-0.5">Stok Menipis: <?= $notif['nama_barang'] ?></p>
                                        <p class="text-xs text-gray-500 leading-relaxed line-clamp-2">Sisa stok saat ini hanya <strong class="text-red-600"><?= $notif['stok'] ?></strong>. Segera lakukan pengadaan.</p>
                                    </div>
                                </div>
                            </a>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <div class="p-6 text-center text-gray-400 text-sm font-medium">Stok dalam keadaan aman.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 border-l pl-6 border-gray-300">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-900 leading-none"><?= $nama_user ?></p>
                    <p class="text-[10px] text-gray-400"><?= $role_user ?></p>
                </div>
                <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')" class="text-gray-900 hover:text-red-600 transition-colors" title="Logout">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>
    </header>

    <nav class="bg-[#05051a] px-8 flex gap-1">
        <a href="dashboard.php" class="nav-item-active flex items-center gap-2 px-6 py-4 text-white text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            Dashboard
        </a>
        
        <?php if($_SESSION['role'] == 'admin'): ?>
            <a href="kategori.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                Kategori
            </a>
            <a href="barang.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Barang
            </a>
            <a href="supplier.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Supplier
            </a>
            <a href="transaksi_masuk.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Transaksi Masuk
            </a>
            <a href="transaksi_keluar.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Transaksi Keluar
            </a>
        <?php endif; ?>

        <a href="laporan.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Laporan
        </a>
    </nav>

    <main class="p-8 space-y-6">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900">Dashboard</h2>
            <p class="text-gray-400 text-sm mt-1">Halo, <?= $nama_user ?>! Berikut ringkasan stok barang hari ini.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="border border-gray-900 rounded-2xl bg-white p-6 flex flex-col justify-between min-h-[140px]">
                <span class="text-gray-500 font-medium">Total Barang</span>
                <span class="text-3xl font-black text-gray-900 mt-6"><?= $jml_barang ?> <span class="text-xs text-gray-400 font-normal">Item</span></span>
            </div>
            <div class="border <?= ($jml_kritis > 0) ? 'border-red-500 bg-red-50' : 'border-gray-900 bg-white' ?> rounded-2xl p-6 flex flex-col justify-between min-h-[140px]">
                <span class="<?= ($jml_kritis > 0) ? 'text-red-500' : 'text-gray-500' ?> font-medium">Stok Rendah</span>
                <span class="text-3xl font-black <?= ($jml_kritis > 0) ? 'text-red-600' : 'text-gray-900' ?> mt-6"><?= $jml_kritis ?> <span class="text-xs font-normal">Perlu diisi</span></span>
            </div>
            <div class="border border-gray-900 rounded-2xl bg-white p-6 flex flex-col justify-between min-h-[140px]">
                <span class="text-gray-500 font-medium">Transaksi Masuk</span>
                <span class="text-3xl font-black text-gray-900 mt-6"><?= $jml_masuk ?> <span class="text-xs text-gray-400 font-normal">Pembelian</span></span>
            </div>
            <div class="border border-gray-900 rounded-2xl bg-white p-6 flex flex-col justify-between min-h-[140px]">
                <span class="text-gray-500 font-medium">Transaksi Keluar</span>
                <span class="text-3xl font-black text-gray-900 mt-6"><?= $jml_keluar ?> <span class="text-xs text-gray-400 font-normal">Penjualan</span></span>
            </div>
        </div>

        <?php if($jml_kritis > 0): ?>
        <div class="border border-red-500 bg-white p-4 flex items-center gap-4 rounded-xl shadow-sm">
            <div class="bg-red-500 text-white p-1.5 rounded">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            </div>
            <div>
                <h4 class="text-red-600 font-bold text-sm leading-none">Peringatan Stok Rendah</h4>
                <p class="text-[10px] text-red-500 mt-1 uppercase">Terdapat <?= $jml_kritis ?> barang dengan stok di bawah batas minimum. Segera lakukan pengadaan.</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="space-y-3">
                <h3 class="font-bold text-gray-900 text-sm">Barang Stok Rendah</h3>
                <div class="border border-gray-900 rounded-2xl p-5 space-y-4 bg-white min-h-[200px]">
                    <?php 
                    if($jml_kritis > 0):
                        mysqli_data_seek($query_kritis, 0); // Reset pointer
                        while($brg = mysqli_fetch_assoc($query_kritis)):
                    ?>
                        <div class="border border-red-200 bg-red-50/30 rounded-xl h-14 flex items-center justify-between px-5">
                            <div class="text-sm font-semibold text-gray-800"><?= $brg['nama_barang'] ?></div>
                            <div class="text-xs font-bold text-red-600 bg-white border border-red-200 px-3 py-1 rounded-full">Sisa: <?= $brg['stok'] ?></div>
                        </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <div class="h-full flex items-center justify-center pt-10">
                            <span class="text-gray-400 font-medium text-sm">Semua stok barang aman</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="font-bold text-gray-900 text-sm">Transaksi Masuk Terbaru</h3>
                <div class="border border-gray-900 rounded-2xl p-5 space-y-4 bg-white min-h-[200px]">
                    <?php 
                    if(mysqli_num_rows($query_trx_baru) > 0):
                        while($trx = mysqli_fetch_assoc($query_trx_baru)):
                    ?>
                        <div class="border border-gray-200 rounded-xl h-14 flex items-center justify-between px-5">
                            <div>
                                <div class="text-sm font-semibold text-gray-800"><?= $trx['nama_barang'] ?></div>
                                <div class="text-[10px] text-gray-400"><?= date('d M Y', strtotime($trx['tanggal'])) ?></div>
                            </div>
                            <div class="text-xs font-bold text-green-600 bg-green-50 px-3 py-1 rounded-full">+ <?= $trx['jumlah'] ?></div>
                        </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <div class="h-full flex items-center justify-center pt-10">
                            <span class="text-gray-400 font-medium text-sm">Belum ada transaksi masuk</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <script src="script.js"></script>

</body>
</html>