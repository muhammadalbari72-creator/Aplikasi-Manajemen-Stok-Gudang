<?php
session_start();
include 'koneksi.php';

// Cek sesi keamanan
if (!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login") {
    header("location:login.php");
    exit();
}

// ----------------------------------------------------
// FUNGSI AJAIB: Mengubah output HTML menjadi file Excel
// ----------------------------------------------------
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Stok_Gudang_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Ambil data dari database
$query_laporan = mysqli_query($koneksi, "
    SELECT b.*, k.nama_kategori 
    FROM barang b 
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
    ORDER BY b.nama_barang ASC
");
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="8" style="font-size: 18px; text-align:center;">LAPORAN STOK BARANG GUDANG</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align:center;">Dicetak pada: <?= date('d M Y H:i') ?> | Oleh: <?= $_SESSION['nama_lengkap'] ?></th>
        </tr>
        <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Sisa Stok</th>
            <th>Batas Minimum</th>
            <th>Harga Beli Satuan (Rp)</th>
            <th>Total Nilai Aset (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if (mysqli_num_rows($query_laporan) > 0) {
            $no = 1;
            $grand_total_aset = 0;

            while ($row = mysqli_fetch_assoc($query_laporan)) {
                $total_aset = $row['stok'] * $row['harga_beli'];
                $grand_total_aset += $total_aset; // Menjumlahkan seluruh nilai aset
        ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $row['kode_barang']; ?></td>
                <td><?= $row['nama_barang']; ?></td>
                <td><?= $row['nama_kategori']; ?></td>
                <td><?= $row['stok']; ?></td>
                <td><?= $row['stok_min']; ?></td>
                <td><?= $row['harga_beli']; ?></td>
                <td><?= $total_aset; ?></td>
            </tr>
        <?php 
            } 
        ?>
        <tr>
            <td colspan="7" style="text-align:right; font-weight:bold;">GRAND TOTAL NILAI ASET:</td>
            <td style="font-weight:bold;"><?= $grand_total_aset; ?></td>
        </tr>
        <?php
        } else { 
        ?>
            <tr>
                <td colspan="8">Belum ada data barang.</td>
            </tr>
        <?php } ?>
    </tbody>
</table>