<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil item keranjang
$query = "SELECT cart.id AS cart_id, cart.qty, products.* 
          FROM cart 
          JOIN products ON cart.product_id = products.id 
          WHERE cart.user_id = $user_id";
$result = mysqli_query($koneksi, $query);

$total_bayar = 0;
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $subtotal = $row['harga'] * $row['qty'];
    $total_bayar += $subtotal;
    $row['subtotal'] = $subtotal;
    $items[] = $row;
}

// Proses Submit Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && count($items) > 0) {
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp  = mysqli_real_escape_string($koneksi, $_POST['no_hp']);

    // Upload Bukti Bayar
    $bukti = '';
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === 0) {
        $ext = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
        $bukti = time() . '_order.' . $ext;
        move_uploaded_file($_FILES['bukti']['tmp_name'], 'assets/images/' . $bukti);
    }

    // Insert Order Utama dengan Debugging Error
    $query_order = "INSERT INTO orders (buyer_id, total_harga, alamat_pengiriman, no_hp, bukti_pembayaran) 
                    VALUES ($user_id, $total_harga, '$alamat', '$no_hp', '$bukti')";
    
    if (!mysqli_query($koneksi, $query_order)) {
        die("Error pada Tabel Orders: " . mysqli_error($koneksi));
    }
    
    $order_id = mysqli_insert_id($koneksi);

    // Insert Order Items & Kurangi Stok Produk
    foreach ($items as $item) {
        $pid = $item['id'];
        $seller_id = $item['seller_id'];
        $qty = $item['qty'];
        $harga = $item['harga'];
        $sub = $item['subtotal'];

        mysqli_query($koneksi, "INSERT INTO order_items (order_id, product_id, seller_id, qty, harga_satuan, subtotal) 
                                VALUES ($order_id, $pid, $seller_id, $qty, $harga, $sub)");
        mysqli_query($koneksi, "UPDATE products SET stok = stok - $qty WHERE id = $pid");
    }

    // Bersihkan Keranjang
    mysqli_query($koneksi, "DELETE FROM cart WHERE user_id = $user_id");

    header("Location: pesanan_saya.php?msg=success");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - TokoSaya</title>
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
            <source src="assets/videos/Kopet.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black/40"></div>
    </div>

    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-6 glass-box p-6 rounded-2xl">
            <h1 class="text-2xl font-bold">🛒 Keranjang Belanja</h1>
            <a href="index.php" class="text-cyan-300 hover:underline">← Lanjut Belanja</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- List Items -->
            <div class="lg:col-span-2 space-y-4">
                <?php if (count($items) > 0): ?>
                    <?php foreach ($items as $item): ?>
                        <div class="glass-box p-4 rounded-2xl flex items-center justify-between gap-4">
                            <img src="assets/images/<?= $item['gambar']; ?>" class="w-20 h-20 object-cover rounded-xl border border-white/20">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg"><?= htmlspecialchars($item['nama_produk']); ?></h3>
                                <p class="text-xs text-gray-300">Rp <?= number_format($item['harga'], 0, ',', '.'); ?> x <?= $item['qty']; ?></p>
                                <p class="text-sm font-semibold text-cyan-300 mt-1">Subtotal: Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></p>
                            </div>
                            <a href="proses_keranjang.php?action=delete&id=<?= $item['cart_id']; ?>" class="text-red-400 hover:text-red-300 text-sm bg-red-500/20 px-3 py-1.5 rounded-xl border border-red-500/30">Hapus</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="glass-box p-8 rounded-2xl text-center text-gray-300">
                        Keranjang kamu masih kosong. Yuk cari produk impianmu!
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form Checkout -->
            <?php if (count($items) > 0): ?>
                <div class="glass-box p-6 rounded-2xl h-fit">
                    <h2 class="text-xl font-bold mb-4 border-b border-white/20 pb-2">Checkout Pesanan</h2>
                    <form action="keranjang.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Alamat Lengkap Pengiriman</label>
                            <textarea name="alamat" required rows="3" class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-xs text-white focus:outline-none" placeholder="Jl. Merdeka No. 123, Medan..."></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold mb-1">Nomor WhatsApp / HP</label>
                            <input type="text" name="no_hp" required class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-xs text-white focus:outline-none" placeholder="08123456789">
                        </div>

                        <div class="bg-black/30 p-3 rounded-xl border border-white/10 text-xs space-y-1">
                            <div class="flex justify-between"><span>Total Harga:</span> <b class="text-yellow-300 text-sm">Rp <?= number_format($total_bayar, 0, ',', '.'); ?></b></div>
                            <p class="text-[10px] text-gray-300 pt-1">Transfer ke QRIS Admin / Rekening Admin sebelum upload bukti.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold mb-1">Upload Bukti Transfer Pembelian</label>
                            <input type="file" name="bukti" accept="image/*" required class="w-full bg-white/10 border border-white/30 rounded-xl p-2 text-xs">
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:brightness-110 text-white font-bold py-3 rounded-xl transition shadow-lg text-sm">
                            Buat Pesanan Sekarang
                        </button>
                    </form>

                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-gray-200">🚚 Pilih Metode Pengantaran:</label>
                        <select name="metode_pengiriman" class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-xs text-white focus:outline-none">
                    <option value="Kurir Toko (COD)" class="bg-gray-900">Kurir Toko / Bayar di Tempat (COD)</option>
                    <option value="JNE Regular (REG)" class="bg-gray-900">JNE Regular (Estimasi 2-3 Hari)</option>
                    <option value="GoSend Instan" class="bg-gray-900">GoSend Instan (Khusus Dalam Kota)</option>
                    <option value="Ambil di Toko" class="bg-gray-900">Ambil Langsung ke Toko</option>
                    </select>
</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>