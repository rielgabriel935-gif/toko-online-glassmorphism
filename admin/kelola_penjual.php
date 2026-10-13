<?php
session_start();

$koneksi = null;
require_once '../config/koneksi.php';

if (!isset($koneksi) || !$koneksi) {
    die('Koneksi database gagal.');
}

// Proteksi Khusus Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Proses ACC / Tolak Penjual
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_user = intval($_GET['id']);
    $action  = $_GET['action'];

    if ($action === 'approve') {
        mysqli_query($koneksi, "UPDATE users SET role = 'penjual', status_penjual = 'approved' WHERE id = $id_user");
    } elseif ($action === 'reject') {
        mysqli_query($koneksi, "UPDATE users SET status_penjual = 'rejected' WHERE id = $id_user");
    }
    header("Location: kelola_penjual.php?msg=updated");
    exit;
}

// Ambil daftar pengajuan penjual (status_penjual = 'pending')
$query  = "SELECT * FROM users WHERE status_penjual = 'pending' ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola & ACC Penjual - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-box {
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
    </style>
</head>
<body class="font-sans relative min-h-screen text-gray-100 bg-gray-950 p-6">

    <!-- BACKGROUND VIDEO JERNIH -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <video autoplay loop muted playsinline class="w-full h-full object-cover">
            <source src="../assets/videos/Kopet.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black/30"></div>
    </div>

    <div class="max-w-6xl mx-auto">
        <!-- HEADER -->
        <div class="flex flex-wrap justify-between items-center mb-8 gap-4 glass-box p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-black text-white drop-shadow">👥 Persetujuan (ACC) Akun Penjual</h1>
                <p class="text-sm text-gray-300 mt-1">Daftar pengguna yang mengajukan diri menjadi Penjual</p>
            </div>
            <div class="flex gap-3">
                <a href="acc_produk.php" class="bg-gradient-to-r from-cyan-600 to-blue-600 hover:brightness-110 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition shadow-lg">
                    📦 ACC Produk Penjual
                </a>
                <a href="../index.php" class="bg-white/10 hover:bg-white/20 text-gray-200 border border-white/20 px-4 py-2.5 rounded-xl text-sm transition backdrop-blur-md">
                    🏠 Beranda
                </a>
            </div>
        </div>

        <!-- TABEL PENGAJUAN PENJUAL -->
        <div class="glass-box rounded-2xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-200">
                    <thead class="bg-white/10 text-gray-100 uppercase text-xs font-bold border-b border-white/10">
                        <tr>
                            <th class="p-4">Nama Lengkap</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Status Pengajuan</th>
                            <th class="p-4 text-center">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                <tr class="hover:bg-white/5 transition">
                                    <td class="p-4 font-bold text-white"><?= htmlspecialchars($user['nama']); ?></td>
                                    <td class="p-4 text-cyan-300"><?= htmlspecialchars($user['email']); ?></td>
                                    <td class="p-4">
                                        <span class="bg-amber-500/20 text-amber-300 border border-amber-500/40 px-3 py-1 rounded-full text-xs font-semibold">
                                            ⏳ Menunggu ACC
                                        </span>
                                    </td>
                                    <td class="p-4 text-center space-x-2">
                                        <a href="kelola_penjual.php?action=approve&id=<?= $user['id']; ?>" 
                                           onclick="return confirm('Setujui akun ini menjadi Penjual?')" 
                                           class="bg-emerald-500/30 hover:bg-emerald-500/50 text-emerald-300 border border-emerald-500/40 px-3 py-1.5 rounded-xl text-xs font-bold transition backdrop-blur-md">
                                            ✓ ACC Penjual
                                        </a>
                                        <a href="kelola_penjual.php?action=reject&id=<?= $user['id']; ?>" 
                                           onclick="return confirm('Tolak pengajuan akun ini?')" 
                                           class="bg-red-500/30 hover:bg-red-500/50 text-red-300 border border-red-500/40 px-3 py-1.5 rounded-xl text-xs font-bold transition backdrop-blur-md">
                                            ✗ Tolak
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-300">Tidak ada pengajuan penjual baru yang menunggu persetujuan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>