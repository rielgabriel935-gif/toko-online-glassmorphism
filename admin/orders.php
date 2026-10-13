<?php
// Mengambil koneksi dari folder config (naik 1 level)
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($koneksi) && isset($conn) && $conn instanceof mysqli) {
    $koneksi = $conn;
}

if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    die('Koneksi database tidak tersedia.');
}

$message = '';

// PROSES UPDATE STATUS PESANAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status   = mysqli_real_escape_string($koneksi, $_POST['status']);

    $query_update = "UPDATE orders SET status = '$status' WHERE id = $order_id";
    if (mysqli_query($koneksi, $query_update)) {
        $message = "Status pesanan #$order_id berhasil diperbarui!";
    } else {
        $message = "Gagal memperbarui status: " . mysqli_error($koneksi);
    }
}

// AMBIL SEMUA DATA PESANAN DARI DATABASE
$query_orders = "SELECT * FROM orders ORDER BY id DESC";
$result_orders = mysqli_query($koneksi, $query_orders);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans p-6">

    <div class="max-w-6xl mx-auto">
        <!-- Header Dashboard Admin -->
        <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-md mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Daftar Pesanan Masuk</h1>
                <p class="text-sm text-gray-500">Kelola dan pantau transaksi dari pelanggan Anda</p>
            </div>
            <div class="flex gap-3">
                <a href="tambah_produk.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    + Tambah Produk
                </a>
                <a href="../index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                    Ke Katalog Web →
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Tabel Pesanan -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <?php if (mysqli_num_rows($result_orders) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-800 text-white uppercase text-xs">
                                <th class="p-4">ID Order</th>
                                <th class="p-4">Data Pembeli</th>
                                <th class="p-4">Detail Items</th>
                                <th class="p-4">Total Bayar</th>
                                <th class="p-4">Status Pesanan</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            <?php while ($order = mysqli_fetch_assoc($result_orders)): ?>
                                <tr class="hover:bg-gray-50 align-top">
                                    <!-- ID Order & Tanggal -->
                                    <td class="p-4 font-bold text-blue-600">
                                        #<?= $order['id']; ?>
                                        <div class="text-xs text-gray-400 font-normal mt-1">
                                            <?= date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                        </div>
                                    </td>

                                    <!-- Data Pembeli -->
                                    <td class="p-4">
                                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['nama_pembeli']); ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($order['email']); ?></p>
                                        <p class="text-xs text-gray-500">Telp: <?= htmlspecialchars($order['telepon']); ?></p>
                                        <div class="mt-2 text-xs bg-gray-100 p-2 rounded text-gray-600 max-w-xs">
                                            <span class="font-semibold">Alamat:</span> <?= htmlspecialchars($order['alamat']); ?>
                                        </div>
                                    </td>

                                    <!-- Item yang dibeli -->
                                    <td class="p-4">
                                        <?php
                                        $o_id = $order['id'];
                                        $query_items = "SELECT order_items.*, products.nama_produk 
                                                        FROM order_items 
                                                        JOIN products ON order_items.product_id = products.id 
                                                        WHERE order_id = $o_id";
                                        $res_items = mysqli_query($koneksi, $query_items);
                                        ?>
                                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                                            <?php while ($item = mysqli_fetch_assoc($res_items)): ?>
                                                <li>
                                                    <span class="font-medium"><?= htmlspecialchars($item['nama_produk']); ?></span> 
                                                    (x<?= $item['jumlah']; ?>) - 
                                                    <span class="text-xs text-gray-500">Rp <?= number_format($item['harga_satuan'], 0, ',', '.'); ?></span>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>
                                    </td>

                                    <!-- Total Harga -->
                                    <td class="p-4 font-bold text-gray-800">
                                        Rp <?= number_format($order['total_harga'], 0, ',', '.'); ?>
                                    </td>

                                    <!-- Status -->
                                    <td class="p-4">
                                        <?php
                                        $status = strtolower($order['status']);
                                        $badge = "bg-yellow-100 text-yellow-800"; // default pending
                                        if ($status == 'diproses') $badge = "bg-blue-100 text-blue-800";
                                        if ($status == 'dikirim') $badge = "bg-purple-100 text-purple-800";
                                        if ($status == 'selesai') $badge = "bg-green-100 text-green-800";
                                        if ($status == 'batal') $badge = "bg-red-100 text-red-800";
                                        ?>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase <?= $badge; ?>">
                                            <?= htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>

                                    <!-- Form Update Status -->
                                    <td class="p-4 text-center">
                                        <form action="orders.php" method="POST" class="flex flex-col gap-1 items-center">
                                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                            <select name="status" class="text-xs border border-gray-300 rounded p-1 focus:ring-1 focus:ring-blue-500">
                                                <option value="pending" <?= $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="diproses" <?= $status == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                                <option value="dikirim" <?= $status == 'dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                                                <option value="selesai" <?= $status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                <option value="batal" <?= $status == 'batal' ? 'selected' : ''; ?>>Batal</option>
                                            </select>
                                            <button type="submit" name="update_status" class="bg-gray-800 hover:bg-black text-white text-xs px-2 py-1 rounded transition w-full mt-1">
                                                Ubah Status
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-12 text-center text-gray-500">
                    <p class="text-lg">Belum ada pesanan masuk dari pembeli.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>