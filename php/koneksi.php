<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "belajaro";

// Membuat koneksi ke database
$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Mengecek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}

// Pesan "Koneksi Berhasil" dihapus
// echo "Koneksi Berhasil";
?>