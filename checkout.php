<?php
session_start();
require_once 'config/koneksi.php';

if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

$ids = implode(',', array_keys($_SESSION['cart']));
$query = "SELECT * FROM products WHERE id IN ($ids)";
$result = mysqli_query($koneksi, $query);

$cart_products = [];
$total_harga = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $row['qty'] = $_SESSION['cart'][$row['id']];
    $row['subtotal'] = $row['harga'] * $row['qty'];
    $total_harga += $row['subtotal'];
    $cart_products[] = $row;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $email   = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $telepon = mysqli_real_escape_string($koneksi, trim($_POST['telepon']));
    $alamat  = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));

    if (empty($nama) || empty($email) || empty($telepon) || empty($alamat)) {
        $error = 'Harap isi semua data!';
    } else {
        mysqli_begin_transaction($koneksi);

        try {
            $query_order = "INSERT INTO orders (nama_pembeli, email, telepon, alamat, total_harga, status) 
                            VALUES ('$nama', '$email', '$telepon', '$alamat', $total_harga, 'pending')";
            mysqli_query($koneksi, $query_order);
            $order_id = mysqli_insert_id($koneksi);

            foreach ($cart_products as $item) {
                $p_id  = $item['id'];
                $qty   = $item['qty'];
                $harga = $item['harga'];

                $query_item = "INSERT INTO order_items (order_id, product_id, jumlah, harga_satuan) 
                               VALUES ($order_id, $p_id, $qty, $harga)";
                mysqli_query($koneksi, $query_item);

                $query_stok = "UPDATE products SET stok = stok - $qty WHERE id = $p_id";
                mysqli_query($koneksi, $query_stok);
            }

            mysqli_commit($koneksi);
            unset($_SESSION['cart']);

            header("Location: checkout.php?success=1&order_id=" . $order_id);
            exit;

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $error = 'Gagal memproses pesanan.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - TokoSaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <header class="bg-blue-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold">TokoSaya</a>
            <a href="cart.php" class="hover:underline">← Kembali ke Keranjang</a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 max-w-4xl">
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-white p-8 rounded-xl shadow-md text-center">
                <div class="text-green-500 text-5xl mb-4">✓</div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Pesanan Berhasil Dibuat!</h1>
                <p class="text-gray-600 mb-6">Nomor Pesanan: <span class="font-bold text-blue-600">#<?= htmlspecialchars($_GET['order_id']); ?></span></p>
                <a href="index.php" class="bg-blue-600 text-white font-medium px-6 py-2.5 rounded-lg">Kembali ke Katalog</a>
            </div>
        <?php else: ?>
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Checkout</h1>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 bg-white p-6 rounded-xl shadow-md">
                    <h2 class="text-xl font-bold mb-4">Data Pengiriman</h2>
                    <form action="checkout.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                            <input type="text" name="nama" required class="w-full border rounded-lg p-2">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input type="email" name="email" required class="w-full border rounded-lg p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Telepon / WhatsApp</label>
                                <input type="text" name="telepon" required class="w-full border rounded-lg p-2">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" rows="3" required class="w-full border rounded-lg p-2"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition">
                            Buat Pesanan (Rp <?= number_format($total_harga, 0, ',', '.'); ?>)
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md h-fit">
                    <h2 class="text-xl font-bold mb-4">Ringkasan</h2>
                    <div class="divide-y mb-4">
                        <?php foreach ($cart_products as $item): ?>
                            <div class="py-2 flex justify-between text-sm">
                                <span><?= htmlspecialchars($item['nama_produk']); ?> (x<?= $item['qty']; ?>)</span>
                                <span class="font-semibold">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="border-t pt-2 flex justify-between font-bold text-gray-800">
                        <span>Total</span>
                        <span class="text-blue-600">Rp <?= number_format($total_harga, 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>