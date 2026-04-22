<?php
session_start();
include 'koneksi.php';

// 1. KEAMANAN & SESSION
if (!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login") {
    header("location:login.php");
    exit();
}

$id_user_login = $_SESSION['id_user'];
$nama_user = $_SESSION['nama_lengkap'];
$role_user = ucfirst($_SESSION['role']);

// 2. LOGIKA SIMPAN TRANSAKSI KELUAR + UPDATE STOK
if (isset($_POST['simpan_keluar'])) {
    $id_barang   = $_POST['id_barang'];
    $tanggal     = $_POST['tanggal'];
    $jumlah      = $_POST['jumlah'];
    $tujuan      = mysqli_real_escape_string($koneksi, $_POST['tujuan']);
    $keterangan  = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    $cek_stok = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT stok FROM barang WHERE id_barang = '$id_barang'"));
    
    if ($cek_stok['stok'] < $jumlah) {
        echo "<script>alert('Gagal! Stok tidak mencukupi.'); window.location.href='transaksi_keluar.php';</script>";
    } else {
        mysqli_begin_transaction($koneksi);
        try {
            mysqli_query($koneksi, "INSERT INTO transaksi_keluar (id_barang, id_user, tanggal, jumlah, tujuan, keterangan) 
                         VALUES ('$id_barang', '$id_user_login', '$tanggal', '$jumlah', '$tujuan', '$keterangan')");
            mysqli_query($koneksi, "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = '$id_barang'");
            mysqli_commit($koneksi);
            echo "<script>alert('Berhasil! Stok barang telah berkurang.'); window.location.href='transaksi_keluar.php';</script>";
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            echo "<script>alert('Terjadi kesalahan!');</script>";
        }
    }
}

// 3. DATA NOTIF & TABEL
$query_kritis = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok <= stok_min");
$jml_kritis   = mysqli_num_rows($query_kritis);

$query_keluar = mysqli_query($koneksi, "
    SELECT tk.*, b.nama_barang, u.nama_lengkap as pencatat
    FROM transaksi_keluar tk
    LEFT JOIN barang b ON tk.id_barang = b.id_barang
    LEFT JOIN users u ON tk.id_user = u.id_user
    ORDER BY tk.id_keluar DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Keluar - Toko Sembako</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        .modal { transition: opacity 0.2s ease; opacity: 0; pointer-events: none; }
        .modal.active { opacity: 1; pointer-events: auto; }
        body.modal-active { overflow: hidden; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #eee; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#fcfcfc] overflow-y-auto">

    <header class="bg-white border-b border-gray-200 px-8 py-3 flex justify-between items-center sticky top-0 z-40">
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
                <button id="btnNotif" class="text-gray-500 hover:text-[#05051a] p-2 rounded-full hover:bg-gray-100 relative transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <?php if($jml_kritis > 0): ?><span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white border-2 border-white"><?= $jml_kritis ?></span><?php endif; ?>
                </button>

                <div id="dropdownNotif" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-[100] overflow-hidden text-left">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h4 class="font-bold text-gray-900 text-xs uppercase tracking-widest">Notifikasi Stok</h4>
                    </div>
                    <div class="max-h-80 overflow-y-auto custom-scrollbar text-left">
                        <?php if($jml_kritis > 0): 
                            mysqli_data_seek($query_kritis, 0); 
                            while($n = mysqli_fetch_assoc($query_kritis)): ?>
                            <div class="px-5 py-4 border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-1.5 h-1.5 bg-red-500 rounded-full flex-shrink-0"></div>
                                    
                                    <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center text-red-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                    </div>

                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-gray-900 leading-tight">
                                            Stok Menipis: <span class="text-red-600"><?= htmlspecialchars($n['nama_barang']) ?></span>
                                        </p>
                                        <p class="text-[11px] text-gray-500 mt-0.5">
                                            Sisa stok: <span class="font-bold text-red-600"><?= $n['stok'] ?></span>. Segera restok.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; else: ?>
                            <div class="py-10 text-center">
                                <p class="text-xs text-gray-400 font-medium">Semua stok aman.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 border-l pl-6 border-gray-300 text-right">
                <div><p class="text-sm font-bold text-gray-900 leading-none"><?= $nama_user ?></p><p class="text-[10px] text-gray-400 uppercase tracking-widest mt-1"><?= $role_user ?></p></div>
                <a href="logout.php" onclick="return confirm('Keluar?')" class="text-gray-900 hover:text-red-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>
    </header>

    <nav class="bg-[#05051a] px-8 flex gap-1">
        <a href="dashboard.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg> Dashboard</a>
        <a href="kategori.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg> Kategori</a>
        <a href="barang.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg> Barang</a>
        <a href="supplier.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg> Supplier</a>
        <a href="transaksi_masuk.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Transaksi Masuk</a>
        <a href="transaksi_keluar.php" class="flex items-center gap-2 px-6 py-4 text-white text-sm font-medium transition-all border-b-2 border-white bg-white/10"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Transaksi Keluar</a>
        <a href="laporan.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Laporan</a>
    </nav>

    <main class="p-8 space-y-6">
        <div class="flex justify-between items-center">
            <div><h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Barang Keluar</h2><p class="text-gray-400 text-sm mt-1">Catat transaksi penjualan atau barang keluar.</p></div>
            <button onclick="openModal()" class="bg-[#05051a] text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-black transition-all flex items-center gap-2 shadow-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Catat Transaksi</button>
        </div>

        <div class="border border-gray-900 rounded-2xl p-6 bg-white shadow-sm">
            <div class="search-bar flex items-center px-4 py-3 gap-3 bg-gray-100 rounded-xl">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" placeholder="Cari transaksi keluar..." class="bg-transparent border-none outline-none text-sm w-full text-gray-600">
            </div>
        </div>

        <div class="border border-gray-900 rounded-2xl p-8 bg-white shadow-sm min-h-[400px]">
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead> 
                        <tr class="text-gray-900 font-extrabold text-xs uppercase border-b-2 border-gray-900 pb-3">
                            <th class="py-3 px-2">No</th>
                            <th class="py-3 px-2">Tanggal</th>
                            <th class="py-3 px-2">Barang</th>
                            <th class="py-3 px-2 text-center">Jumlah</th>
                            <th class="py-3 px-2">Tujuan</th>
                            <th class="py-3 px-2 text-right">Pencatat</th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php if (mysqli_num_rows($query_keluar) > 0): $no = 1; while ($row = mysqli_fetch_assoc($query_keluar)): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-2 text-gray-500"><?= $no++; ?></td>
                                <td class="py-4 px-2 font-medium"><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                                <td class="py-4 px-2 font-bold text-gray-900"><?= htmlspecialchars($row['nama_barang']); ?></td>
                                <td class="py-4 px-2 text-center"><span class="bg-red-100 text-red-700 px-3 py-1 rounded-full font-black text-xs">- <?= $row['jumlah']; ?></span></td>
                                <td class="py-4 px-2 text-gray-500"><?= htmlspecialchars($row['tujuan']); ?></td>
                                <td class="py-4 px-2 text-right text-gray-400 italic text-[10px] uppercase"><?= $row['pencatat']; ?></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="6" class="py-12 text-center text-gray-400 font-medium">Belum ada data transaksi keluar.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalKeluar" class="modal fixed inset-0 flex items-center justify-center z-[100]">
        <div class="modal-overlay absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded-[2rem] shadow-2xl z-50 overflow-hidden border border-gray-100">
            <div class="p-10">
                <div class="flex justify-between items-center pb-6">
                    <p class="text-2xl font-black text-gray-900">Barang Keluar</p>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-black transition-transform hover:scale-110"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <form action="transaksi_keluar.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required class="w-full px-5 py-3 border border-gray-200 rounded-2xl outline-none focus:border-black bg-gray-50 text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Barang</label>
                        <select name="id_barang" required class="w-full px-5 py-3 border border-gray-200 rounded-2xl outline-none focus:border-black bg-gray-50 text-sm appearance-none">
                            <option value="">Pilih barang...</option>
                            <?php $brgs = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok > 0 ORDER BY nama_barang ASC"); while($b = mysqli_fetch_assoc($brgs)): ?>
                                <option value="<?= $b['id_barang'] ?>"><?= $b['nama_barang'] ?> (Sedia: <?= $b['stok'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Jumlah</label>
                            <input type="number" name="jumlah" min="1" placeholder="0" required class="w-full px-5 py-3 border border-gray-200 rounded-2xl outline-none focus:border-black bg-gray-50 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tujuan</label>
                            <input type="text" name="tujuan" placeholder="Pembeli/Toko" required class="w-full px-5 py-3 border border-gray-200 rounded-2xl outline-none focus:border-black bg-gray-50 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Keterangan</label>
                        <textarea name="keterangan" placeholder="Catatan tambahan..." class="w-full px-5 py-3 border border-gray-200 rounded-2xl outline-none focus:border-black bg-gray-50 text-sm h-20 resize-none"></textarea>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="closeModal()" class="flex-1 py-4 bg-gray-100 text-gray-500 rounded-2xl font-bold uppercase text-xs">Batal</button>
                        <button type="submit" name="simpan_keluar" class="flex-1 py-4 bg-[#05051a] text-white rounded-2xl font-bold uppercase text-xs shadow-xl">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        const modal = document.getElementById('modalKeluar');
        function openModal() { modal.classList.add('active'); document.body.classList.add('modal-active'); }
        function closeModal() { modal.classList.remove('active'); document.body.classList.remove('modal-active'); }
    </script>
</body>
</html>