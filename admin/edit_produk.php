<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($koneksi) || !$koneksi) {
    die('Koneksi database gagal.');
}

// Proteksi halaman: Hanya admin atau penjual
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'penjual')) {
    header("Location: ../login.php");
    exit;
}

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$message   = '';
$error     = '';

// 1. Ambil ID Produk yang ingin di-edit
if (!isset($_GET['id'])) {
    header("Location: tambah_produk.php");
    exit;
}

$product_id = (int)$_GET['id'];

// 2. Ambil data produk berdasarkan ID & Hak Akses
if ($user_role === 'admin') {
    $query = "SELECT * FROM products WHERE id = $product_id";
} else {
    // Penjual hanya bisa edit produk buatan sendiri
    $query = "SELECT * FROM products WHERE id = $product_id AND seller_id = $user_id";
}

$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) === 0) {
    echo "<script>alert('Produk tidak ditemukan atau Anda tidak memiliki akses!'); window.location='tambah_produk.php';</script>";
    exit;
}

$produk = mysqli_fetch_assoc($result);

// 3. Proses Update Data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $harga       = (int)$_POST['harga'];
    $stok        = (int)$_POST['stok'];
    $deskripsi   = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    $filename = $produk['gambar']; // Gambar lama sebagai default

    // Cek jika pengguna mengunggah gambar baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $filename = time() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/images/' . $filename);
    }

    $query_update = "UPDATE products SET 
                        nama_produk = '$nama_produk', 
                        harga = $harga, 
                        stok = $stok, 
                        deskripsi = '$deskripsi', 
                        gambar = '$filename' 
                     WHERE id = $product_id";

    if (mysqli_query($koneksi, $query_update)) {
        $message = "Produk berhasil diperbarui!";
        // Refresh data produk terbaru
        $res_updated = mysqli_query($koneksi, $query);
        $produk = mysqli_fetch_assoc($res_updated);
    } else {
        $error = "Gagal memperbarui produk: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - TokoSaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-lg mx-auto bg-white p-6 rounded-xl shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Produk</h1>
            <a href="tambah_produk.php" class="text-blue-600 hover:underline text-sm">← Kembali ke Kelola Produk</a>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 p-3 rounded mb-4 text-sm"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4 text-sm"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="edit_produk.php?id=<?= $product_id; ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Produk</label>
                <input type="text" name="nama_produk" value="<?= htmlspecialchars($produk['nama_produk']); ?>" required class="w-full border rounded p-2">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Harga (Rp)</label>
                    <input type="number" name="harga" value="<?= $produk['harga']; ?>" required class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Stok</label>
                    <input type="number" name="stok" value="<?= $produk['stok']; ?>" required class="w-full border rounded p-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Deskripsi Produk</label>
                <textarea name="deskripsi" rows="3" class="w-full border rounded p-2"><?= htmlspecialchars($produk['deskripsi']); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Gambar Produk Saat Ini</label>
                <img src="../assets/images/<?= htmlspecialchars($produk['gambar']); ?>" class="w-24 h-24 object-cover rounded mb-2 border">
                <input type="file" name="gambar" accept="image/*" class="w-full border rounded p-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">*Biarkan kosong jika tidak ingin mengubah gambar</p>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg transition">
                Simpan Perubahan
            </button>
        </form>
    </div>

</body>
</html>