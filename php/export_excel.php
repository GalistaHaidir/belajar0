<?php
include 'koneksi.php'; // Menyertakan file koneksi ke database
session_start(); // Memulai session untuk akses data sesi jika diperlukan

// Ambil ID konten kuis dari parameter GET
$id_content = $_GET['id_quiz'] ?? null;

// Jika parameter tidak ada, hentikan proses dan tampilkan pesan error
if (!$id_content) {
    die("Parameter id_quiz tidak ditemukan.");
}

// Atur header HTTP agar browser mengunduh file sebagai Excel
header("Content-Type: application/vnd.ms-excel"); // Format konten sebagai file Excel
header("Content-Disposition: attachment; filename=hasil_quiz_" . $id_content . ".xls"); // Nama file saat didownload
header("Pragma: no-cache"); // Mencegah cache
header("Expires: 0"); // Menghindari penyimpanan cache oleh browser

// Mulai membuat isi file Excel dalam format tabel HTML
echo "<table border='1'>";
echo "<tr>
        <th>No</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Skor</th>
        <th>Total Soal</th>
        <th>Jawaban Benar</th>
        <th>Waktu Submit</th>
      </tr>";

// Ambil data hasil kuis dari database berdasarkan id_content (id kuis)
$query = mysqli_query($koneksi, "
    SELECT u.name, u.email, r.score, r.total_questions, r.correct_answers, r.submitted_at
    FROM quiz_result r
    JOIN users u ON r.id_user = u.id_user
    WHERE r.id_content = '$id_content'
    ORDER BY r.score DESC
");

// Inisialisasi nomor baris
$no = 1;

// Loop hasil query dan cetak sebagai baris-baris tabel
while ($row = mysqli_fetch_assoc($query)) {
    echo "<tr>
            <td>{$no}</td>
            <td>" . htmlspecialchars($row['name']) . "</td>  // Lindungi output dari karakter spesial
            <td>" . htmlspecialchars($row['email']) . "</td>
            <td>" . htmlspecialchars($row['score']) . "</td>
            <td>" . htmlspecialchars($row['total_questions']) . "</td>
            <td>" . htmlspecialchars($row['correct_answers']) . "</td>
            <td>" . date('d-m-Y H:i', strtotime($row['submitted_at'])) . "</td> // Format tanggal
          </tr>";
    $no++; // Naikkan nomor
}

// Tutup tabel
echo "</table>";

// Hentikan script setelah output selesai
exit;
