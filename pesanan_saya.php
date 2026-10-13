<?php
session_start();
require_once 'config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Query untuk mengambil data pesanan
$query = "SELECT * FROM orders ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - TokoSaya</title>
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

    <div class="max-w-5xl mx-auto space-y-6">
        <!-- HEADER -->
        <div class="flex justify-between items-center glass-box p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-bold">📦 Status Pesanan Saya</h1>
                <p class="text-xs text-gray-300 mt-1">Pantau proses verifikasi dan pengiriman pesanan kamu di sini</p>
            </div>
            <a href="index.php" class="bg-white/10 hover:bg-white/20 text-cyan-300 border border-white/20 px-4 py-2 rounded-xl text-sm transition">
                ← Lanjut Belanja
            </a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
            <div class="bg-emerald-500/30 border border-emerald-500/50 text-emerald-100 p-4 rounded-2xl text-sm backdrop-blur-md">
                🎉 <b>Pesanan Berhasil Dibuat!</b> Bukti pembayaran kamu sedang diperiksa.
            </div>
        <?php endif; ?>

        <!-- DAFTAR PESANAN -->
        <div class="space-y-4">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                    <div class="glass-box p-6 rounded-2xl space-y-4">
                        <!-- Head Card -->
                        <div class="flex flex-wrap justify-between items-center border-b border-white/15 pb-3 gap-2">
                            <div>
                                <span class="text-xs text-gray-300">ID Transaksi:</span>
                                <b class="text-sm text-cyan-300 ml-1">#ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></b>
                                <span class="text-[11px] text-gray-400 ml-3">📅 <?= date('d M Y H:i', strtotime($order['created_at'])); ?></span>
                            </div>

                            <!-- BADGE STATUS -->
                            <div>
                                <?php 
                                $status = $order['status'] ?? 'pending';
                                if ($status === 'pending'): ?>
                                    <span class="bg-amber-500/20 text-amber-300 border border-amber-500/40 px-3 py-1 rounded-full text-xs font-semibold">
                                        ⏳ Menunggu Verifikasi Pembayaran
                                    </span>
                                <?php elseif ($status === 'diproses'): ?>
                                    <span class="bg-blue-500/20 text-blue-300 border border-blue-500/40 px-3 py-1 rounded-full text-xs font-semibold">
                                        ⚙️ Pesanan Sedang Diproses Penjual
                                    </span>
                                <?php elseif ($status === 'dikirim'): ?>
                                    <span class="bg-purple-500/20 text-purple-300 border border-purple-500/40 px-3 py-1 rounded-full text-xs font-semibold">
                                        🚚 Sedang Dalam Pengiriman
                                    </span>
                                <?php elseif ($status === 'selesai'): ?>
                                    <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 px-3 py-1 rounded-full text-xs font-semibold">
                                        ✅ Pesanan Selesai
                                    </span>
                                <?php else: ?>
                                    <span class="bg-gray-500/20 text-gray-300 border border-gray-500/40 px-3 py-1 rounded-full text-xs font-semibold">
                                        <?= ucfirst($status); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- RINCIAN ITEM PRODUK -->
                        <div class="space-y-3">
                            <?php
                            $order_id = $order['id'];
                            $items_query = mysqli_query($koneksi, "SELECT order_items.*, products.nama_produk, products.gambar 
                                                                  FROM order_items 
                                                                  JOIN products ON order_items.product_id = products.id 
                                                                  WHERE order_items.order_id = $order_id");
                            if ($items_query && mysqli_num_rows($items_query) > 0):
                                while ($item = mysqli_fetch_assoc($items_query)):
                                    $qty = $item['qty'] ?? 1;
                                    $harga_satuan = $item['harga_satuan'] ?? 0;
                                    $subtotal = $item['subtotal'] ?? ($qty * $harga_satuan);
                            ?>
                                    <div class="flex items-center gap-4 bg-white/5 p-3 rounded-xl border border-white/10">
                                        <img src="assets/images/<?= $item['gambar'] ?? 'default.jpg'; ?>" class="w-14 h-14 object-cover rounded-lg border border-white/20" onerror="this.src='https://via.placeholder.com/100?text=No+Image'">
                                        <div class="flex-1">
                                            <h4 class="font-bold text-sm text-white"><?= htmlspecialchars($item['nama_produk'] ?? 'Produk'); ?></h4>
                                            <p class="text-xs text-gray-300">
                                                Rp <?= number_format($harga_satuan, 0, ',', '.'); ?> × <?= $qty; ?> unit
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-semibold text-yellow-300">
                                                Rp <?= number_format($subtotal, 0, ',', '.'); ?>
                                            </span>
                                        </div>
                                    </div>
                            <?php 
                                endwhile;
                            else: 
                            ?>
                                <p class="text-xs text-gray-400 italic px-2">Rincian item untuk transaksi ini sedang dimuat.</p>
                            <?php endif; ?>
                        </div>

                <!-- ALAMAT & INFORMASI PENGIRIMAN -->
<div class="bg-black/30 p-3.5 rounded-xl border border-white/10 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
    <div>
        <span class="text-gray-400 block mb-0.5">📍 Alamat & Metode Pengiriman:</span>
        <p class="text-gray-200"><?= htmlspecialchars($order['alamat']); ?> (HP: <?= htmlspecialchars($order['telepon']); ?>)</p>
        <span class="inline-block mt-1 bg-cyan-500/20 text-cyan-300 px-2 py-0.5 rounded font-semibold text-[11px]">
            🚚 <?= htmlspecialchars($order['metode_pengiriman'] ?? 'Kurir Toko'); ?>
        </span>
    </div>
    <div>
        <span class="text-gray-400 block mb-0.5">📦 Status Kurir & Nomor Resi:</span>
        <?php if (!empty($order['kurir']) && $order['kurir'] !== '-'): ?>
            <p class="text-gray-300">Kurir: <b class="text-white"><?= htmlspecialchars($order['kurir']); ?></b></p>
        <?php endif; ?>
        
        <?php if (!empty($order['no_resi'])): ?>
            <span class="text-cyan-300 font-mono font-bold text-sm block mt-1">Resi: <?= htmlspecialchars($order['no_resi']); ?></span>
        <?php else: ?>
            <span class="text-gray-400 italic block mt-1">Nomor resi belum di-input penjual</span>
        <?php endif; ?>
    </div>
</div>

                        <!-- TOTAL PEMBAYARAN -->
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-xs text-gray-300">Total Pembayaran:</span>
                            <span class="text-lg font-black text-yellow-300">Rp <?= number_format($order['total_harga'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="glass-box p-12 rounded-2xl text-center space-y-3">
                    <p class="text-gray-300 text-lg">Kamu belum memiliki riwayat pesanan.</p>
                    <a href="index.php" class="inline-block bg-gradient-to-r from-blue-600 to-cyan-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition shadow-lg">
                        Mulai Belanja Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>