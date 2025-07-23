<?php
// kerjakan_quiz.php
include 'koneksi.php';
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    die('Anda harus login terlebih dahulu.');
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'siswa' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id_content = $_GET['id_content'] ?? $_POST['id_content'] ?? null;

if (!$id_content) {
    die('ID konten quiz tidak ditemukan.');
}

// Ambil id_meeting dari tabel konten berdasarkan id_content
$stmt = $koneksi->prepare("SELECT id_meeting, duration_minutes FROM meeting_contents WHERE id_content = ?");

$stmt->bind_param("i", $id_content);
$stmt->execute();
$result = $stmt->get_result();
$content = $result->fetch_assoc();
$duration_minutes = $content['duration_minutes'] ?? null;


if (!$content) {
    die('Konten tidak ditemukan.');
}

$hasil_skor = null;

// Jika form dikirim (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jawaban'])) {
    $jawaban = $_POST['jawaban'];
    $id_questions = $_POST['id_question'];
    $total = count($id_questions);
    $benar = 0;

    foreach ($id_questions as $id_q) {
        $selected = isset($jawaban[$id_q]) ? $jawaban[$id_q] : null;
        $query = mysqli_query($koneksi, "SELECT correct_option FROM quiz_questions WHERE id_question = '$id_q'");
        $data = mysqli_fetch_assoc($query);
        $is_correct = ($selected && $selected === $data['correct_option']) ? 1 : 0;
        if ($is_correct) $benar++;

        $stmt = $koneksi->prepare("INSERT INTO quiz_answers (id_question, id_content, id_user, selected_option, is_correct, answered_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiisi", $id_q, $id_content, $user_id, $selected, $is_correct);
        $stmt->execute();
    }

    $score = round(($benar / $total) * 100);
    $stmt = $koneksi->prepare("INSERT INTO quiz_result (id_user, id_content, score, total_questions, correct_answers, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiiii", $user_id, $id_content, $score, $total, $benar);
    $stmt->execute();

    $hasil_skor = $score;
}

// Tambahkan ke content_activity status 'submitted'
$cek_activity = $koneksi->prepare("SELECT * FROM content_activity WHERE id_user = ? AND id_content = ?");
$cek_activity->bind_param("ii", $user_id, $id_content);
$cek_activity->execute();
$res = $cek_activity->get_result();

if ($res->num_rows > 0) {
    $update = $koneksi->prepare("UPDATE content_activity SET status = 'submitted' WHERE id_user = ? AND id_content = ?");
    $update->bind_param("ii", $user_id, $id_content);
    $update->execute();
} else {
    $insert = $koneksi->prepare("INSERT INTO content_activity (id_user, id_content, status) VALUES (?, ?, 'submitted')");
    $insert->bind_param("ii", $user_id, $id_content);
    $insert->execute();
}


// Cek apakah siswa sudah pernah mengerjakan
$cek = mysqli_query($koneksi, "SELECT score FROM quiz_result WHERE id_user = '$user_id' AND id_content = '$id_content'");
$hasil_ulang = mysqli_fetch_assoc($cek);
if ($hasil_ulang) {
    $hasil_skor = $hasil_ulang['score'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kerjakan Quiz | Belajaro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="guru_home.css">

    <style>
        .card {
            transition: all 0.3s ease;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1)
        }

        .card-header {
            border-radius: 0.75rem 0.75rem 0 0 !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            font-family: 'Poppins', sans-serif;
        }

        /* Styling Container Utama */
        .main-content {
            padding: 20px;
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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h3 class="mb-1">✏️ Kerjakan Quiz</h3>
                                <!--  -->
                            </div>
                        </div>

                        <?php if ($hasil_skor !== null): ?>
                            <div class="alert alert-success">
                                ✅ Kamu sudah mengerjakan quiz ini.<br>
                                Skor kamu: <strong><?= $hasil_skor ?> / 100</strong>
                            </div>
                        <?php else: ?>
                            <?php
                            $result = mysqli_query($koneksi, "SELECT * FROM quiz_questions WHERE id_content = '$id_content'");
                            $questions = [];
                            while ($row = mysqli_fetch_assoc($result)) {
                                $questions[] = $row;
                            }

                            if (count($questions) === 0) {
                                echo "<div class='alert alert-warning'>Soal quiz belum tersedia.</div>";
                                exit;
                            }
                            ?>

                            <?php if ($duration_minutes): ?>
                                <div class="alert alert-info">
                                    ⏳ Waktu pengerjaan: <strong><?= $duration_minutes ?> menit</strong><br>
                                    Sisa waktu: <span id="timer" class="fw-bold text-danger"></span>
                                </div>
                            <?php endif; ?>


                            <form method="post" id="quizForm">
                                <input type="hidden" name="id_content" value="<?= $id_content ?>">
                                <?php foreach ($questions as $i => $q): ?>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Soal <?= $i + 1 ?></h5>
                                            <p class="card-text"> <?= htmlspecialchars($q['question_text']) ?> </p>

                                            <input type="hidden" name="id_question[]" value="<?= $q['id_question'] ?>">

                                            <?php foreach (["A", "B", "C", "D"] as $opt): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="jawaban[<?= $q['id_question'] ?>]" value="<?= $opt ?>" id="<?= $opt . $q['id_question'] ?>">
                                                    <label class="form-check-label" for="<?= $opt . $q['id_question'] ?>">
                                                        <?= $opt ?>. <?= htmlspecialchars($q['option_' . strtolower($opt)]) ?>
                                                    </label>

                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <button type="submit" class="btn btn-primary">Kumpulkan Jawaban</button>
                            </form>
                        <?php endif; ?>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <?php if ($duration_minutes): ?>
        <script>
            let duration = <?= $duration_minutes ?> * 60; // detik
            const timerDisplay = document.getElementById('timer');
            const form = document.querySelector('form');

            function updateTimer() {
                const minutes = Math.floor(duration / 60);
                const seconds = duration % 60;
                timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                if (duration <= 0) {
                    clearInterval(timerInterval);
                    alert("⏰ Waktu habis! Jawaban akan dikumpulkan otomatis.");
                    form.submit();
                }
                duration--;
            }

            const timerInterval = setInterval(updateTimer, 1000);
            updateTimer();
        </script>
    <?php endif; ?>

    <script>
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            const totalSoal = <?= count($questions) ?>;
            let jumlahTerjawab = 0;

            // Cek berapa soal yang sudah dijawab
            <?php foreach ($questions as $q): ?>
                if (document.querySelector('input[name="jawaban[<?= $q['id_question'] ?>]"]:checked')) {
                    jumlahTerjawab++;
                }
            <?php endforeach; ?>

            if (jumlahTerjawab < totalSoal) {
                const lanjut = confirm(`⚠️ Masih ada ${totalSoal - jumlahTerjawab} soal yang belum kamu jawab. Apakah kamu yakin ingin mengumpulkan sekarang?`);
                if (!lanjut) {
                    e.preventDefault(); // Batalkan submit
                }
            }
        });
    </script>



    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>