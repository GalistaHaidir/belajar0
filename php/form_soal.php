<?php
include 'koneksi.php';
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    die('Anda harus login terlebih dahulu.');
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$mode = $_GET['mode'] ?? 'add';
$id_content = $_GET['id_content'] ?? $_POST['id_content'] ?? null;
$id = $_GET['id'] ?? $_POST['id'] ?? null;

$question = [
    'question_text' => '',
    'option_a' => '',
    'option_b' => '',
    'option_c' => '',
    'option_d' => '',
    'correct_option' => 'A',
];

// Proses simpan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_content = $_POST['id_content'];
    $question_text = mysqli_real_escape_string($koneksi, $_POST['question_text']);
    $option_a = mysqli_real_escape_string($koneksi, $_POST['option_a']);
    $option_b = mysqli_real_escape_string($koneksi, $_POST['option_b']);
    $option_c = mysqli_real_escape_string($koneksi, $_POST['option_c']);
    $option_d = mysqli_real_escape_string($koneksi, $_POST['option_d']);
    $correct_option = $_POST['correct_option'];

    if (isset($_POST['id']) && $mode === 'edit') {
        // Update
        $id = $_POST['id'];
        $update = mysqli_query($koneksi, "UPDATE quiz_questions SET 
            question_text = '$question_text',
            option_a = '$option_a',
            option_b = '$option_b',
            option_c = '$option_c',
            option_d = '$option_d',
            correct_option = '$correct_option'
            WHERE id_question = '$id'
        ");

        if ($update) {
            header("Location: kelola_soal.php?id_content=$id_content&msg=updated");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Gagal memperbarui soal.</div>";
        }
    } else {
        // Insert
        $insert = mysqli_query($koneksi, "INSERT INTO quiz_questions (id_content, question_text, option_a, option_b, option_c, option_d, correct_option) 
            VALUES ('$id_content', '$question_text', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_option')");

        if ($insert) {
            header("Location: kelola_soal.php?id_content=$id_content&msg=added");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Gagal menambahkan soal.</div>";
        }
    }
}

// Ambil data soal untuk edit
if ($mode === 'edit' && $id) {
    $q = mysqli_query($koneksi, "SELECT * FROM quiz_questions WHERE id_question = '$id'");
    if ($row = mysqli_fetch_assoc($q)) {
        $question = $row;
        $id_content = $row['id_content'];
    } else {
        echo "<div class='alert alert-warning'>Data soal tidak ditemukan.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $mode ?> Soal | Belajaro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="guru_home.css">

    <!-- CSS Tambahan -->
    <style>
        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            font-family: 'Poppins', sans-serif;
        }

        .main-content {
            padding: 20px;
        }

        .card {
            transition: transform 0.2s;
            border-radius: 12px;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .input-group-text {
            font-weight: 600;
            transition: all 0.3s;
        }

        .hover-scale {
            transition: transform 0.2s;
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }

        .bg-success-light {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .invalid-feedback {
            font-size: 0.85rem;
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
                        <!-- Header di lokasi yang ditentukan -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h1 class="mb-2" style="text-transform: capitalize;">✏️ <?= $mode ?> Soal</h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="kelola_soal.php?id_content=<?= $id_content ?>">Kelola Soal</a></li>
                                        <li class="breadcrumb-item active">Form Soal</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Form Container -->
                        <div class="row justify-content-center">
                            <div class="col-lg-8 col-xl-6">
                                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                                    <div class="card-header bg-white py-3 border-bottom">
                                        <h5 class="card-title mb-0 fw-semibold">
                                            <i class="bi bi-card-text me-2"></i>
                                            Formulir Soal
                                        </h5>
                                    </div>

                                    <div class="card-body p-4">
                                        <form action="form_soal.php?mode=<?= $mode ?>" method="post" class="needs-validation" novalidate>
                                            <input type="hidden" name="id_content" value="<?= htmlspecialchars($id_content) ?>">
                                            <?php if ($mode === 'edit'): ?>
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                                            <?php endif; ?>

                                            <!-- Pertanyaan -->
                                            <div class="mb-4">
                                                <label for="question_text" class="form-label fw-semibold">
                                                    <i class="bi bi-question-circle-fill text-primary me-2"></i>
                                                    Pertanyaan
                                                </label>
                                                <textarea name="question_text" id="question_text" rows="5"
                                                    class="form-control p-3 border-2"
                                                    style="min-height: 120px; border-radius: 10px;"
                                                    required><?= htmlspecialchars($question['question_text']) ?></textarea>
                                                <div class="invalid-feedback">
                                                    Silakan isi pertanyaan
                                                </div>
                                            </div>

                                            <!-- Opsi Jawaban -->
                                            <div class="options-container mb-4">
                                                <h6 class="fw-semibold mb-3">
                                                    <i class="bi bi-list-ul text-primary me-2"></i>
                                                    Opsi Jawaban
                                                </h6>

                                                <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                                                    <div class="option-item mb-3">
                                                        <div class="input-group">
                                                            <span class="input-group-text 
                                                            <?= $question['correct_option'] === $opt ? 'bg-success text-white' : 'bg-light' ?> 
                                                            fw-bold" style="width: 50px;">
                                                                <?= $opt ?>
                                                            </span>
                                                            <input type="text" name="option_<?= strtolower($opt) ?>"
                                                                class="form-control border-start-0"
                                                                value="<?= htmlspecialchars($question['option_' . strtolower($opt)]) ?>"
                                                                required>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <!-- Jawaban Benar -->
                                            <div class="mb-4">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                                    Jawaban Benar
                                                </label>
                                                <select name="correct_option" class="form-select p-3 border-2" required>
                                                    <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                                                        <option value="<?= $opt ?>"
                                                            <?= $question['correct_option'] === $opt ? 'selected' : '' ?>
                                                            class="<?= $question['correct_option'] === $opt ? 'bg-success-light' : '' ?>">
                                                            Opsi <?= $opt ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="d-flex justify-content-between pt-3">
                                                <button type="submit" class="btn btn-primary px-4 rounded-pill hover-scale">
                                                    <i class="bi bi-save me-2"></i>
                                                    <?= $mode === 'edit' ? 'Simpan Perubahan' : 'Tambah Soal' ?>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Validasi Form -->
    <script>
        (function() {
            'use strict'

            const forms = document.querySelectorAll('.needs-validation')

            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>

</html>