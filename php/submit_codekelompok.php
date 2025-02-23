<?php
session_start();
include 'koneksi.php'; // Koneksi ke database

header("Content-Type: application/json");

// Pastikan pengguna sudah login
if (!isset($_SESSION['session_username'])) {
    echo json_encode(["status" => "error", "message" => "Silakan login terlebih dahulu."]);
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];

// Ambil id_kelompok dari session atau database
if (!isset($_SESSION['id_kelompok'])) {
    $query_kelompok = $koneksi->prepare("SELECT id_kelompok FROM akses_kelompok WHERE id_pengguna = ?");
    $query_kelompok->bind_param("i", $id_pengguna);
    $query_kelompok->execute();
    $result_kelompok = $query_kelompok->get_result();
    $data_kelompok = $result_kelompok->fetch_assoc();

    if ($data_kelompok) {
        $_SESSION['id_kelompok'] = $data_kelompok['id_kelompok'];
    } else {
        echo json_encode(["status" => "error", "message" => "Anda belum tergabung dalam kelompok."]);
        exit;
    }
}

$id_kelompok = $_SESSION['id_kelompok'];

// Pastikan request berupa POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_tugas = $_POST['id_tugas'] ?? null;
    $id_proyek = $_POST['id_proyek'] ?? null;
    $html_code = $_POST['html_code'] ?? '';
    $css_code = $_POST['css_code'] ?? '';
    $js_code = $_POST['js_code'] ?? '';

    if (!$id_tugas || !$id_proyek) {
        echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
        exit;
    }

    // Cek apakah tugas sudah dikumpulkan oleh kelompok ini
    $query_cek = $koneksi->prepare("SELECT * FROM pengumpulan_tugas WHERE id_kelompok = ? AND id_tugas = ?");
    $query_cek->bind_param("ii", $id_kelompok, $id_tugas);
    $query_cek->execute();
    $result_cek = $query_cek->get_result();

    if ($result_cek->num_rows > 0) {
        // Jika tugas sudah ada, lakukan update
        $query_update = $koneksi->prepare("
            UPDATE pengumpulan_tugas 
            SET html_code = ?, css_code = ?, js_code = ?, tanggal_upload = NOW() 
            WHERE id_kelompok = ? AND id_tugas = ?
        ");
        $query_update->bind_param("sssii", $html_code, $css_code, $js_code, $id_kelompok, $id_tugas);
        $query_update->execute();
    } else {
        // Jika belum ada, insert tugas baru
        $query_insert = $koneksi->prepare("
            INSERT INTO pengumpulan_tugas (id_tugas, id_kelompok, id_pengguna, html_code, css_code, js_code, tanggal_upload) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $query_insert->bind_param("iiisss", $id_tugas, $id_kelompok, $id_pengguna, $html_code, $css_code, $js_code);
        $query_insert->execute();
    }

    echo json_encode(["status" => "success", "message" => "Tugas berhasil dikumpulkan!"]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Metode tidak diizinkan."]);
?>
