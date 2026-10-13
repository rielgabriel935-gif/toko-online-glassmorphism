<?php
session_start();
require_once '../config/koneksi.php';

if (!$koneksi instanceof mysqli) {
    die('Koneksi database tidak tersedia.');
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Update Tarif Biaya Admin Dinamis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tarif'])) {
    $kategori = $_POST['kategori'];
    $biaya = (int)$_POST['biaya_admin'];
    mysqli_query($koneksi, "INSERT INTO admin_settings (kategori, biaya_admin) VALUES ('$kategori', $biaya) ON DUPLICATE KEY UPDATE biaya_admin = $biaya");
    header("Location: keuangan_admin.php?msg=updated");
    exit;
}

// Rekap Total Pendapatan Biaya Upload Admin
$total_query = mysqli_query($koneksi, "SELECT SUM(biaya_admin) AS total_pemasukan FROM products WHERE status_bayar = 'lunas'");
$total_row   = mysqli_fetch_assoc($total_query);
$pemasukan   = $total_row['total_pemasukan'] ?? 0;

// Daftar Pengaturan Tarif
$tarif = mysqli_query($koneksi, "SELECT * FROM admin_settings");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan & Tarif Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-box {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="font-sans min-h-screen text-white bg-gray-950 p-6 relative">

    <div class="fixed inset-0 -z-10 overflow-hidden">
        <video autoplay loop muted playsinline class="w-full h-full object-cover">
            <source src="../assets/videos/Kopet.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black/40"></div>
    </div>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex justify-between items-center glass-box p-6 rounded-2xl">
            <h1 class="text-2xl font-bold">💵 Laporan Keuangan Biaya Admin</h1>
            <a href="../index.php" class="text-cyan-300 hover:underline text-sm">← Beranda</a>
        </div>

        <div class="glass-box p-6 rounded-2xl text-center space-y-2">
            <p class="text-sm text-gray-300">Total Pemasukan dari Biaya Upload Produk Penjual:</p>
            <h2 class="text-4xl font-black text-emerald-400">Rp <?= number_format($pemasukan, 0, ',', '.'); ?></h2>
        </div>

        <!-- Form Ubah Tarif Dinamis -->
        <div class="glass-box p-6 rounded-2xl space-y-4">
            <h2 class="text-xl font-bold">⚙️ Pengaturan Tarif Biaya Admin per Kategori</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php while ($t = mysqli_fetch_assoc($tarif)): ?>
                    <form action="keuangan_admin.php" method="POST" class="bg-white/5 p-4 rounded-xl flex items-center justify-between gap-3 border border-white/10">
                        <input type="hidden" name="kategori" value="<?= $t['kategori']; ?>">
                        <input type="hidden" name="update_tarif" value="1">
                        <div>
                            <span class="font-bold text-sm block"><?= $t['kategori']; ?></span>
                            <span class="text-xs text-gray-300">Tarif Saat Ini: Rp <?= number_format($t['biaya_admin'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex gap-2">
                            <input type="number" name="biaya_admin" value="<?= $t['biaya_admin']; ?>" class="w-24 bg-gray-900 border border-white/30 rounded-lg p-1.5 text-xs text-white">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-xs px-3 py-1.5 rounded-lg font-bold">Simpan</button>
                        </div>
                    </form>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

</body>
</html>