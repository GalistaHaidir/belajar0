<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];
$id_tugas = $_POST['id_tugas'] ?? null;
$file_tugas = null;

// Ambil kode dari form (jika ada)
$html_code = $_POST['html_code'] ?? null;
$css_code = $_POST['css_code'] ?? null;
$js_code = $_POST['js_code'] ?? null;

if (!$id_tugas) {
    die("Tugas tidak ditemukan!");
}

// Cek apakah pengguna sudah mengumpulkan tugas
$query_check = $koneksi->prepare("SELECT * FROM pengumpulan_tugas WHERE id_tugas = ? AND id_pengguna = ?");
$query_check->bind_param("ii", $id_tugas, $id_pengguna);
$query_check->execute();
$result_check = $query_check->get_result();

if ($result_check->num_rows > 0) {
    die("Anda sudah mengumpulkan tugas ini!");
}

// Jika pengguna mengunggah file
if (isset($_FILES['file_tugas']) && $_FILES['file_tugas']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = "pengumpulan_tugas_individu/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_tmp = $_FILES['file_tugas']['tmp_name'];
    $file_name = basename($_FILES['file_tugas']['name']);
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $file_name_new = "tugas_" . $id_pengguna . "_" . time() . "." . $file_ext;
    $file_path = $upload_dir . $file_name_new;

    $allowed_extensions = ['pdf', 'doc', 'docx', 'zip', 'rar'];
    if (!in_array(strtolower($file_ext), $allowed_extensions)) {
        die("Format file tidak diizinkan!");
    }

    move_uploaded_file($file_tmp, $file_path);
    $file_tugas = $file_path;
}

// Simpan ke database
$query = $koneksi->prepare("
    INSERT INTO pengumpulan_tugas (id_tugas, id_pengguna, file_tugas, html_code, css_code, js_code, tanggal_upload) 
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$query->bind_param("iissss", $id_tugas, $id_pengguna, $file_tugas, $html_code, $css_code, $js_code);
$query->execute();

echo "<script>alert('Tugas berhasil dikumpulkan!'); window.location.href='detailtugas_individu.php?id_tugas=$id_tugas';</script>";
?>
