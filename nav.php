<?php
$role = $_SESSION['role']; // Ambil role dari session
$halaman_aktif = basename($_SERVER['PHP_SELF']); // Deteksi file apa yang sedang dibuka
?>

<nav class="bg-[#05051a] px-8 flex gap-1 overflow-x-auto">
    
    <a href="dashboard.php" class="<?= ($halaman_aktif == 'dashboard.php') ? 'nav-item-active text-white' : 'text-gray-300' ?> flex items-center gap-2 px-6 py-4 hover:text-white text-sm font-medium transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
        Dashboard
    </a>

    <?php if($role == 'admin'): ?>
        
        <a href="kategori.php" class="<?= ($halaman_aktif == 'kategori.php') ? 'nav-item-active text-white' : 'text-gray-300' ?> flex items-center gap-2 px-6 py-4 hover:text-white text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
            Kategori
        </a>
        <a href="barang.php" class="<?= ($halaman_aktif == 'barang.php') ? 'nav-item-active text-white' : 'text-gray-300' ?> flex items-center gap-2 px-6 py-4 hover:text-white text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            Barang
        </a>
        <a href="supplier.php" class="<?= ($halaman_aktif == 'supplier.php') ? 'nav-item-active text-white' : 'text-gray-300' ?> flex items-center gap-2 px-6 py-4 hover:text-white text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Supplier
        </a>
        <a href="transaksi_masuk.php" class="<?= ($halaman_aktif == 'transaksi_masuk.php') ? 'nav-item-active text-white' : 'text-gray-300' ?> flex items-center gap-2 px-6 py-4 hover:text-white text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Masuk
        </a>
        <a href="transaksi_keluar.php" class="<?= ($halaman_aktif == 'transaksi_keluar.php') ? 'nav-item-active text-white' : 'text-gray-300' ?> flex items-center gap-2 px-6 py-4 hover:text-white text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            Keluar
        </a>

    <?php endif; ?>

    <a href="laporan.php" class="<?= ($halaman_aktif == 'laporan.php') ? 'nav-item-active text-white' : 'text-gray-300' ?> flex items-center gap-2 px-6 py-4 hover:text-white text-sm font-medium transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        Laporan
    </a>
</nav>