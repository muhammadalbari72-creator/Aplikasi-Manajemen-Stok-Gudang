<?php 
// Mulai sesi agar kita bisa menyimpan data user yang sedang login
session_start();

// Panggil koneksi database
include 'koneksi.php';

// Pastikan file ini diakses melalui tombol submit (POST), bukan diketik langsung di URL
if (isset($_POST['login'])) {
    
    // 1. Tangkap inputan user
    // Menggunakan mysqli_real_escape_string untuk mencegah serangan SQL Injection
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // 2. Cek kecocokan di database
    // Kita cari data di tabel 'users' yang username dan password-nya cocok dengan inputan
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    $cek = mysqli_num_rows($query); // Hitung ada berapa baris yang cocok

    // 3. Logika Penentuan
    if ($cek > 0) {
        // JIKA COCOK (LOGIN SUKSES)
        
        // Ambil data user tersebut dari database
        $data = mysqli_fetch_assoc($query);

        // Daftarkan data user ke dalam Session (Sesi)
        $_SESSION['id_user']      = $data['id_user'];
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
        $_SESSION['username']     = $data['username'];
        $_SESSION['role']         = $data['role']; // Sangat penting untuk membedakan Admin & Manajer
        $_SESSION['status']       = "sudah_login";

        // Alihkan (Redirect) ke halaman dashboard
        header("location:dashboard.php");
        exit();
        
    } else {
        // JIKA SALAH (LOGIN GAGAL)
        // Munculkan popup error dengan JavaScript, lalu kembalikan ke halaman login
        echo "<script>
                alert('Login Gagal! Username atau Password yang Anda masukkan salah.');
                window.location.href='login.php';
              </script>";
        exit();
    }
} else {
    // JIKA ADA YANG ISENG MENGAKSES FILE INI LANGSUNG DARI URL
    // Tendang langsung ke halaman login
    header("location:login.php");
    exit();
}
?>