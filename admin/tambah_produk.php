<?php
session_start();

if (isset($conn) && !isset($koneksi)) {
    $koneksi = $conn;
}

if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    require_once __DIR__ . '/../config/koneksi.php';
    if (isset($conn) && !isset($koneksi)) {
        $koneksi = $conn;
    }
}

if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    $koneksi = mysqli_connect('localhost', 'root', '', 'toko_online');
    if (!$koneksi) {
        die('Koneksi database gagal: ' . mysqli_connect_error());
    }
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'penjual')) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$message = '';
$error = '';

// Daftar Biaya Admin berdasarkan Kategori Produk
$tarif_admin = [
    'Pakaian' => 2000,
    'Makanan & Minuman' => 3000,
    'Elektronik' => 10000,
    'Aksesoris' => 2500,
    'Lainnya' => 5000
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = mysqli_real_escape_string($koneksi, trim($_POST['nama_produk']));
    $kategori    = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga       = (int)$_POST['harga'];
    $stok        = (int)$_POST['stok'];
    $deskripsi   = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));
    $biaya_admin = $tarif_admin[$kategori] ?? 5000;

    // Upload Gambar Produk
    $filename_produk = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $filename_produk = time() . '_prod.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/images/' . $filename_produk);
    }

    // Upload Bukti Transfer Biaya Admin
    $filename_bukti = '';
    if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === 0) {
        $ext_b = pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION);
        $filename_bukti = time() . '_bukti.' . $ext_b;
        move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], '../assets/images/' . $filename_bukti);
    }

    // Admin langsung 'active' & 'lunas', Penjual harus di-ACC Admin ('pending')
    $status_produk = ($user_role === 'admin') ? 'active' : 'pending';
    $status_bayar  = ($user_role === 'admin') ? 'lunas' : 'pending';

    $query = "INSERT INTO products (seller_id, nama_produk, kategori, harga, stok, deskripsi, gambar, biaya_admin, status_bayar, bukti_transfer, status_produk) 
              VALUES ($user_id, '$nama_produk', '$kategori', $harga, $stok, '$deskripsi', '$filename_produk', $biaya_admin, '$status_bayar', '$filename_bukti', '$status_produk')";

    if (mysqli_query($koneksi, $query)) {
        if ($user_role === 'admin') {
            $message = "Produk berhasil ditambahkan dan langsung aktif!";
        } else {
            $message = "Produk berhasil diajukan! Menunggu verifikasi pembayaran biaya admin oleh Admin.";
        }
    } else {
        $error = "Gagal menambahkan produk: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Baru - TokoSaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-box {
            background: transparent;
            backdrop-filter: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
    </style>
</head>
<body class="font-sans relative min-h-screen text-white bg-gray-950 p-6">

    <!-- BACKGROUND VIDEO JERNIH -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <video autoplay loop muted playsinline class="w-full h-full object-cover">
            <source src="../assets/videos/Kopet.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black/30"></div>
    </div>

    <div class="max-w-3xl mx-auto my-6">
        <div class="glass-box p-8 rounded-2xl">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Tambah Produk Baru</h1>
                <a href="../kelola_produk.php" class="text-cyan-300 hover:underline text-sm">← Kembali</a>
            </div>

            <?php if ($message): ?>
                <div class="bg-emerald-500/30 border border-emerald-500/50 text-emerald-100 p-3 rounded-xl text-sm mb-4 backdrop-blur-md"><?= htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-500/30 border border-red-500/50 text-red-100 p-3 rounded-xl text-sm mb-4 backdrop-blur-md"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="tambah_produk.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Produk</label>
                    <input type="text" name="nama_produk" required placeholder="Contoh: Sepatu Lari Nike" class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-white focus:outline-none focus:ring-2 focus:ring-cyan-400">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Kategori Produk</label>
                        <select name="kategori" id="kategoriSelect" onchange="updateBiayaAdmin()" class="w-full bg-gray-900/90 border border-white/30 rounded-xl p-2.5 text-white focus:outline-none">
                            <option value="Pakaian">Pakaian (Biaya Admin: Rp 2.000)</option>
                            <option value="Makanan & Minuman">Makanan & Minuman (Biaya Admin: Rp 3.000)</option>
                            <option value="Aksesoris">Aksesoris (Biaya Admin: Rp 2.500)</option>
                            <option value="Elektronik">Elektronik (Biaya Admin: Rp 10.000)</option>
                            <option value="Lainnya">Lainnya (Biaya Admin: Rp 5.000)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Harga Produk (Rp)</label>
                        <input type="number" name="harga" required placeholder="50000" class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-white focus:outline-none focus:ring-2 focus:ring-cyan-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Stok Produk</label>
                        <input type="number" name="stok" required placeholder="10" class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Gambar Produk</label>
                        <input type="file" name="gambar" accept="image/*" required class="w-full bg-white/10 border border-white/30 rounded-xl p-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Deskripsi Produk</label>
                    <textarea name="deskripsi" rows="3" class="w-full bg-white/10 border border-white/30 rounded-xl p-2.5 text-white focus:outline-none" placeholder="Tuliskan detail produk Anda..."></textarea>
                </div>

                <!-- INFORMASI METODE PEMBAYARAN BIAYA ADMIN -->
                <?php if ($user_role === 'penjual'): ?>
                    <div class="bg-white/10 border border-cyan-400/40 p-5 rounded-2xl backdrop-blur-md space-y-3">
                        <h3 class="font-bold text-cyan-300 text-base">💳 Pembayaran Biaya Upload Produk</h3>
                        <p class="text-xs text-gray-200">Silakan transfer biaya admin sebesar <b id="displayBiaya" class="text-yellow-300 text-sm">Rp 2.000</b> ke salah satu rekening/e-wallet Admin berikut:</p>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-xs bg-black/40 p-3 rounded-xl border border-white/10">
                            <div><strong>DANA:</strong><br><span class="text-cyan-300">085122084825</span></div>
                                <div><strong>OVO:</strong><br><span class="text-cyan-300">081262215926</span></div>
                        <div><strong>GoPay:</strong><br><span class="text-cyan-300">081262215926</span></div>
                            <div><strong>SeaBank:</strong><br><span class="text-cyan-300">9010-4228-8024</span></div>
                        </div>

                        <!-- Gambar QRIS Admin -->
                    <div class="text-center py-2">
                        <p class="text-xs font-semibold mb-1 text-gray-200">Atau Scan QRIS All Payment:</p>
    <img src="../assets/images/Qris_admin.jpeg"
        alt="QRIS Admin" 
        class="w-36 h-36 mx-auto rounded-xl border border-white/20 bg-white p-2 shadow-lg object-contain">
                    </div>

                        <p class="text-xs text-gray-200">Setelah transfer, unggah bukti transfer di bawah ini agar Admin dapat memverifikasi pembayaran Anda.</p>
                        <div class="text-xs text-gray-400">*Biaya admin akan digunakan untuk biaya operasional dan pengembangan platform.</div>

                        <div class="border-t border-white/20 my-3"></div>

                        <div class="text-sm font-semibold text-gray-200 mb-1">Unggah Bukti Transfer</div>
                        <div class="text-xs text-gray-400 mb-2">Pastikan bukti transfer jelas terlihat dan sesuai dengan jumlah yang ditentukan.</div>
</div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Unggah Bukti Transfer Biaya Admin</label>
                            <input type="file" name="bukti_transfer" accept="image/*" required class="w-full bg-white/10 border border-white/30 rounded-xl p-2 text-sm">
                        </div>
                    </div>
                <?php endif; ?>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:brightness-110 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-blue-500/30">
                    Submit & Kirim ke Admin
                </button>
            </form>
        </div>
    </div>

    <script>
    function updateBiayaAdmin() {
        const tarif = {
            'Pakaian': 'Rp 2.000',
            'Makanan & Minuman': 'Rp 3.000',
            'Aksesoris': 'Rp 2.500',
            'Elektronik': 'Rp 10.000',
            'Lainnya': 'Rp 5.000'
        };
        const selected = document.getElementById('kategoriSelect').value;
        document.getElementById('displayBiaya').innerText = tarif[selected] || 'Rp 5.000';
    }
    </script>
</body>
</html>