<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Tambah produk
if ($action == 'add' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += 1;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }
    header("Location: cart.php");
    exit;
}

// Update Qty
if ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['qty'] as $id => $jumlah) {
        $jumlah = (int)$jumlah;
        if ($jumlah > 0) {
            $_SESSION['cart'][$id] = $jumlah;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
    header("Location: cart.php");
    exit;
}

// Hapus Item
if ($action == 'delete' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart.php");
    exit;
}

// Clear
if ($action == 'clear') {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

$cart_products = [];
$total_harga = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $query = "SELECT * FROM products WHERE id IN ($ids)";
    $result = mysqli_query($koneksi, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $cart_products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <header class="bg-blue-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold">TokoSaya</a>
            <a href="index.php" class="hover:underline">← Lanjut Belanja</a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Keranjang Belanja</h1>

        <?php if (!empty($cart_products)): ?>
            <form action="cart.php?action=update" method="POST">
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 uppercase text-xs">
                                <th class="p-4">Produk</th>
                                <th class="p-4">Harga</th>
                                <th class="p-4 text-center">Jumlah</th>
                                <th class="p-4">Subtotal</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($cart_products as $item): ?>
                                <?php 
                                    $qty = $_SESSION['cart'][$item['id']];
                                    $subtotal = $item['harga'] * $qty;
                                    $total_harga += $subtotal;
                                ?>
                                <tr>
                                    <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($item['nama_produk']); ?></td>
                                    <td class="p-4">Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                    <td class="p-4 text-center">
                                        <input type="number" name="qty[<?= $item['id']; ?>]" value="<?= $qty; ?>" min="1" class="w-16 border rounded p-1 text-center">
                                    </td>
                                    <td class="p-4 font-semibold text-blue-600">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                    <td class="p-4 text-center">
                                        <a href="cart.php?action=delete&id=<?= $item['id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Hapus barang ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-md">
                    <div class="flex gap-2">
                        <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg">Update Keranjang</button>
                        <a href="cart.php?action=clear" class="bg-red-100 text-red-600 px-4 py-2 rounded-lg" onclick="return confirm('Kosongkan keranjang?')">Kosongkan</a>
                    </div>
                    <div class="text-right">
                        <span class="text-gray-600">Total:</span>
                        <div class="text-3xl font-bold text-blue-600">Rp <?= number_format($total_harga, 0, ',', '.'); ?></div>
                    </div>
                </div>

                <div class="mt-6 text-right">
                    <a href="checkout.php" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold px-8 py-3 rounded-xl shadow-lg">Lanjut ke Checkout →</a>
                </div>
            </form>
        <?php else: ?>
            <div class="bg-white p-12 rounded-xl shadow-md text-center">
                <p class="text-gray-500 text-xl mb-4">Keranjang Anda kosong.</p>
                <a href="index.php" class="inline-block bg-blue-600 text-white px-6 py-2.5 rounded-lg">Mulai Belanja</a>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>