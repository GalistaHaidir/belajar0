<?php
session_start();
include 'koneksi.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php"); // Redirect ke halaman login jika belum login
    exit;
}

$sessionUsername = $_SESSION['admin_username'];

// Ambil data user dari database
$query = "SELECT fotoProfil FROM pengguna WHERE username = '$sessionUsername'";
$result = mysqli_query($koneksi, $query);

// Periksa apakah ada hasil
if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    $fotoProfil = $data['fotoProfil'];
} else {
    // Jika data tidak ditemukan, set nilai default
    $fotoProfil = "default.jpg";
}

$id_peraturan = $_GET['id_peraturan'];
$id_pengguna = $_SESSION['id_pengguna'];

// Ambil data ujian dari tbl_pengaturan
$query_pengaturan = "SELECT * FROM tbl_pengaturan WHERE id_peraturan = '$id_peraturan'";
$result_pengaturan = mysqli_query($koneksi, $query_pengaturan);
$pengaturan = mysqli_fetch_assoc($result_pengaturan);

// Ambil soal dari tbl_soal
$query_soal = "SELECT * FROM tbl_soal WHERE id_peraturan = '$id_peraturan'";
$result_soal = mysqli_query($koneksi, $query_soal);
$soal_list = mysqli_fetch_all($result_soal, MYSQLI_ASSOC);

// Waktu dalam detik
$waktu_ujian = $pengaturan['waktu'] * 60;

