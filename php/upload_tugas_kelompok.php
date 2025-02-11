<?php


session_start();

include 'koneksi.php'; // Koneksi ke database

if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
}

$id_pengguna = $_SESSION['id_pengguna'];

// Periksa apakah id_kelompok sudah ada di session
if (!isset($_SESSION['id_kelompok'])) {
    $query_kelompok = $koneksi->prepare("SELECT id_kelompok FROM akses_kelompok WHERE id_pengguna = ?");
    $query_kelompok->bind_param("i", $id_pengguna);
    $query_kelompok->execute();
    $result_kelompok = $query_kelompok->get_result();
    $data_kelompok = $result_kelompok->fetch_assoc();

    if ($data_kelompok) {
        $_SESSION['id_kelompok'] = $data_kelompok['id_kelompok'];
    } else {
        die("Error: Anda belum tergabung dalam kelompok.");
    }
}

$id_kelompok = $_SESSION['id_kelompok'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_tugas'])) {
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
    $file_path = $upload_dir . $file_new_name;

    // Pindahkan file ke folder tujuan
    if (!move_uploaded_file($file_tmp, $file_path)) {
        die("Gagal mengunggah file.");
    }

    // Simpan ke database (SIMPAN PATH LENGKAP)
    $query = $koneksi->prepare("
        INSERT INTO pengumpulan_tugas (id_tugas, id_kelompok, id_pengguna, file_tugas, tanggal_upload) 
        VALUES (?, ?, ?, ?, NOW())
    ");

    $query->bind_param("iiis", $id_tugas, $id_kelompok, $id_pengguna, $file_path);
    if ($query->execute()) {
        $id_proyek = $_POST['id_proyek']; // Pastikan id_proyek dikirim dari form
        echo "<script>
            alert('Tugas berhasil dikumpulkan!');
            window.location.href = 'detailtugas_kelompok.php?id_proyek=$id_proyek';
        </script>";
    } else {
        echo "<script>alert('Gagal menyimpan data ke database.');</script>";
    }
    
    
}

// Ambil semua tugas yang sudah dikumpulkan
$query_tugas = $koneksi->query("SELECT * FROM pengumpulan_tugas WHERE id_kelompok = '$id_kelompok' ORDER BY tanggal_upload DESC");
