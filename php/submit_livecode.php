<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['session_username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda belum login.']);
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];
$id_tugas = $_POST['id_tugas'];

// Jika tidak diisi, set ke string kosong '' agar tetap tersimpan
$html_code = isset($_POST['html_code']) ? $_POST['html_code'] : '';
$css_code = isset($_POST['css_code']) ? $_POST['css_code'] : '';
$js_code = isset($_POST['js_code']) ? $_POST['js_code'] : '';

// Cek apakah pengguna sudah mengumpulkan tugas
$query_check = $koneksi->prepare("SELECT * FROM pengumpulan_tugas WHERE id_tugas = ? AND id_pengguna = ?");
$query_check->bind_param("ii", $id_tugas, $id_pengguna);
$query_check->execute();
$result_check = $query_check->get_result();

if ($result_check->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Anda sudah mengumpulkan tugas ini!']);
    exit;
}

// Simpan ke database dengan string kosong jika tidak ada data yang diisi
$query = $koneksi->prepare("
    INSERT INTO pengumpulan_tugas (id_tugas, id_pengguna, html_code, css_code, js_code, tanggal_upload) 
    VALUES (?, ?, ?, ?, ?, NOW())
");

// Gunakan string kosong untuk input yang tidak diisi
$query->bind_param("iisss", $id_tugas, $id_pengguna, $html_code, $css_code, $js_code);
$query->execute();

echo json_encode(['status' => 'success', 'message' => 'Tugas berhasil dikumpulkan!']);