// Jika formulir dikirimkan
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $benar = 0;
    $salah = 0;
    $kosong = 0;

    foreach ($soal_list as $soal) {
        $id_soal = $soal['id_soal'];
        $kunci_jawaban = $soal['kunci_jawaban'];

        // Cek jawaban pengguna
        if (isset($_POST["jawaban_$id_soal"])) {
            $jawaban = $_POST["jawaban_$id_soal"];
            if ($jawaban == $kunci_jawaban) {
                $benar++;
            } else {
                $salah++;
            }
        } else {
            $kosong++;
        }
    }

    // Hitung nilai
    $total_soal = count($soal_list);
    $nilai = ($benar / $total_soal) * 100;
    $status = ($nilai >= $pengaturan['nilai_minimal']) ? "Lulus" : "Tidak Lulus";

    // Simpan hasil ke database
    $query_nilai = "INSERT INTO tbl_nilai (id_pengguna, id_peraturan, benar, salah, kosong, nilai, tanggal, status)
                    VALUES ('$id_pengguna', '$id_peraturan', '$benar', '$salah', '$kosong', '$nilai', NOW(), '$status')";
    mysqli_query($koneksi, $query_nilai);

    // Redirect ke halaman hasil
    header("Location: hasil.php?id_peraturan=$id_peraturan");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">
    <title>Mengerjakan Soal</title>
    <style>
        .card-soal {
            border-left: 5px solid #007bff;
            /* Garis warna biru di sebelah kiri soal */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            /* Efek bayangan */
            padding: 20px;
            border-radius: 10px;
            background-color: #f8f9fa;
            /* Warna latar belakang yang lembut */
        }

        /* Styling untuk pertanyaan */
        .card-soal h5 {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        /* Styling untuk opsi jawaban */
        .form-check {
            background: white;
            padding: 10px;
            border-radius: 5px;
            margin-top: 8px;
            transition: 0.3s;
        }

        /* Efek hover pada pilihan jawaban */
        .form-check:hover {
            background: #e9ecef;
            transform: scale(1.02);
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <div class="container-fluid mt-4">
                    <div class="row">
                        <!-- Kolom kiri: Soal -->
                        <div class="col-md-3">
                            <div class="card bg-light p-3">
                                <h2 class="text-center" style="text-transform:capitalize;"><?php echo $pengaturan['nama_ujian']; ?></h2>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" id="ujianForm">
                                <?php foreach ($soal_list as $index => $soal) : ?>
                                    <div class="card card-soal mb-3 soal" id="soal-<?php echo $index; ?>" style="display: <?php echo $index == 0 ? 'block' : 'none'; ?>;">
                                        <div class="card-body">
                                            <h5><?php echo ($index + 1) . ". " . $soal['pertanyaan']; ?></h5>
                                            <?php if ($soal['gambar']) : ?>
                                                <img src="gambar_soal/<?php echo $soal['gambar']; ?>" class="img-fluid mb-3 d-block mx-auto" width="200">
                                            <?php endif; ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jawaban_<?php echo $soal['id_soal']; ?>" value="A">
                                                <label class="form-check-label"><?php echo $soal['a']; ?></label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jawaban_<?php echo $soal['id_soal']; ?>" value="B">
                                                <label class="form-check-label"><?php echo $soal['b']; ?></label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jawaban_<?php echo $soal['id_soal']; ?>" value="C">
                                                <label class="form-check-label"><?php echo $soal['c']; ?></label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jawaban_<?php echo $soal['id_soal']; ?>" value="D">
                                                <label class="form-check-label"><?php echo $soal['d']; ?></label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <!-- Navigasi Soal -->
                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-secondary btn-lg" id="prevBtn" style="display: none;">Sebelumnya</button>
                                    <button type="button" class="btn btn-primary btn-lg" id="nextBtn">Selanjutnya</button>
                                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn" style="display: none;">Selesai</button>
                                </div>
                            </form>
                        </div>

                        <!-- Kolom kanan: Info Ujian -->
                        <div class="col-md-3">
                            <div class="card bg-light p-3">
                                <h5 class="text-center">Informasi Ujian</h5>
                                <hr>
                                <p><strong>Nama Ujian:</strong> <?php echo $pengaturan['nama_ujian']; ?></p>
                                <p><strong>Nilai Minimal:</strong> <?php echo $pengaturan['nilai_minimal']; ?></p>
                                <p><strong>Peraturan:</strong> <?php echo $pengaturan['peraturan']; ?></p>
                                <div class="alert alert-danger text-center">
                                    <h5>Waktu Tersisa</h5>
                                    <h3 id="timer"><?php echo floor($waktu_ujian / 60) . " : " . ($waktu_ujian % 60); ?></h3>
                                </div>
                            </div>
                            <div class="text-center mb-3">
                                <div class="card p-3">
                                    <h5>Daftar Soal</h5>
                                    <div id="daftarSoal">
                                        <?php foreach ($soal_list as $index => $soal) : ?>
                                            <button type="button" class="btn btn-outline-primary m-1 nomor-soal" data-index="<?php echo $index; ?>">
                                                <?php echo $index + 1; ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <?php include 'footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script>
        let waktu = <?php echo $waktu_ujian; ?>; // Waktu dari PHP (dalam detik)

        function updateTimer() {
            let menit = Math.floor(waktu / 60);
            let detik = waktu % 60;
            document.getElementById("timer").innerHTML = `${menit} : ${detik < 10 ? "0" : ""}${detik}`;
            if (waktu <= 0) {
                document.getElementById("ujianForm").submit(); // Kirim otomatis jika waktu habis
            }
            waktu--;
        }

        setInterval(updateTimer, 1000);

        document.addEventListener("DOMContentLoaded", function() {
            let currentSoal = 0;
            let totalSoal = <?php echo count($soal_list); ?>;
            let soalElements = document.querySelectorAll(".soal");
            let prevBtn = document.getElementById("prevBtn");
            let nextBtn = document.getElementById("nextBtn");
            let submitBtn = document.getElementById("submitBtn");
            let nomorSoalButtons = document.querySelectorAll(".nomor-soal");

            function updateSoal() {
                soalElements.forEach((soal, index) => {
                    soal.style.display = index === currentSoal ? "block" : "none";
                });

                // Perbarui tampilan tombol navigasi
                prevBtn.style.display = currentSoal > 0 ? "inline-block" : "none";
                nextBtn.style.display = currentSoal < totalSoal - 1 ? "inline-block" : "none";
                submitBtn.style.display = currentSoal === totalSoal - 1 ? "inline-block" : "none";

                // Perbarui gaya tombol daftar soal
                nomorSoalButtons.forEach((btn, index) => {
                    btn.classList.toggle("btn-primary", index === currentSoal);
                    btn.classList.toggle("btn-outline-primary", index !== currentSoal);
                });
            }

            prevBtn.addEventListener("click", function() {
                if (currentSoal > 0) {
                    currentSoal--;
                    updateSoal();
                }
            });

            nextBtn.addEventListener("click", function() {
                if (currentSoal < totalSoal - 1) {
                    currentSoal++;
                    updateSoal();
                }
            });

            // Tambahkan event listener ke tombol daftar soal
            nomorSoalButtons.forEach((btn) => {
                btn.addEventListener("click", function() {
                    currentSoal = parseInt(this.getAttribute("data-index"));
                    updateSoal();
                });
            });

            updateSoal();
        });
    </script>
</body>

</html>