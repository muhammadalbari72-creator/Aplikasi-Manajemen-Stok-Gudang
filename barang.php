<?php
session_start();
include 'koneksi.php';

// 1. KEAMANAN & SESSION
if (!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login") {
    header("location:login.php");
    exit();
}

$nama_user = $_SESSION['nama_lengkap'];
$role_user = ucfirst($_SESSION['role']);

// 2. LOGIKA SIMPAN (TAMBAH)
if (isset($_POST['simpan_barang'])) {
    $kode      = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $id_kat    = $_POST['id_kategori'];
    $stok      = $_POST['stok'];
    $stok_min  = $_POST['stok_min'];
    $harga     = $_POST['harga_beli'];

    $simpan = mysqli_query($koneksi, "INSERT INTO barang (kode_barang, nama_barang, id_kategori, stok, stok_min, harga_beli) 
              VALUES ('$kode', '$nama', '$id_kat', '$stok', '$stok_min', '$harga')");
    if ($simpan) { header("location:barang.php"); }
}

// 3. LOGIKA UPDATE (EDIT)
if (isset($_POST['update_barang'])) {
    $id        = $_POST['id_barang'];
    $kode      = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $id_kat    = $_POST['id_kategori'];
    $stok      = $_POST['stok'];
    $stok_min  = $_POST['stok_min'];
    $harga     = $_POST['harga_beli'];

    $update = mysqli_query($koneksi, "UPDATE barang SET 
                kode_barang='$kode', nama_barang='$nama', id_kategori='$id_kat', 
                stok='$stok', stok_min='$stok_min', harga_beli='$harga' 
                WHERE id_barang='$id'");
    if ($update) { header("location:barang.php"); }
}

// 4. LOGIKA HAPUS
if (isset($_GET['hapus_id'])) {
    $id = $_GET['hapus_id'];
    mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang='$id'");
    header("location:barang.php");
}

// 5. DATA NOTIF & TABEL
$query_kritis = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok <= stok_min");
$jml_kritis = mysqli_num_rows($query_kritis);

$query_barang = mysqli_query($koneksi, "SELECT barang.*, kategori.nama_kategori 
    FROM barang LEFT JOIN kategori ON barang.id_kategori = kategori.id_kategori 
    ORDER BY barang.id_barang DESC");
$total_barang = mysqli_num_rows($query_barang);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang - Toko Sembako</title>
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
<body class="bg-[#fcfcfc]">

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
                <button id="btnNotif" class="text-gray-500 hover:text-[#05051a] relative p-2 transition-colors rounded-full hover:bg-gray-100">
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

            <div class="flex items-center gap-3 border-l pl-6 border-gray-300">
                <div class="text-right"><p class="text-sm font-bold text-gray-900 leading-none"><?= $nama_user ?></p><p class="text-[10px] text-gray-400"><?= $role_user ?></p></div>
                <a href="logout.php" onclick="return confirm('Keluar?')" class="text-gray-900 hover:text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>
    </header>

    <nav class="bg-[#05051a] px-8 flex gap-1">
        <a href="dashboard.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg> Dashboard</a>
        <a href="kategori.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg> Kategori</a>
        <a href="barang.php" class="flex items-center gap-2 px-6 py-4 text-white text-sm font-medium transition-all border-b-2 border-white bg-white/10"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg> Barang</a>
        <a href="supplier.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg> Supplier</a>
        <a href="transaksi_masuk.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Transaksi Masuk</a>
        <a href="transaksi_keluar.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Transaksi Keluar</a>
        <a href="laporan.php" class="flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white text-sm font-medium transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Laporan</a>
    </nav>

    <main class="p-8 space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900">Stok Barang</h2>
                <p class="text-gray-400 text-sm mt-1">Kelola data produk dan stok sembako.</p>
            </div>
            <button onclick="openTambahModal()" class="bg-black text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-gray-800 transition-all flex items-center gap-2 shadow-lg">
                <span class="text-lg">+</span> Tambah Barang
            </button>
        </div>

        <div class="border border-gray-900 rounded-2xl p-6 bg-white shadow-sm">
            <div class="search-bar flex items-center px-4 py-3 gap-3 bg-gray-100 rounded-xl">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" placeholder="Cari barang..." class="bg-transparent border-none outline-none text-sm w-full text-gray-600">
            </div>
        </div>

        <div class="border border-gray-900 rounded-2xl p-8 bg-white shadow-sm min-h-[400px]">
            <div class="mb-6 flex justify-between items-center border-b pb-4">
                <h3 class="font-bold text-gray-800 uppercase tracking-wider text-sm">Daftar Barang</h3>
                <p class="text-gray-400 text-xs font-medium">Total: <?= $total_barang ?> Item</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="text-gray-900 font-extrabold text-xs uppercase border-b border-gray-200 pb-3">
                            <th class="pb-3 px-2">No</th>
                            <th class="pb-3 px-2">Kode</th>
                            <th class="pb-3 px-2">Nama Barang</th>
                            <th class="pb-3 px-2 text-center">Stok</th>
                            <th class="pb-3 px-2">Harga Beli</th>
                            <th class="pb-3 px-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_barang > 0): $no = 1; while ($row = mysqli_fetch_assoc($query_barang)): 
                            $is_kritis = ($row['stok'] <= $row['stok_min']);
                        ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-2"><?= $no++; ?></td>
                                <td class="py-4 px-2 font-mono text-xs text-gray-500 uppercase"><?= $row['kode_barang']; ?></td>
                                <td class="py-4 px-2 font-bold text-gray-900"><?= htmlspecialchars($row['nama_barang']); ?></td>
                                <td class="py-4 px-2 text-center">
                                    <span class="px-3 py-1 rounded-full font-bold text-xs <?= $is_kritis ? 'bg-red-100 text-red-600 border border-red-200' : 'bg-green-100 text-green-600 border border-green-200' ?>">
                                        <?= $row['stok'] ?>
                                    </span>
                                </td>
                                <td class="py-4 px-2 font-medium text-gray-900">Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></td>
                                <td class="py-4 px-2 text-right">
                                    <button onclick="openEditModal('<?= $row['id_barang'] ?>', '<?= $row['kode_barang'] ?>', '<?= htmlspecialchars($row['nama_barang']) ?>', '<?= $row['id_kategori'] ?>', '<?= $row['stok'] ?>', '<?= $row['stok_min'] ?>', '<?= $row['harga_beli'] ?>')" class="text-blue-600 font-bold mr-4">Edit</button>
                                    <a href="barang.php?hapus_id=<?= $row['id_barang'] ?>" onclick="return confirm('Hapus?')" class="text-red-600 font-bold">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="6" class="py-12 text-center text-gray-400">Belum ada data barang.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalBarang" class="modal fixed inset-0 flex items-center justify-center z-[100]">
        <div class="modal-overlay absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="modal-container bg-white w-11/12 md:max-w-2xl mx-auto rounded-2xl shadow-2xl z-50 overflow-hidden">
            <div class="p-8">
                <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                    <p id="modalTitle" class="text-xl font-black">Tambah Barang Baru</p>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-black text-2xl">&times;</button>
                </div>
                <form action="barang.php" method="POST" class="mt-6 space-y-4">
                    <input type="hidden" name="id_barang" id="form_id">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Kode Barang</label>
                            <input type="text" name="kode_barang" id="form_kode" required class="w-full px-4 py-3 border border-gray-200 rounded-xl outline-none focus:border-black bg-gray-50 text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Kategori</label>
                            <select name="id_kategori" id="form_kategori" required class="w-full px-4 py-3 border border-gray-200 rounded-xl outline-none focus:border-black bg-gray-50 text-sm">
                                <option value="">-- Pilih --</option>
                                <?php $kats = mysqli_query($koneksi, "SELECT * FROM kategori"); while($k = mysqli_fetch_assoc($kats)): ?>
                                    <option value="<?= $k['id_kategori'] ?>"><?= $k['nama_kategori'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nama Barang</label>
                        <input type="text" name="nama_barang" id="form_nama" required class="w-full px-4 py-3 border border-gray-200 rounded-xl outline-none focus:border-black bg-gray-50 text-sm">
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Stok</label>
                            <input type="number" name="stok" id="form_stok" required class="w-full px-4 py-3 border border-gray-200 rounded-xl outline-none focus:border-black bg-gray-50 text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Stok Min</label>
                            <input type="number" name="stok_min" id="form_stok_min" required class="w-full px-4 py-3 border border-gray-200 rounded-xl outline-none focus:border-black bg-gray-50 text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Harga Beli</label>
                            <input type="number" name="harga_beli" id="form_harga" required class="w-full px-4 py-3 border border-gray-200 rounded-xl outline-none focus:border-black bg-gray-50 text-sm">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closeModal()" class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition-all">Batal</button>
                        <button type="submit" id="btnSubmit" name="simpan_barang" class="flex-1 py-3 bg-black text-white rounded-xl font-bold hover:bg-gray-800 shadow-lg transition-all">Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        const modal = document.getElementById('modalBarang');

        function openTambahModal() {
            document.getElementById('modalTitle').innerText = "Tambah Barang Baru";
            document.getElementById('btnSubmit').name = "simpan_barang";
            document.getElementById('form_id').value = "";
            document.getElementById('form_kode').value = "";
            document.getElementById('form_nama').value = "";
            document.getElementById('form_kategori').value = "";
            document.getElementById('form_stok').value = "0";
            document.getElementById('form_stok_min').value = "5";
            document.getElementById('form_harga').value = "";
            modal.classList.add('active');
            document.body.classList.add('modal-active');
        }

        function openEditModal(id, kode, nama, kat, stok, stok_min, harga) {
            document.getElementById('modalTitle').innerText = "Edit Barang";
            document.getElementById('btnSubmit').name = "update_barang";
            document.getElementById('form_id').value = id;
            document.getElementById('form_kode').value = kode;
            document.getElementById('form_nama').value = nama;
            document.getElementById('form_kategori').value = kat;
            document.getElementById('form_stok').value = stok;
            document.getElementById('form_stok_min').value = stok_min;
            document.getElementById('form_harga').value = harga;
            modal.classList.add('active');
            document.body.classList.add('modal-active');
        }

        function closeModal() {
            modal.classList.remove('active');
            document.body.classList.remove('modal-active');
        }
    </script>
</body>
</html>```