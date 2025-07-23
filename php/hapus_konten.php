<?php
include 'koneksi.php';  // sesuaikan dengan file koneksi kamu

if (isset($_GET['id']) && isset($_GET['id_meeting'])) {
    $id_content = intval($_GET['id']);
    $id_meeting = intval($_GET['id_meeting']);

    // Ambil dulu info file_path sebelum dihapus
    $query = "SELECT file_path FROM meeting_contents WHERE id_content = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_content);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $file_path);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Jika ada file_path dan file tersebut ada di folder uploads/materi/, hapus file fisiknya
    if (!empty($file_path)) {
        $file = __DIR__ . '/uploads/materi/' . $file_path;
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // Hapus data dari database
    $query = "DELETE FROM meeting_contents WHERE id_content = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_content);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success) {
        header("Location: kelola_konten.php?id_meeting=$id_meeting");
        exit;
    } else {
        echo "Gagal menghapus konten.";
    }
} else {
    echo "Parameter tidak lengkap.";
}
