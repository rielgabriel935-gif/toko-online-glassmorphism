<?php
session_start();
require_once 'config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama          = mysqli_real_escape_string($koneksi, trim($_POST['nama']));$email         = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password_raw  =$_POST['password'];
    $role_pilihan  =$_POST['role_pilihan'];

    if (empty($nama) || empty($email) || empty($password_raw)) {
        $error = "Semua kolom pendaftaran wajib diisi!";
    } else {
        $query_cek = "SELECT id FROM users WHERE email = '$email'";
        $res_cek   = mysqli_query($koneksi,$query_cek);

        if (mysqli_num_rows($res_cek) > 0) {$error = "Email sudah terdaftar, silakan gunakan email lain!";
        } else {
            // Enkripsi password dengan aman menggunakan password_hash
            $hashed_password = password_hash($password_raw, PASSWORD_DEFAULT);

            if ($role_pilihan === 'penjual') {$role = 'pembeli';
                $status_penjual = 'pending';$msg_sukses = "Pendaftaran penjual berhasil! Akun Anda sedang menunggu persetujuan (ACC) dari Admin.";
            } else {
                $role = 'pembeli';
                $status_penjual = 'none';$msg_sukses = "Pendaftaran berhasil! Silakan login dengan akun Anda.";
            }

            $query_insert = "INSERT INTO users (nama, email, password, role, status_penjual) 
                             VALUES ('$nama', '$email', '$hashed_password', '$role', '$status_penjual')";

            if (mysqli_query($koneksi,$query_insert)) {
                $success =$msg_sukses;
            } else {
                $error = "Gagal mendaftar: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - TokoSaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-box {
            background: rgba(0, 0, 0, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
    </style>
</head>
<body class="font-sans relative flex items-center justify-center min-h-screen p-4 text-white">

    <div class="fixed inset-0 -z-10 overflow-hidden">
        <video autoplay loop muted playsinline class="w-full h-full object-cover">
            <source src="assets/videos/Tuempek.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black/25"></div>
    </div>

    <div class="glass-box p-8 rounded-2xl w-full max-w-md my-8">
        <h1 class="text-2xl font-bold text-white text-center mb-1 drop-shadow-md">Daftar Akun Baru</h1>
        <p class="text-sm text-gray-200 text-center mb-6 drop-shadow-sm">Pilih jenis akun yang ingin Anda daftarkan</p>

        <?php if (!empty($error)): ?>
            <div class="bg-red-500/30 border border-red-500/50 text-red-100 px-4 py-3 rounded-xl text-sm mb-4 backdrop-blur-md">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-emerald-500/30 border border-emerald-500/50 text-emerald-100 px-4 py-3 rounded-xl text-sm mb-4 backdrop-blur-md">
                <?= htmlspecialchars($success); ?>
                <div class="mt-3">
                    <a href="login.php" class="inline-block bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-xl text-xs transition">Klik di sini untuk Login</a>
                </div>
            </div>
        <?php else: ?>

        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" required placeholder="Masukkan nama lengkap Anda"
                       autocomplete="name" readonly onfocus="this.removeAttribute('readonly');"
                       class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-400 focus:bg-black/30 focus:outline-none backdrop-blur-md transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Alamat Email</label>
                <input type="email" name="email" required placeholder="Masukkan email aktif Anda"
                       autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"
                       class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-400 focus:bg-black/30 focus:outline-none backdrop-blur-md transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="passwordInput" required placeholder="Buat password yang kuat"   
                           autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');"
                           class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 pr-10 text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-400 focus:bg-black/30 focus:outline-none backdrop-blur-md transition">
                    
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-300 hover:text-white focus:outline-none">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Daftar Sebagai</label>
                <select name="role_pilihan" 
                        class="w-full bg-gray-900/80 border border-white/30 rounded-xl p-2.5 text-white focus:ring-2 focus:ring-cyan-400 focus:outline-none backdrop-blur-md transition">
                    <option value="pembeli" class="bg-gray-900 text-white">Pembeli (Untuk Belanja Produk)</option>
                    <option value="penjual" class="bg-gray-900 text-white">Penjual (Butuh Verifikasi/ACC Admin)</option>
                </select>
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3 rounded-xl transition duration-300 shadow-lg shadow-blue-500/30">
                Daftar Sekarang
            </button>
        </form>
        <?php endif; ?>

        <p class="text-sm text-gray-200 text-center mt-6 drop-shadow-sm">
            Sudah punya akun? <a href="login.php" class="text-cyan-300 font-semibold hover:underline">Login di sini</a>
        </p>
    </div>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('passwordInput');
        const eyeIcon = document.getElementById('eyeIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />`;
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.88 9.88" />`;
        }
    }
    </script>
</body>
</html>