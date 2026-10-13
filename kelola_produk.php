<?php
session_start();

// Karena kelola_produk.php ada di folder utama, koneksi panggil config/koneksi.php
require_once 'config/koneksi.php';

// Validasi akses (Hanya penjual & admin yang boleh masuk)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'penjual')) {
    header("Location: login.php");
    exit;
}

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// FITUR HAPUS PRODUK
if (isset($_GET['hapus'])) {
    $id_produk = intval($_GET['hapus']);

    // Admin bisa hapus semua, Penjual hanya bisa hapus produk miliknya sendiri
    if ($user_role === 'admin') {
        $q_cek = "SELECT gambar FROM products WHERE id = $id_produk";
    } else {
        $q_cek = "SELECT gambar FROM products WHERE id = $id_produk AND seller_id = $user_id";
    }

    $res_cek = mysqli_query($koneksi, $q_cek);
    if (mysqli_num_rows($res_cek) > 0) {
        $data = mysqli_fetch_assoc($res_cek);
        
        // Hapus file gambar dari folder assets/images/ jika ada
        if (!empty($data['gambar']) && file_exists('assets/images/' . $data['gambar'])) {
            unlink('assets/images/' . $data['gambar']);
        }

        // Hapus dari database
        mysqli_query($koneksi, "DELETE FROM products WHERE id = $id_produk");
        header("Location: kelola_produk.php?msg=deleted");
        exit;
    }
}

// AMBIL DATA PRODUK
if ($user_role === 'admin') {
    $query = "SELECT products.*, users.nama AS nama_penjual 
              FROM products 
              LEFT JOIN users ON products.seller_id = users.id 
              ORDER BY products.id DESC";
} else {
    $query = "SELECT products.*, users.nama AS nama_penjual 
              FROM products 
              LEFT JOIN users ON products.seller_id = users.id 
              WHERE products.seller_id = $user_id 
              ORDER BY products.id DESC";
}
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - TokoSaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-box {
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
    </style>
</head>
<body class="font-sans relative min-h-screen text-gray-100 bg-gray-950 p-6">

    <div class="fixed inset-0 -z-10 overflow-hidden">
        <video autoplay loop muted playsinline class="w-full h-full object-cover">
            <source src="assets/videos/Kopet.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black/30"></div>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="flex flex-wrap justify-between items-center mb-8 gap-4 glass-box p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-black text-white drop-shadow">Kelola Data Produk</h1>
                <p class="text-sm text-gray-300 mt-1">Daftar produk yang Anda tampilkan di marketplace</p>
            </div>
            <div class="flex gap-3">
                <a href="admin/tambah_produk.php" class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:brightness-110 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition shadow-lg shadow-teal-500/20">
                    + Tambah Produk Baru
                </a>
                <a href="index.php" class="bg-white/10 hover:bg-white/20 text-gray-200 border border-white/20 px-4 py-2.5 rounded-xl text-sm transition backdrop-blur-md">
                    🏠 Kembali ke Beranda
                </a>
            </div>
        </div>

        <div class="glass-box rounded-2xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-200">
                    <thead class="bg-white/10 text-gray-100 uppercase text-xs font-bold border-b border-white/10">
                        <tr>
                            <th class="p-4">Gambar</th>
                            <th class="p-4">Nama Produk</th>
                            <th class="p-4">Penjual</th>
                            <th class="p-4">Harga</th>
                            <th class="p-4">Stok</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php $path = 'assets/images/' . $row['gambar']; ?>
                                <tr class="hover:bg-white/5 transition">
                                    <td class="p-4">
                                        <img src="<?= file_exists($path) && !empty($row['gambar']) ? $path : 'https://via.placeholder.com/100?text=No+Img'; ?>" 
                                             class="w-16 h-16 object-cover rounded-xl border border-white/20 shadow-md">
                                    </td>
                                    <td class="p-4 font-bold text-white"><?= htmlspecialchars($row['nama_produk']); ?></td>
                                    <td class="p-4 text-cyan-300 font-medium"><?= htmlspecialchars($row['nama_penjual'] ?? 'Toko Utama'); ?></td>
                                    <td class="p-4 text-emerald-300 font-semibold">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <td class="p-4 font-semibold"><?= $row['stok']; ?></td>
                                    <td class="p-4 text-center">
                                        <a href="kelola_produk.php?hapus=<?= $row['id']; ?>" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')" 
                                           class="bg-red-500/20 hover:bg-red-500/40 text-red-300 border border-red-500/30 px-3 py-1.5 rounded-xl text-xs font-semibold transition backdrop-blur-md">
                                            🗑️ Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-300">Belum ada produk yang Anda tambahkan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>