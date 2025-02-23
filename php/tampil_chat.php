<?php
session_start(); // Mulai session di awal file

// Periksa apakah session id_pengguna tersedia
if (!isset($_SESSION['id_pengguna'])) {
    echo "Anda belum login.";
    exit;
}

include 'koneksi.php'; // Sesuaikan dengan file koneksi database Anda

$id_proyek = $_GET['id_proyek'];
$id_kelompok = $_GET['id_kelompok'];
$id_pengguna_login = $_SESSION['id_pengguna']; // ID pengguna yang sedang login

// Query untuk mengambil pesan dan nama pengirim
$query = "
    SELECT d.*, p.namaLengkap 
    FROM diskusi d
    JOIN pengguna p ON d.id_pengguna = p.id_pengguna
    WHERE d.id_proyek = ? AND d.id_kelompok = ?
    ORDER BY d.waktu ASC
";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("ii", $id_proyek, $id_kelompok);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $nama_pengirim = htmlspecialchars($row['namaLengkap']);
    $chat = htmlspecialchars($row['chat']);
    $waktu = htmlspecialchars($row['waktu']);

    // Tentukan kelas CSS berdasarkan apakah pengirim adalah pengguna yang sedang login
    $chat_class = ($row['id_pengguna'] == $id_pengguna_login) ? 'chat-pengirim' : 'chat-teman';

    echo "
    <div class='chat-item $chat_class'>
        <div class='chat-header'>
            <span class='chat-nama'>$nama_pengirim</span>
            <span class='chat-waktu'>$waktu</span>
        </div>
        <p>$chat</p>
    </div>
";
}

$stmt->close();
$koneksi->close();
