<?php
include 'koneksi.php'; // Sesuaikan dengan file koneksi database Anda

$id_proyek = $_POST['id_proyek'];
$id_pengguna = $_POST['id_pengguna'];
$chat = $_POST['chat'];
$id_kelompok = $_POST['id_kelompok'];

// Validasi bahwa id_kelompok sesuai dengan kelompok pengguna
// Anda mungkin perlu menambahkan validasi tambahan di sini

$query = "INSERT INTO diskusi (id_proyek, id_pengguna, chat, id_kelompok) VALUES (?, ?, ?, ?)";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("iisi", $id_proyek, $id_pengguna, $chat, $id_kelompok);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$koneksi->close();
?>