<?php
session_start();

// 1. KUNCI PINTU UTAMA
if (!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login") {
    header("location:login.php");
    exit();
}
// CATATAN: Admin dan Manajer sama-sama boleh mengakses halaman ini, jadi tidak ada batasan role.

include 'koneksi.php';

$nama_user = $_SESSION['nama_lengkap'];
$role_user = ucfirst($_SESSION['role']);

// Notifikasi Stok Kritis
$query_kritis = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok <= stok_min");
$jml_kritis   = mysqli_num_rows($query_kritis);

// Ambil data Laporan Stok Barang
$query_laporan = mysqli_query($koneksi, "
    SELECT b.*, k.nama_kategori 
    FROM barang b 
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
    ORDER BY b.nama_barang ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Toko Sembako</title>
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
                        <h4 class="font-bold text-gray-900">Peringatan Stok</h4>
                    </div>
                    <div class="max-h-[360px] overflow-y-auto custom-scrollbar">
                        <?php 
                        if($jml_kritis > 0): 
                            mysqli_data_seek($query_kritis, 0);
                            while($notif = mysqli_fetch_assoc($query_kritis)):
                        ?>
                            <a href="barang.php" class="block px-5 py-4 hover:bg-gray-50 transition-colors border-b border-gray-50 relative bg-red-50/10">
                                <span class="absolute top-1/2 left-2.5 -translate-y-1/2 w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                <div class="flex gap-4 items-start pl-3">
                                    <div class="bg-red-50 p-2 rounded-full text-red-500 shrink-0 mt-0.5 border border-red-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 mb-0.5">Stok Menipis: <?= htmlspecialchars($notif['nama_barang']) ?></p>
                                        <p class="text-xs text-gray-500 leading-relaxed line-clamp-2">Sisa stok saat ini hanya <strong class="text-red-600"><?= $notif['stok'] ?></strong>.</p>
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
                <a href="logout.php" onclick="return confirm('Yakin ingin keluar?')" class="text-gray-900 hover:text-red-600 transition-colors" title="Logout">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>
    </header>

    <nav class="bg-[#05051a] px-8 flex gap-1 overflow-x-auto">
        <a href="dashboard.php" class="nav-item flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            Dashboard
        </a>
        
        <?php if($_SESSION['role'] == 'admin'): ?>
            <a href="kategori.php" class="nav-item flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                Kategori
            </a>
            <a href="barang.php" class="nav-item flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Barang
            </a>
            <a href="supplier.php" class="nav-item flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Supplier
            </a>
            <a href="transaksi_masuk.php" class="nav-item flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Transaksi Masuk
            </a>
            <a href="transaksi_keluar.php" class="nav-item flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Transaksi Keluar
            </a>
        <?php endif; ?>

        <a href="laporan.php" class="nav-item-active flex items-center gap-2 px-6 py-4 text-white text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Laporan
        </a>
    </nav>

    <main class="p-10 space-y-8">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900">Laporan & Analitik</h2>
            <p class="text-gray-400 text-sm mt-1">Laporan stok dan rekapitulasi nilai barang</p>
        </div>

        <div class="border border-gray-900 rounded-2xl p-6 bg-white shadow-sm flex justify-between items-center">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Unduh Laporan Stok Saat Ini</h3>
                <p class="text-xs text-gray-500 mt-1">Ekspor data ke dalam format Excel (.xls) untuk dianalisis lebih lanjut.</p>
            </div>
            
            <a href="export_laporan.php" class="bg-green-600 text-white px-8 py-3 rounded-xl flex items-center gap-2 font-bold text-sm hover:bg-green-700 transition-all shadow-lg shadow-green-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </a>
        </div>

        <div class="border border-gray-900 rounded-2xl p-8 bg-white shadow-sm min-h-[400px]">
            <div class="mb-6 flex justify-between items-end">
                <div>
                    <h3 class="font-bold text-gray-800">Preview Laporan Stok Barang</h3>
                    <p class="text-gray-400 text-xs mt-0.5 font-medium">Kondisi Stok Real-time</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="text-gray-900 font-bold text-sm border-b-2 border-gray-900 pb-2">
                            <th class="pb-3 px-2 w-12">No</th>
                            <th class="pb-3 px-2">Kode</th>
                            <th class="pb-3 px-2">Nama Barang</th>
                            <th class="pb-3 px-2">Kategori</th>
                            <th class="pb-3 px-2">Stok</th>
                            <th class="pb-3 px-2">Harga Beli</th>
                            <th class="pb-3 px-2">Total Nilai Aset</th>
                            <th class="pb-3 px-2 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php 
                        if (mysqli_num_rows($query_laporan) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($query_laporan)) {
                                $total_aset = $row['stok'] * $row['harga_beli'];
                                $is_kritis = ($row['stok'] <= $row['stok_min']);
                                $status_text = $is_kritis ? "Kritis" : "Aman";
                                $status_css  = $is_kritis ? "text-red-600 font-bold" : "text-green-600 font-bold";
                        ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-2 text-gray-600"><?= $no++; ?></td>
                                <td class="py-4 px-2 font-mono text-gray-500 font-medium"><?= htmlspecialchars($row['kode_barang']); ?></td>
                                <td class="py-4 px-2 font-bold text-gray-900"><?= htmlspecialchars($row['nama_barang']); ?></td>
                                <td class="py-4 px-2 text-gray-500"><?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?></td>
                                <td class="py-4 px-2 font-bold text-gray-700"><?= $row['stok']; ?></td>
                                <td class="py-4 px-2 text-gray-600">Rp <?= number_format($row['harga_beli'], 0, ',', '.'); ?></td>
                                <td class="py-4 px-2 font-bold text-gray-900">Rp <?= number_format($total_aset, 0, ',', '.'); ?></td>
                                <td class="py-4 px-2 text-right <?= $status_css ?>"><?= $status_text ?></td>
                            </tr>
                        <?php 
                            } 
                        } else { 
                        ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-gray-400 font-medium bg-gray-50/50 rounded-b-xl">
                                    Belum ada data untuk dilaporkan.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="script.js"></script>

</body>
</html>