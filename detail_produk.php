<?php
session_start();
require_once 'config/koneksi.php';

$id_produk = (int)($_GET['id'] ?? 0);

// Ambil Detail Produk, Data Penjual, Lokasi, dan Nomor WA Penjual
$query = "SELECT products.*, users.nama AS nama_penjual, users.email AS email_penjual, users.no_hp AS wa_penjual, users.lokasi_toko 
          FROM products 
          JOIN users ON products.seller_id = users.id 
          WHERE products.id = $id_produk";
$res = mysqli_query($koneksi, $query);
$produk = mysqli_fetch_assoc($res);

if (!$produk) {
    header("Location: index.php");
    exit;
}

// Tambah Review Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $rating = (int)$_POST['rating'];
    $komentar = mysqli_real_escape_string($koneksi, $_POST['komentar']);

    mysqli_query($koneksi, "INSERT INTO reviews (product_id, user_id, rating, komentar) VALUES ($id_produk, $uid, $rating, '$komentar')");
    header("Location: detail_produk.php?id=$id_produk");
    exit;
}

// Ambil Review Produk
$reviews = mysqli_query($koneksi, "SELECT reviews.*, users.nama FROM reviews JOIN users ON reviews.user_id = users.id WHERE product_id = $id_produk ORDER BY id DESC");

// Rata-rata Rating
$avg_res = mysqli_query($koneksi, "SELECT AVG(rating) AS avg_rating FROM reviews WHERE product_id = $id_produk");
$avg_row = mysqli_fetch_assoc($avg_res);
$rating_rata = round($avg_row['avg_rating'] ?? 0, 1);

// Format Nomor WhatsApp Penjual (Pastikan format internasional tanpa angka 0 di depan, misal: 628...)
$no_wa_penjual = preg_replace('/[^0-9]/', '', $produk['wa_penjual'] ?? '08123456789');
if (substr($no_wa_penjual, 0, 1) === '0') {
    $no_wa_penjual = '62' . substr($no_wa_penjual, 1);
}

// Template Pesan Otomatis ke WhatsApp Penjual
$teks_wa = urlencode("Halo Kak " . $produk['nama_penjual'] . ", saya tertarik dengan produk *" . $produk['nama_produk'] . "* (Harga: Rp " . number_format($produk['harga'], 0, ',', '.') . ") yang dijual di lokasi *" . ($produk['lokasi_toko'] ?? 'Medan') . "*. Apakah stoknya masih tersedia?");
$link_wa = "https://wa.me/" . $no_wa_penjual . "?text=" . $teks_wa;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($produk['nama_produk']); ?> - TokoSaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-box {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
    </style>
</head>
<body class="font-sans min-h-screen text-white bg-gray-950 p-6 relative">

    <!-- BACKGROUND VIDEO -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <video autoplay loop muted playsinline class="w-full h-full object-cover">
            <source src="assets/videos/Kopet.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black/40"></div>
    </div>

    <div class="max-w-4xl mx-auto space-y-6">
        <a href="index.php" class="inline-block text-cyan-300 hover:underline text-sm">← Kembali ke Katalog</a>

        <div class="glass-box p-6 rounded-2xl grid grid-cols-1 md:grid-cols-2 gap-6">
            <img src="assets/images/<?= $produk['gambar']; ?>" class="w-full h-80 object-cover rounded-xl border border-white/20" onerror="this.src='https://via.placeholder.com/300?text=No+Image'">
            <div class="space-y-4">
                <div class="flex gap-2">
                    <span class="bg-cyan-500/20 text-cyan-300 px-3 py-1 rounded-full text-xs font-semibold border border-cyan-500/30"><?= htmlspecialchars($produk['kategori']); ?></span>
                    <span class="bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full text-xs font-semibold border border-purple-500/30">📍 <?= htmlspecialchars($produk['lokasi_toko'] ?? 'Medan'); ?></span>
                </div>

                <h1 class="text-3xl font-bold"><?= htmlspecialchars($produk['nama_produk']); ?></h1>
                <p class="text-2xl font-black text-yellow-300">Rp <?= number_format($produk['harga'], 0, ',', '.'); ?></p>
                
                <div class="text-xs text-gray-300 space-y-1 bg-white/5 p-3 rounded-xl border border-white/10">
                    <p><b>👤 Penjual:</b> <?= htmlspecialchars($produk['nama_penjual']); ?></p>
                    <p><b>📦 Stok Tersedia:</b> <?= $produk['stok']; ?> unit</p>
                    <p><b>⭐ Rating Produk:</b> ⭐ <?= $rating_rata; ?> / 5.0</p>
                </div>

                <p class="text-xs text-gray-200"><?= nl2br(htmlspecialchars($produk['deskripsi'])); ?></p>
                
                <!-- TOMBOL AKSI: KERANJANG & CHAT WA -->
                <div class="flex gap-3 pt-2">
                    <a href="proses_keranjang.php?action=add&id=<?= $produk['id']; ?>" class="flex-1 text-center bg-blue-600 hover:bg-blue-500 py-3 rounded-xl font-bold text-sm transition shadow-lg flex items-center justify-center gap-1">
                        🛒 + Keranjang
                    </a>
                    <a href="<?= $link_wa; ?>" target="_blank" class="flex-1 text-center bg-emerald-600 hover:bg-emerald-500 py-3 rounded-xl font-bold text-sm transition shadow-lg flex items-center justify-center gap-1.5">
                        💬 Chat WA Penjual
                    </a>
                </div>
            </div>
        </div>

        <!-- Ulasan & Rating Section -->
        <div class="glass-box p-6 rounded-2xl space-y-4">
            <h2 class="text-xl font-bold">⭐ Ulasan Pembeli</h2>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="detail_produk.php?id=<?= $id_produk; ?>" method="POST" class="bg-white/5 p-4 rounded-xl space-y-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Beri Rating (1 - 5):</label>
                        <select name="rating" class="bg-gray-900 border border-white/30 rounded-lg p-2 text-xs text-white">
                            <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Bagus)</option>
                            <option value="4">⭐⭐⭐⭐ (4 - Bagus)</option>
                            <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                            <option value="2">⭐⭐ (2 - Kurang)</option>
                            <option value="1">⭐ (1 - Buruk)</option>
                        </select>
                    </div>
                    <textarea name="komentar" required placeholder="Tulis ulasan kamu tentang produk ini..." class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-xs text-white focus:outline-none" rows="2"></textarea>
                    <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 px-4 py-2 rounded-xl text-xs font-bold">Kirim Ulasan</button>
                </form>
            <?php endif; ?>

            <div class="space-y-3">
                <?php if (mysqli_num_rows($reviews) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($reviews)): ?>
                        <div class="bg-white/5 p-3 rounded-xl border border-white/10 text-xs space-y-1">
                            <div class="flex justify-between">
                                <b class="text-cyan-300"><?= htmlspecialchars($r['nama']); ?></b>
                                <span>⭐ <?= $r['rating']; ?>/5</span>
                            </div>
                            <p class="text-gray-200"><?= htmlspecialchars($r['komentar']); ?></p>
                            <span class="text-[10px] text-gray-400"><?= $r['created_at']; ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-xs text-gray-400 italic">Belum ada ulasan untuk produk ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>