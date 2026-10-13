<?php
session_start();
require_once '../config/koneksi.php';
$koneksi = $koneksi ?? ($conn ?? null);

if (!($koneksi instanceof mysqli)) {
    die('Koneksi database tidak tersedia.');
}

// Hanya Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Proses ACC Produk
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $act = $_GET['action'];

    if ($act === 'approve') {
        mysqli_query($koneksi, "UPDATE products SET status_produk = 'active', status_bayar = 'lunas' WHERE id = $id");
    } elseif ($act === 'reject') {
        mysqli_query($koneksi, "UPDATE products SET status_produk = 'rejected' WHERE id = $id");
    }
    header("Location: acc_produk.php");
    exit;
}

$query = "SELECT products.*, users.nama AS nama_penjual 
          FROM products 
          LEFT JOIN users ON products.seller_id = users.id 
          WHERE products.status_produk = 'pending' 
          ORDER BY products.id DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACC Pengajuan Produk - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-box {
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="font-sans min-h-screen text-white bg-gray-950 p-6">

    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8 glass-box p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-bold">Verifikasi Pembayaran Biaya Admin Produk</h1>
                <p class="text-sm text-gray-300">Cek bukti transfer penjual sebelum menyetujui penayangan produk</p>
            </div>
            <a href="../index.php" class="bg-white/10 border border-white/20 hover:bg-white/20 px-4 py-2 rounded-xl text-sm">Kembali</a>
        </div>

        <div class="glass-box rounded-2xl overflow-hidden">
            <table class="w-full text-left text-sm text-gray-200">
                <thead class="bg-white/10 uppercase text-xs font-bold">
                    <tr>
                        <th class="p-4">Produk</th>
                        <th class="p-4">Penjual</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Biaya Admin</th>
                        <th class="p-4">Bukti Transfer</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-white/5">
                                <td class="p-4 font-bold"><?= htmlspecialchars($row['nama_produk']); ?></td>
                                <td class="p-4 text-cyan-300"><?= htmlspecialchars($row['nama_penjual']); ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['kategori']); ?></td>
                                <td class="p-4 text-yellow-300 font-semibold">Rp <?= number_format($row['biaya_admin'], 0, ',', '.'); ?></td>
                                <td class="p-4">
                                    <?php if (!empty($row['bukti_transfer'])): ?>
                                        <a href="../assets/images/<?= $row['bukti_transfer']; ?>" target="_blank" class="text-cyan-400 underline text-xs">
                                            🔍 Lihat Bukti TF
                                        </a>
                                    <?php else: ?>
                                        <span class="text-red-400 text-xs">Belum Upload</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center space-x-2">
                                    <a href="acc_produk.php?action=approve&id=<?= $row['id']; ?>" class="bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 px-3 py-1.5 rounded-xl text-xs font-bold hover:bg-emerald-500/50">
                                        ✓ ACC & Aktifkan
                                    </a>
                                    <a href="acc_produk.php?action=reject&id=<?= $row['id']; ?>" class="bg-red-500/30 text-red-300 border border-red-500/40 px-3 py-1.5 rounded-xl text-xs font-bold hover:bg-red-500/50" onclick="return confirm('Tolak produk ini?')">
                                        ✗ Tolak
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400">Tidak ada pengajuan produk yang menunggu verifikasi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>