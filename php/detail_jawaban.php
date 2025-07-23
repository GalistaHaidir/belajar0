<?php
include 'koneksi.php'; // Menyertakan koneksi ke database
session_start(); // Memulai session PHP

// Ambil parameter dari URL (GET), jika tidak ada maka null
$id_user = $_GET['id_user'] ?? null;
$id_quiz = $_GET['id_quiz'] ?? null;

// Validasi parameter
if (!$id_user || !$id_quiz) {
    die("Parameter tidak lengkap."); // Hentikan proses jika ada yang kosong
}

// ========== AMBIL INFO SISWA ==========
$q_siswa = mysqli_query($koneksi, "
    SELECT name, email FROM users WHERE id_user = '$id_user'
");
$siswa = mysqli_fetch_assoc($q_siswa); // Ambil data siswa (nama & email)

// ========== AMBIL HASIL QUIZ SISWA ==========
$q_hasil = mysqli_query($koneksi, "
    SELECT * FROM quiz_result WHERE id_user = '$id_user' AND id_content = '$id_quiz'
");
$hasil = mysqli_fetch_assoc($q_hasil); // Data hasil quiz (skor, waktu kirim, dll)

// ========== AMBIL DETAIL SOAL DAN JAWABAN SISWA ==========
$q_detail = mysqli_query($koneksi, "
    SELECT 
        qq.question_text,       -- Teks soal
        qq.option_a,            -- Pilihan A
        qq.option_b,            -- Pilihan B
        qq.option_c,            -- Pilihan C
        qq.option_d,            -- Pilihan D
        qq.correct_option,      -- Jawaban benar
        qa.selected_option,     -- Jawaban yang dipilih siswa
        qa.is_correct           -- Apakah jawaban siswa benar (1/0)
    FROM quiz_questions qq
    LEFT JOIN quiz_answers qa 
        ON qq.id_question = qa.id_question AND qa.id_user = '$id_user'
    WHERE qq.id_content = '$id_quiz'
");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Jawaban Quiz | Belajaro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="guru_home.css">

    <style>
        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            font-family: 'Poppins', sans-serif;
        }

        /* Styling Container Utama */
        .main-content {
            padding: 20px;
        }

        .card {
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <!-- Overlay hanya untuk mobile -->
        <div class="mobile-sidebar-overlay d-md-none" id="mobile-sidebar-overlay"></div>
        <div class="main">
            <?php include 'akun-info.php'; ?> <!-- Ini selalu muncul (desktop) -->
            <?php include 'navbar.php'; ?>
            <main class="content px-4 py-4">
                <div class="main-content">
                    <div class="container-fluid">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h1 class="fw-bold mb-2">
                                    📋 Detail Jawaban Quiz
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="lihat_hasil_quiz.php?id_content=<?= $id_quiz ?>" class="">Hasil Quiz</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Detail Jawaban</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"></h5>
                            </div>
                            <div class="card-body">
                                <h6>👤 <?= htmlspecialchars($siswa['name']) ?> (<?= htmlspecialchars($siswa['email']) ?>)</h6>
                                <p class="mb-2">Skor: <strong><?= $hasil['score'] ?>/100</strong></p>
                                <p class="text-muted">Total Benar: <?= $hasil['correct_answers'] ?> dari <?= $hasil['total_questions'] ?> soal</p>
                                <div class="d-flex justify-content-center">
                                    <canvas id="scoreChart" style="max-width: 250px; max-height: 250px;"></canvas>
                                </div>


                                <hr>
                                <?php
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($q_detail)):
                                    $jawaban_siswa = $row['selected_option'] ?? '-';
                                    $benar_salah = $row['is_correct'] == 1 ? '✅ Benar' : '❌ Salah';
                                ?>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold"><?= $no++ ?>. <?= htmlspecialchars($row['question_text']) ?></h6>
                                        <ul class="list-group list-group-flush mb-2">
                                            <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                                                <?php
                                                $option_text = $row['option_' . strtolower($opt)];
                                                $is_correct = ($opt == $row['correct_option']);
                                                $is_selected = ($opt == $jawaban_siswa);
                                                ?>
                                                <li class="list-group-item <?= $is_selected ? 'bg-primary bg-opacity-10' : '' ?>">
                                                    <?= $opt ?>. <?= htmlspecialchars($option_text) ?>
                                                    <?php if ($is_correct): ?>
                                                        <span class="badge bg-success ms-2">Kunci</span>
                                                    <?php endif; ?>
                                                    <?php if ($is_selected): ?>
                                                        <span class="badge bg-info ms-2">Jawaban Anda</span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <p class="<?= $row['is_correct'] ? 'text-success' : 'text-danger' ?>">
                                            <?= $benar_salah ?>
                                        </p>
                                    </div>
                                <?php endwhile; ?>
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
        const ctx = document.getElementById('scoreChart').getContext('2d');
        const scoreChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Benar', 'Salah'],
                datasets: [{
                    data: [<?= $hasil['correct_answers'] ?>, <?= $hasil['total_questions'] - $hasil['correct_answers'] ?>],
                    backgroundColor: ['#198754', '#dc3545'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 20,
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = <?= $hasil['total_questions'] ?>;
                                const percent = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                },
                cutout: '70%' // inner radius
            }
        });
    </script>

</body>

</html>