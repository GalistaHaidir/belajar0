<?php


session_start();

include 'koneksi.php'; // Koneksi ke database

if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
}


$id_pengguna = $_SESSION['id_pengguna'];

// Periksa apakah id_kelompok sudah ada di session
if (!isset($_SESSION['id_kelompok'])) {
    // Jika belum ada, ambil dari database
    $query_kelompok = $koneksi->prepare("SELECT id_kelompok FROM akses_kelompok WHERE id_pengguna = ?");
    $query_kelompok->bind_param("i", $id_pengguna);
    $query_kelompok->execute();
    $result_kelompok = $query_kelompok->get_result();
    $data_kelompok = $result_kelompok->fetch_assoc();

    if ($data_kelompok) {
        $_SESSION['id_kelompok'] = $data_kelompok['id_kelompok']; // Simpan ke session
    } else {
        die("Error: Anda belum tergabung dalam kelompok.");
    }
}

// Ambil id_kelompok dari session
$id_kelompok = $_SESSION['id_kelompok'];

// Cek apakah file diunggah
if (!isset($_FILES['file_tugas']) || $_FILES['file_tugas']['error'] != 0) {
    die("Error: File tidak ditemukan atau gagal diunggah.");
}

$id_tugas = $_POST['id_tugas'];

// Folder penyimpanan
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Ambil informasi file
$file_name = basename($_FILES['file_tugas']['name']);
$file_tmp = $_FILES['file_tugas']['tmp_name'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Validasi ekstensi file
$allowed_ext = ['pdf', 'doc', 'docx', 'txt'];
if (!in_array($file_ext, $allowed_ext)) {
    die("Error: Format file tidak didukung.");
}

// Buat nama file unik
$file_new_name = "tugas_" . $id_tugas . "_" . $id_kelompok . "." . $file_ext;
$file_tugas = $upload_dir . $file_new_name;

// Pindahkan file ke folder tujuan
if (!move_uploaded_file($file_tmp, $file_tugas)) {
    die("Gagal mengunggah file.");
}

// Simpan ke database
$query = $koneksi->prepare("
    INSERT INTO pengumpulan_tugas (id_tugas, id_kelompok, id_pengguna, file_tugas, tanggal_upload) 
    VALUES (?, ?, ?, ?, NOW())
");

$query->bind_param("iiis", $id_tugas, $id_kelompok, $id_pengguna, $file_new_name);
$query->execute();

if ($query) {
    echo "Tugas berhasil dikumpulkan!";
} else {
    echo "Gagal menyimpan data ke database.";
}