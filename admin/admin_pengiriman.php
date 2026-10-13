<?php
session_start();

$koneksi = null;
$koneksi_path = __DIR__ . '/../config/koneksi.php';

if (file_exists($koneksi_path)) {
    require_once $koneksi_path;
}

if (!isset($koneksi) || $koneksi === null) {
    $koneksi = mysqli_connect('localhost', 'root', '', 'toko_online');
    if (!$koneksi) {
        die('Koneksi database gagal: ' . mysqli_connect_error());
    }
}

// Cek hak akses admin/penjual (opsional sesuai sistem login kamu)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Proses Update Resi & Status Pengiriman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_kirim'])) {
    $order_id = (int)$_POST['order_id'];
    $status_baru = mysqli_real_escape_string($koneksi, $_POST['status_pesanan']);
    $no_resi = mysqli_real_escape_string($koneksi, $_POST['no_resi']);
    $kurir = mysqli_real_escape_string($koneksi, $_POST['kurir']);

    mysqli_query($koneksi, "UPDATE orders SET status = '$status_baru', no_resi = '$no_resi', kurir = '$kurir' WHERE id = $order_id");
    header("Location: admin_pengiriman.php?msg=updated");
    exit;
}

$orders = mysqli_query($koneksi, "SELECT * FROM orders ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengiriman Pesanan - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-white p-6 font-sans">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex justify-between items-center bg-gray-900 p-6 rounded-2xl border border-white/10">
            <h1 class="text-xl font-bold">🚚 Kelola Pengiriman & Resi Pesanan</h1>
            <a href="index.php" class="text-cyan-400 text-xs hover:underline">← Dashboard Admin</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 p-3 rounded-xl text-xs">
                ✅ Status pengiriman dan nomor resi berhasil diperbarui!
            </div>
        <?php endif; ?>

        <div class="space-y-4">
            <?php while ($ord = mysqli_fetch_assoc($orders)): ?>
                <div class="bg-gray-900/80 border border-white/10 p-5 rounded-2xl space-y-4">
                    <div class="flex flex-wrap justify-between items-center text-xs border-b border-white/10 pb-3">
                        <div>
                            <b>ID Transaksi: #ORD-<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?></b> | 
                            Pembeli: <span class="text-cyan-300"><?= htmlspecialchars($ord['nama_pembeli'] ?? 'Pelanggan'); ?></span>
                        </div>
                        <span class="text-gray-400">📅 <?= $ord['created_at']; ?></span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div>
                            <p class="text-gray-400 mb-1">📍 Alamat & Kontak:</p>
                            <p class="font-medium"><?= htmlspecialchars($ord['alamat']); ?> (HP: <?= htmlspecialchars($ord['telepon']); ?>)</p>
                            <p class="mt-2 text-gray-400">📦 Metode Pengiriman:</p>
                            <span class="bg-blue-500/20 text-blue-300 px-2.5 py-1 rounded-md font-semibold">
                                <?= htmlspecialchars($ord['metode_pengiriman'] ?? 'Kurir Toko'); ?>
                            </span>
                        </div>

                        <!-- Form Update Pengiriman -->
                        <form action="admin_pengiriman.php" method="POST" class="space-y-3 bg-black/30 p-3 rounded-xl border border-white/10">
                            <input type="hidden" name="order_id" value="<?= $ord['id']; ?>">
                            
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] text-gray-400 mb-1">Status Pesanan:</label>
                                    <select name="status_pesanan" class="w-full bg-gray-800 border border-white/20 rounded-lg p-1.5 text-xs text-white">
                                        <option value="pending" <?= ($ord['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="diproses" <?= ($ord['status'] === 'diproses') ? 'selected' : ''; ?>>Diproses</option>
                                        <option value="dikirim" <?= ($ord['status'] === 'dikirim') ? 'selected' : ''; ?>>Dikirim</option>
                                        <option value="selesai" <?= ($ord['status'] === 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] text-gray-400 mb-1">Nama Kurir:</label>
                                    <input type="text" name="kurir" value="<?= htmlspecialchars($ord['kurir'] ?? ''); ?>" placeholder="Cth: JNE / Kurir Toko" class="w-full bg-gray-800 border border-white/20 rounded-lg p-1.5 text-xs text-white">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] text-gray-400 mb-1">Nomor Resi / Keterangan Kurir:</label>
                                <div class="flex gap-2">
                                    <input type="text" name="no_resi" value="<?= htmlspecialchars($ord['no_resi'] ?? ''); ?>" placeholder="Masukkan No Resi pengiriman..." class="flex-1 bg-gray-800 border border-white/20 rounded-lg p-1.5 text-xs text-white">
                                    <button type="submit" name="update_kirim" class="bg-cyan-600 hover:bg-cyan-500 px-4 py-1.5 rounded-lg font-bold text-xs">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>