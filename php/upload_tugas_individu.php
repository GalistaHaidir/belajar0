<?php

session_start();

include 'koneksi.php'; // Koneksi ke database

if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
}

$id_pengguna = $_SESSION['id_pengguna'];
$id_tugas = $_POST['id_tugas'] ?? null;

// Cek apakah ID tugas valid
if (!$id_tugas) {
    die("Tugas tidak ditemukan!");
}

// Ambil informasi tugas
$query_tugas = $koneksi->prepare("SELECT * FROM tugas WHERE id_tugas = ?");
$query_tugas->bind_param("i", $id_tugas);
$query_tugas->execute();
$result_tugas = $query_tugas->get_result();
$tugas = $result_tugas->fetch_assoc();

if (!$tugas) {
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

// Proses Upload File
if ($_FILES['file_tugas']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = "pengumpulan_tugas_individu/"; // Direktori penyimpanan file tugas
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_tmp = $_FILES['file_tugas']['tmp_name'];
    $file_name = basename($_FILES['file_tugas']['name']);
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $file_name_new = "tugas_" . $id_pengguna . "_" . time() . "." . $file_ext;
    $file_path = $upload_dir . $file_name_new;

    // Validasi ekstensi file
    $allowed_extensions = ['pdf', 'doc', 'docx', 'zip', 'rar'];
    if (!in_array(strtolower($file_ext), $allowed_extensions)) {
        echo "<script>
            alert('Format file tidak diizinkan! Hanya boleh PDF, DOC, DOCX, ZIP, atau RAR.');
            window.history.back();
        </script>";
        exit;
    }

    // Pindahkan file ke folder tujuan
    if (move_uploaded_file($file_tmp, $file_path)) {
        // Simpan informasi pengumpulan ke database
        $query_insert = $koneksi->prepare("
            INSERT INTO pengumpulan_tugas (id_tugas, id_pengguna, file_tugas, tanggal_upload)
            VALUES (?, ?, ?, NOW())
        ");
        $query_insert->bind_param("iis", $id_tugas, $id_pengguna, $file_path);

        if ($query_insert->execute()) {
            echo "<script>
                alert('Tugas berhasil dikumpulkan!');
                window.location.href = 'detailtugas_individu.php?id_tugas=$id_tugas';
            </script>";
            exit;
        } else {
            echo "<script>
                alert('Gagal menyimpan tugas ke database.');
                window.history.back();
            </script>";
            exit;
        }
    } else {
        echo "<script>
            alert('Gagal mengunggah file.');
            window.history.back();
        </script>";
        exit;
    }
} else {
    echo "<script>
        alert('Terjadi kesalahan saat mengunggah file.');
        window.history.back();
    </script>";
    exit;
}
