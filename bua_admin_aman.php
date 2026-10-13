<?php
require_once 'config/koneksi.php';

$email = 'gabriel@gmail.com';
$password_polos = 'korincantik230506';

// Enkripsi password agar sinkron dengan password_verify() di login.php
$password_terenkripsi = password_hash($password_polos, PASSWORD_DEFAULT);

// Hapus akun lama jika ada, lalu buat baru sebagai admin
mysqli_query($koneksi, "DELETE FROM users WHERE email = '$email'");

$query = "INSERT INTO users (nama, email, password, role, lokasi_toko, no_hp) 
          VALUES ('Gabriel S. Sirait', '$email', '$password_terenkripsi', 'admin', 'Medan', '08123456789')";

if (mysqli_query($koneksi, $query)) {
    echo "<h3>Berhasil! Akun admin sudah dibuat dengan enkripsi yang benar.</h3>";
    echo "<p>Sekarang silakan kembali ke <a href='login.php'><b>login.php</b></a> dan login menggunakan:</p>";
    echo "<ul><li>Email: <b>$email</b></li><li>Password: <b>$password_polos</b></li></ul>";
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>