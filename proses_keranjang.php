<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'add') {
    $product_id = (int)$_GET['id'];
    
    // Cek apakah produk sudah ada di keranjang
    $cek = mysqli_query($koneksi, "SELECT id, qty FROM cart WHERE user_id = $user_id AND product_id = $product_id");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "UPDATE cart SET qty = qty + 1 WHERE user_id = $user_id AND product_id = $product_id");
    } else {
        mysqli_query($koneksi, "INSERT INTO cart (user_id, product_id, qty) VALUES ($user_id, $product_id, 1)");
    }
    header("Location: keranjang.php");
    exit;
} elseif ($action === 'delete') {
    $cart_id = (int)$_GET['id'];
    mysqli_query($koneksi, "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
    header("Location: keranjang.php");
    exit;
}