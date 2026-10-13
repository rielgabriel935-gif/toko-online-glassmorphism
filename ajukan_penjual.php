<?php
session_start();
require_once 'config/koneksi.php';

// 1. Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error   = '';

// 2. Ambil status_penjual dan role terbaru pengguna dari database
$query_user  = "SELECT role, status_penjual FROM users WHERE id = $user_id";
$result_user = mysqli_query($koneksi, $query_user);
$user_data   = mysqli_fetch_assoc($result_user);

$role           = $user_data['role'];
$status_penjual = $user_data['status_penjual'];

// 3. Proses saat formulir pengajuan dikirimkan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajukan'])) {
    if ($role === 'pembeli' && ($status_penjual === 'none' || $status_penjual === 'rejected')) {
        $query_update = "UPDATE users SET status_penjual = 'pending' WHERE id = $user_id";
        
        if (mysqli_query($koneksi, $query_update)) {
            $_SESSION['status_penjual'] = 'pending';
            $status_penjual = 'pending';
            $message = "Permohonan menjadi penjual berhasil dikirim! Silakan tunggu persetujuan Admin.";
        } else {
            $error = "Gagal mengirim permohonan: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Menjadi Penjual - TokoSaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans min-h-screen">

    <!-- Header Navbar -->
    <header class="bg-blue-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold">TokoSaya</a>
            <div class="flex items-center gap-4">
                <span class="text-sm">Halo, <b><?= htmlspecialchars($_SESSION['nama']); ?></b></span>
                <a href="index.php" class="hover:underline text-sm">← Kembali ke Katalog</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-12 max-w-2xl">
        <div class="bg-white rounded-xl shadow-md p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Pendaftaran Akun Penjual</h1>
            <p class="text-gray-600 text-sm mb-6">Mulai buka tokomu sendiri dan jual produkmu kepada ribuan pembeli di TokoSaya.</p>

            <?php if (!empty($message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-sm mb-6">
                    <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- KONDISI STATUS PENGAJUAN -->
            <?php if ($role === 'penjual'): ?>
                <!-- Sudah di-ACC oleh admin -->
                <div class="bg-blue-50 border border-blue-200 p-6 rounded-lg text-center">
                    <div class="text-4xl mb-2">🎉</div>
                    <h2 class="text-lg font-bold text-blue-800 mb-1">Selamat! Anda Sudah Menjadi Penjual</h2>
                    <p class="text-sm text-blue-600 mb-4">Akun Anda telah disetujui. Anda sudah bisa mulai menambahkan dan mengelola produk jualan Anda.</p>
                    <a href="admin/tambah_produk.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg transition">
                        Buka Dashboard Penjual →
                    </a>
                </div>

            <?php elseif ($status_penjual === 'pending'): ?>
                <!-- Menunggu ACC Admin -->
                <div class="bg-yellow-50 border border-yellow-200 p-6 rounded-lg text-center">
                    <div class="text-4xl mb-2">⏳</div>
                    <h2 class="text-lg font-bold text-yellow-800 mb-1">Permohonan Sedang Diproses</h2>
                    <p class="text-sm text-yellow-700">Permohonan Anda untuk menjadi penjual sedang dalam tahap peninjauan oleh Administrator. Silakan cek halaman ini secara berkala.</p>
                </div>

            <?php elseif ($status_penjual === 'rejected'): ?>
                <!-- Pengajuan ditolak -->
                <div class="bg-red-50 border border-red-200 p-6 rounded-lg mb-6">
                    <h2 class="text-lg font-bold text-red-800 mb-1">Permohonan Ditolak</h2>
                    <p class="text-sm text-red-600">Mohon maaf, permohonan Anda sebelumnya belum dapat disetujui. Anda dapat mengajukan permohonan kembali di bawah ini.</p>
                </div>
                
                <form action="ajukan_penjual.php" method="POST">
                    <button type="submit" name="ajukan" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                        Ajukan Kembali Sebagai Penjual
                    </button>
                </form>

            <?php else: ?>
                <!-- Belum pernah mengajukan (status_penjual === 'none') -->
                <div class="border-t pt-6 space-y-4">
                    <h3 class="font-semibold text-gray-800">Syarat & Ketentuan Singkat:</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>Memiliki produk yang layak dan tidak melanggar hukum.</li>
                        <li>Bersedia mengelola stok dan memproses pesanan pembeli dengan jujur.</li>
                        <li>Permohonan akan diverifikasi oleh Admin toko terlebih dahulu.</li>
                    </ul>

                    <form action="ajukan_penjual.php" method="POST" class="pt-4">
                        <button type="submit" name="ajukan" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg shadow-lg transition">
                            Kirim Permohonan Menjadi Penjual
                        </button>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    </main>

</body>
</html>