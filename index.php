<?php
session_start();
require_once 'config/koneksi.php';

// Ambil data produk yang ada gambarnya saja
$query = "SELECT products.*, users.nama AS nama_penjual 
          FROM products 
          LEFT JOIN users ON products.seller_id = users.id 
          WHERE products.gambar IS NOT NULL AND products.gambar != ''
          ORDER BY products.id DESC";
$result = mysqli_query($koneksi, $query);

$is_logged_in   = isset($_SESSION['user_id']);
$user_role      = $is_logged_in ? $_SESSION['role'] : '';
$status_penjual = $is_logged_in ? ($_SESSION['status_penjual'] ?? 'none') : '';
$user_nama      = $is_logged_in ? $_SESSION['nama'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di TokoSaya</title>
     <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- AOS Library (Animation on Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-box {
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }
        
        .glass-card {
            background: rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .glass-card:hover {
            background: rgba(0, 0, 0, 0.3);
            border-color: rgba(99, 102, 241, 0.6);
            box-shadow: 0 0 25px rgba(79, 70, 229, 0.4);
        }
    </style>
</head>
<body class="font-sans relative min-h-screen text-gray-100 bg-gray-950">

    <div class="fixed inset-0 -z-10 overflow-hidden">
        <video autoplay loop muted playsinline class="w-full h-full object-cover">
            <source src="assets/videos/Kopet.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black/20"></div>
    </div>

    <header class="bg-black/20 backdrop-blur-md border-b border-white/10 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-black bg-gradient-to-r from-cyan-400 via-indigo-400 to-purple-500 bg-clip-text text-transparent tracking-wider">
                TOKO<span class="text-indigo-400">SAYA</span>
            </a>
            
            <div class="flex items-center space-x-4 text-sm font-medium">
                <a href="cart.php" class="hover:text-cyan-400 transition flex items-center gap-2 bg-white/10 hover:bg-white/20 px-4 py-2 rounded-full border border-white/10 backdrop-blur-md">
                    🛒 <span>Keranjang</span>
                </a>

                <?php if ($is_logged_in): ?>
                    <span class="bg-indigo-500/20 text-indigo-200 border border-indigo-500/30 px-3 py-1 rounded-full text-xs backdrop-blur-md">
                        Halo, <b><?= htmlspecialchars($user_nama); ?></b>
                    </span>

                    <?php if ($user_role === 'pembeli'): ?>
                        <a href="ajukan_penjual.php" class="bg-gradient-to-r from-amber-500 to-orange-500 hover:brightness-110 text-white px-4 py-2 rounded-xl transition shadow-lg shadow-orange-500/20">
                            <?= $status_penjual === 'pending' ? '⏳ Cek Status Penjual' : '🏪 Ingin Jualan? Klik Disini'; ?>
                        </a>
                    <?php elseif ($user_role === 'penjual'): ?>
                        <a href="kelola_produk.php" class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:brightness-110 text-white px-4 py-2 rounded-xl transition shadow-lg shadow-teal-500/20">
                            + Kelola Produk
                        </a>
                    <?php elseif ($user_role === 'admin'): ?>
                        <a href="admin/kelola_penjual.php" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:brightness-110 text-white px-4 py-2 rounded-xl transition shadow-lg shadow-purple-500/20">
                            👥 ACC Penjual
                        </a>
                        <a href="kelola_produk.php" class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:brightness-110 text-white px-4 py-2 rounded-xl transition shadow-lg shadow-blue-500/20">
                            📦 Kelola Produk
                        </a>
                    <?php endif; ?>

                    <a href="logout.php" class="bg-red-500/20 hover:bg-red-500/40 text-red-300 border border-red-500/30 px-4 py-2 rounded-xl transition backdrop-blur-md" onclick="return confirm('Yakin ingin keluar?')">
                        Logout
                    </a>

                <?php else: ?>
                    <a href="login.php" class="hover:text-white text-gray-200 px-4 py-2 rounded-xl transition bg-white/10 hover:bg-white/20 backdrop-blur-md">Masuk</a>
                    <a href="register.php" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:brightness-110 text-white px-5 py-2 rounded-xl transition shadow-lg shadow-indigo-500/25">Daftar</a>
                <?php endif; ?>
            </div>

            <a href="pesanan_saya.php" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-4 py-2 rounded-xl text-sm transition">
    📦 Pesanan Saya
</a>
        </div>
    </header>

    <main class="container mx-auto px-6 py-8">

        <section class="glass-box rounded-3xl p-8 md:p-12 mb-12 text-center max-w-4xl mx-auto shadow-2xl relative overflow-hidden">
            <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-wide leading-tight drop-shadow-md">
                Selamat Datang di <span class="bg-gradient-to-r from-cyan-400 via-indigo-300 to-purple-400 bg-clip-text text-transparent">TokoSaya</span>
            </h1>
            <p class="text-gray-100 text-base md:text-lg mb-8 max-w-2xl mx-auto leading-relaxed drop-shadow">
                Platform tempat Anda bisa <span class="text-cyan-300 font-semibold">membeli produk impian</span> dan <span class="text-indigo-300 font-semibold">menjual produk Anda sendiri</span> secara mudah, cepat, dan aman!
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <?php if (!$is_logged_in): ?>
                    <a href="login.php" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:brightness-110 text-white font-bold px-6 py-3 rounded-xl transition shadow-lg shadow-cyan-500/30 flex items-center gap-2">
                        <span>🔑 Login untuk Mulai Jualan</span>
                    </a>
                <?php elseif ($user_role === 'pembeli'): ?>
                    <a href="ajukan_penjual.php" class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:brightness-110 text-white font-bold px-6 py-3 rounded-xl transition shadow-lg shadow-emerald-500/30 flex items-center gap-2">
                        <span>🏪 Daftar Menjadi Penjual Sekarang</span>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section>
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-extrabold text-white drop-shadow-md">
                        Daftar Produk Terbaru
                    </h2>
                    <p class="text-gray-200 text-sm mt-1 drop-shadow">Jelajahi barang berkualitas langsung dari para penjual</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-7">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($produk = mysqli_fetch_assoc($result)): ?>
                        <?php 
                            $path_gambar = 'assets/images/' . $produk['gambar'];
                            if (empty($produk['gambar']) || !file_exists($path_gambar)) {
                                continue;
                            }
                        ?>
                        <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between transition-all duration-300 transform hover:-translate-y-1.5 group">
                            <div>
                                <div class="relative overflow-hidden h-52 bg-transparent">
                                    <img src="<?= $path_gambar; ?>" 
                                         alt="<?= htmlspecialchars($produk['nama_produk']); ?>" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                    
                                    <div class="absolute top-3 left-3 bg-black/40 backdrop-blur-md border border-white/20 px-3 py-1 rounded-full">
                                        <span class="text-[11px] font-medium text-cyan-300 flex items-center gap-1">
                                            👤 <?= htmlspecialchars($produk['nama_penjual'] ?? 'Toko Utama'); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <h3 class="text-lg font-bold text-white group-hover:text-cyan-300 transition line-clamp-1 mb-1 drop-shadow-md">
                                        <?= htmlspecialchars($produk['nama_produk']); ?>
                                    </h3>
                                    <p class="text-xs text-gray-200 line-clamp-2 mb-4 leading-relaxed drop-shadow">
                                        <?= htmlspecialchars($produk['deskripsi']); ?>
                                    </p>
                                    
                                    <div class="flex items-baseline justify-between pt-2 border-t border-white/10">
                                        <div>
                                            <span class="text-[10px] text-gray-300 block uppercase tracking-wider">Harga</span>
                                            <span class="text-xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-indigo-300 drop-shadow">
                                                Rp <?= number_format($produk['harga'], 0, ',', '.'); ?>
                                            </span>
                                        </div>
                                        <span class="text-xs bg-white/10 px-2.5 py-1 rounded-md text-gray-100 border border-white/20 backdrop-blur-md">
                                            Stok: <b class="text-white"><?= $produk['stok']; ?></b>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-5 pt-0">
                                <a href="cart.php?action=add&id=<?= $produk['id']; ?>" 
                                   class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600/80 via-purple-600/80 to-pink-600/80 hover:brightness-125 text-white font-bold py-3 rounded-xl transition duration-300 shadow-lg backdrop-blur-md border border-white/10">
                                    <span>+ Tambah ke Keranjang</span>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-20 glass-card rounded-3xl">
                        <p class="text-gray-200 text-lg">Belum ada produk yang tersedia saat ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>

</body>
</html>