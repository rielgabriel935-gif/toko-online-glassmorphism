<?php
session_start();
require_once 'config/koneksi.php';

$error = '';$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email         = mysqli_real_escape_string($koneksi, trim($_POST['email']));$no_hp         = mysqli_real_escape_string($koneksi, trim($_POST['no_hp']));
    $password_baru =$_POST['password_baru'];

    if (empty($email) || empty($no_hp) || empty($password_baru)) {$error = "Semua kolom wajib diisi!";
    } else {
        $query = "SELECT * FROM users WHERE email = '$email' AND no_hp = '$no_hp'";
        $result = mysqli_query($koneksi,$query);

        if (mysqli_num_rows($result) === 1) {
            // Enkripsi password baru sebelum disimpan ke database.
            $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
            $update = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
            
            if (mysqli_query($koneksi, $update)) {$success = "Kata sandi berhasil diubah! Silakan login kembali.";
            } else {
                $error = "Gagal memperbarui kata sandi: " . mysqli_error($koneksi);
            }
        } else {
            $error = "Email atau Nomor HP tidak ditemukan / tidak cocok!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi - TokoSaya</title>
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
            <source src="assets/videos/Bagus.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black/25"></div>
    </div>

    <div class="glass-box p-8 rounded-2xl w-full max-w-md">
        <h1 class="text-2xl font-bold text-white text-center mb-1 drop-shadow-md">Reset Kata Sandi</h1>
        <p class="text-sm text-gray-200 text-center mb-6 drop-shadow-sm">Masukkan email dan No HP terdaftar untuk verifikasi</p>

        <?php if (!empty($error)): ?>
            <div class="bg-red-500/30 border border-red-500/50 text-red-100 px-4 py-3 rounded-xl text-sm mb-4 backdrop-blur-md">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-green-500/30 border border-green-500/50 text-green-100 px-4 py-3 rounded-xl text-sm mb-4 backdrop-blur-md">
                <?= htmlspecialchars($success); ?>
                <div class="mt-3">
                    <a href="login.php" class="inline-block bg-green-600 hover:bg-green-500 text-white font-bold px-4 py-2 rounded-xl text-xs transition">Klik di sini untuk Login</a>
                </div>
            </div>
        <?php else: ?>

        <form action="lupa_password.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Email Akun</label>
                <input type="email" name="email" required placeholder="Masukkan email terdaftar"
                       autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"
                       class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-400 focus:bg-black/30 focus:outline-none backdrop-blur-md transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Nomor WhatsApp / HP</label>
                <input type="text" name="no_hp" required placeholder="Contoh: 08123456789"
                       autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"
                       class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-400 focus:bg-black/30 focus:outline-none backdrop-blur-md transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Kata Sandi Baru</label>
                <div class="relative">
                    <input type="password" name="password_baru" id="passwordInput" required placeholder="Masukkan kata sandi baru"
                           autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');"
                           class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 pr-10 text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-400 focus:bg-black/30 focus:outline-none backdrop-blur-md transition">
                    
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-300 hover:text-white focus:outline-none">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold py-3 rounded-xl transition duration-300 shadow-lg shadow-cyan-500/30">
                Perbarui Kata Sandi
            </button>
        </form>
        <?php endif; ?>

        <p class="text-sm text-gray-200 text-center mt-6 drop-shadow-sm">
            Ingat kata sandi? <a href="login.php" class="text-cyan-300 font-semibold hover:underline">Kembali Login</a>
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