<?php
session_start();
include 'koneksi.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php"); // Redirect ke halaman login jika belum login
    exit;
}

$id_pengguna = $_SESSION['id_pengguna']; // Ambil id_pengguna dari session

// Fetch waktu minimal dari tabel peraturan
$peraturan = $koneksi->query("SELECT waktu FROM tbl_pengaturan LIMIT 1")->fetch_assoc();
$waktu_minimal = $peraturan['waktu']; // Waktu dalam menit

// Fetch all questions from the database
$result = $koneksi->query("SELECT * FROM tbl_soal");

// Handle form submission
if (isset($_POST['submit'])) {
    $jawaban_benar = 0;
    $jawaban_salah = 0;
    $jawaban_kosong = 0;

    while ($row = $result->fetch_assoc()) {
        $id_soal = $row['id'];
        $kunci_jawaban = $row['kunci_jawaban'];

        // Cek jawaban pengguna
        if (isset($_POST["jawaban_$id_soal"])) {
            $jawaban = $_POST["jawaban_$id_soal"];
            if ($jawaban == $kunci_jawaban) {
                $jawaban_benar++;
            } else {
                $jawaban_salah++;
            }
        } else {
            $jawaban_kosong++;
        }
    }

    // Hitung nilai
    $total_soal = $jawaban_benar + $jawaban_salah + $jawaban_kosong;
    $nilai = ($jawaban_benar / $total_soal) * 100;

    // Simpan hasil ke database
    $tanggal = date('Y-m-d');
    $status = $nilai >= 60 ? 'Lulus' : 'Tidak Lulus'; // Contoh kriteria kelulusan

    $sql = "INSERT INTO tbl_nilai (id_pengguna, benar, salah, kosong, nilai, tanggal, status) VALUES ('$id_pengguna', '$jawaban_benar', '$jawaban_salah', '$jawaban_kosong', '$nilai', '$tanggal', '$status')";
    $koneksi->query($sql);

    // Tampilkan hasil
    echo "<h2>Hasil Ujian</h2>";
    echo "Jawaban Benar: $jawaban_benar<br>";
    echo "Jawaban Salah: $jawaban_salah<br>";
    echo "Jawaban Kosong: $jawaban_kosong<br>";
    echo "Nilai: $nilai<br>";
    echo "Status: $status<br>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mengerjakan Soal</title>
    <script>
        // Timer untuk waktu minimal
        let waktu = <?= $waktu_minimal ?> * 60; // Konversi menit ke detik

        function startTimer() {
            const timerElement = document.getElementById('timer');
            const interval = setInterval(() => {
                const minutes = Math.floor(waktu / 60);
                const seconds = waktu % 60;

                // Tampilkan waktu yang tersisa
                timerElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

                // Jika waktu habis, kirim form secara otomatis
                if (waktu <= 0) {
                    clearInterval(interval);
                    alert('Waktu habis! Jawaban Anda akan dikirim.');
                    document.getElementById('soalForm').submit();
                }

                waktu--;
            }, 1000);
        }

        window.onload = startTimer;
    </script>
</head>

<body>
    <h1>Mengerjakan Soal</h1>
    <p>Waktu Tersisa: <span id="timer"></span></p>
    <form method="POST" action="" id="soalForm">
        <?php
        // Reset result pointer to fetch questions again
        $result = $koneksi->query("SELECT * FROM tbl_soal");
        while ($row = $result->fetch_assoc()) { ?>
            <div>
                <p><strong><?= $row['pertanyaan'] ?></strong></p>
                <?php if ($row['gambar']) { ?>
                    <img src="gambar_soal/<?= $row['gambar'] ?>" width="200"><br>
                <?php } ?>
                <input type="radio" name="jawaban_<?= $row['id'] ?>" value="A"> <?= $row['a'] ?><br>
                <input type="radio" name="jawaban_<?= $row['id'] ?>" value="B"> <?= $row['b'] ?><br>
                <input type="radio" name="jawaban_<?= $row['id'] ?>" value="C"> <?= $row['c'] ?><br>
                <input type="radio" name="jawaban_<?= $row['id'] ?>" value="D"> <?= $row['d'] ?><br>
            </div>
            <hr>
        <?php } ?>
        <button type="submit" name="submit">Kirim Jawaban</button>
    </form>
</body>

</html>