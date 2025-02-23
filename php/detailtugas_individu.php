<?php
session_start();

include 'koneksi.php'; // Koneksi ke database

if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
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



$id_tugas = $_GET['id_tugas'];
$id_pengguna = $_SESSION['id_pengguna']; // Ambil ID pengguna yang sedang login

// Ambil detail tugas
$query_tugas = "
    SELECT id_tugas, judul_tugas, dateline, deskripsi
    FROM tugas
    WHERE id_tugas = ?
";
$stmt = $koneksi->prepare($query_tugas);
$stmt->bind_param("i", $id_tugas);
$stmt->execute();
$result_tugas = $stmt->get_result();
$tugas = $result_tugas->fetch_assoc();

if (!$tugas) {
    echo "Tugas tidak ditemukan!";
    exit;
}

// Cek apakah pengguna sudah mengumpulkan tugas ini
$query_pengumpulan = "
    SELECT nilai, catatan_guru, html_code, css_code, js_code 
    FROM pengumpulan_tugas 
    WHERE id_tugas = ? AND id_pengguna = ?
";
$stmt = $koneksi->prepare($query_pengumpulan);
$stmt->bind_param("ii", $id_tugas, $id_pengguna);
$stmt->execute();
$result_pengumpulan = $stmt->get_result();
$pengumpulan = $result_pengumpulan->fetch_assoc();


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
    <title>Detail Tugas Individu</title>
    <style>
        /* Styling body */
        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            color: #1B5E20;
            /* Warna hijau tua */
        }

        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <a class="btn btn-outline-danger"
                    style="border-top-left-radius: 50px; border-bottom-left-radius: 50px; margin-bottom:10px;"
                    onclick="navigateToPage()">
                    <i class="bi bi-backspace-fill"></i>
                    <span>Kembali</span>
                </a>

                <div class="container">
                    <h1 class="mb-4 text-center text-primary">📚 Detail Tugas Individu</h1>

                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            // Cek apakah tugas sudah dikumpulkan
                            $query_pengumpulan = $koneksi->prepare("
            SELECT * FROM pengumpulan_tugas WHERE id_pengguna = ? AND id_tugas = ?
        ");
                            $query_pengumpulan->bind_param("ii", $id_pengguna, $tugas['id_tugas']);
                            $query_pengumpulan->execute();
                            $result_pengumpulan = $query_pengumpulan->get_result();
                            $pengumpulan = $result_pengumpulan->fetch_assoc();

                            // Warna kartu berdasarkan status
                            $card_class = $pengumpulan ? 'border-success' : 'border-warning';
                            $status_badge = $pengumpulan
                                ? '<span class="badge bg-success">✅ Sudah Dikumpulkan</span>'
                                : '<span class="badge bg-warning text-dark">⚠️ Belum Dikumpulkan</span>';
                            ?>

                            <div class="card shadow-lg <?= $card_class; ?>">
                                <div class="card-body">
                                    <h4 class="fw-bold"><?= htmlspecialchars($tugas['judul_tugas']); ?></h4>
                                    <p><strong>📅 Deadline:</strong>
                                        <span class="badge bg-danger"><?= htmlspecialchars($tugas['dateline']); ?></span>
                                    </p>
                                    <p><strong>📌 Deskripsi:</strong></p>
                                    <div class="alert alert-light"><?= nl2br(htmlspecialchars($tugas['deskripsi'])); ?></div>

                                    <?= $status_badge; ?>

                                    <hr>

                                    <?php if (!$pengumpulan): ?>
                                        <h5 class="text-success mt-3">📤 Pilihan Pengumpulan Tugas</h5>

                                        <!-- Tab Navigasi -->
                                        <ul class="nav nav-tabs" id="tabTugas" role="tablist">
                                            <li class="nav-item">
                                                <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">
                                                    📁 Unggah File
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link" id="livecode-tab" data-bs-toggle="tab" data-bs-target="#livecode" type="button" role="tab">
                                                    ✍️ Kerjakan Langsung
                                                </button>
                                            </li>
                                        </ul>

                                        <!-- Tab Konten -->
                                        <div class="tab-content mt-3" id="tabContent">
                                            <!-- Tab Unggah File -->
                                            <div class="tab-pane fade show active" id="upload" role="tabpanel">
                                                <form action="upload_tugas_individu.php" method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="id_tugas" value="<?= $tugas['id_tugas']; ?>">
                                                    <div class="mb-3">
                                                        <input type="file" name="file_tugas" class="form-control" required>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary w-100">
                                                        <i class="bi bi-upload"></i> Kumpulkan Tugas
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Tab Live Code Editor -->
                                            <div class="tab-pane fade" id="livecode" role="tabpanel">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="card p-3">
                                                            <div class="mb-3">
                                                                <label class="fw-bold">HTML</label>
                                                                <textarea id="html-code-<?= $tugas['id_tugas']; ?>" class="form-control"></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="fw-bold">CSS</label>
                                                                <textarea id="css-code-<?= $tugas['id_tugas']; ?>" class="form-control"></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="fw-bold">JavaScript</label>
                                                                <textarea id="js-code-<?= $tugas['id_tugas']; ?>" class="form-control"></textarea>
                                                            </div>
                                                            <button class="btn btn-run w-100 mt-2" onclick="runCode(<?= $tugas['id_tugas']; ?>)">
                                                                ▶️ Jalankan Kode
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <h5 class="text-center">🔍 Output:</h5>
                                                        <div class="card p-3">
                                                            <iframe id="output-<?= $tugas['id_tugas']; ?>" class="w-100" style="height: 300px;"></iframe>
                                                        </div>
                                                        <button class="btn btn-success w-100 mt-3" onclick="submitCode(<?= $tugas['id_tugas']; ?>)">
                                                            📤 Simpan & Kumpulkan
                                                        </button>

                                                        <form id="submit-code-form-<?= $tugas['id_tugas']; ?>" action="submit_livecode.php" method="POST">
                                                            <input type="hidden" name="id_tugas" value="<?= $tugas['id_tugas']; ?>">
                                                            <input type="hidden" id="html-input-<?= $tugas['id_tugas']; ?>" name="html_code">
                                                            <input type="hidden" id="css-input-<?= $tugas['id_tugas']; ?>" name="css_code">
                                                            <input type="hidden" id="js-input-<?= $tugas['id_tugas']; ?>" name="js_code">
                                                        </form>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <h5 class="text-warning mt-3">📑 Status Pengumpulan</h5>
                                        <table class="table table-bordered mt-3">
                                            <tr>
                                                <th class="bg-light">📝 Catatan Guru</th>
                                                <td><?= nl2br(htmlspecialchars($pengumpulan['catatan_guru'] ?? 'Tidak ada catatan')); ?></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">📊 Nilai</th>
                                                <td><?= htmlspecialchars($pengumpulan['nilai'] ?? 'Belum dinilai'); ?></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">📁 File yang Dikumpulkan</th>
                                                <td>
                                                    <?php if ($pengumpulan['file_tugas']): ?>
                                                        <a href="<?= htmlspecialchars($pengumpulan['file_tugas']); ?>" target="_blank" class="btn btn-success btn-sm">
                                                            <i class="bi bi-file-earmark-text"></i> <?= htmlspecialchars(basename($pengumpulan['file_tugas'])); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-danger">Tidak ada file diunggah</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">📁 Kode yang Dikumpulkan</th>
                                                <td>
                                                    <?php if (!empty($pengumpulan['html_code']) || !empty($pengumpulan['css_code']) || !empty($pengumpulan['js_code'])): ?>
                                                        <div class="mt-2">
                                                            <?php if (!empty($pengumpulan['html_code'])): ?>
                                                                <h6>📜 Kode HTML</h6>
                                                                <pre class="border p-3 bg-light"><code><?= htmlspecialchars($pengumpulan['html_code']); ?></code></pre>
                                                            <?php endif; ?>

                                                            <?php if (!empty($pengumpulan['css_code'])): ?>
                                                                <h6>🎨 Kode CSS</h6>
                                                                <pre class="border p-3 bg-light"><code><?= htmlspecialchars($pengumpulan['css_code']); ?></code></pre>
                                                            <?php endif; ?>

                                                            <?php if (!empty($pengumpulan['js_code'])): ?>
                                                                <h6>⚡ Kode JavaScript</h6>
                                                                <pre class="border p-3 bg-light"><code><?= htmlspecialchars($pengumpulan['js_code']); ?></code></pre>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-danger">Tidak ada kode yang dikumpulkan.</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    <?php endif; ?>
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
        function navigateToPage() {
            window.history.back();
        }

        function runCode(id) {
            let htmlCode = document.getElementById(`html-code-${id}`).value;
            let cssCode = "<style>" + document.getElementById(`css-code-${id}`).value + "</style>";
            let jsCode = "<script>" + document.getElementById(`js-code-${id}`).value + "<\/script>";

            let outputFrame = document.getElementById(`output-${id}`).contentWindow.document;
            outputFrame.open();
            outputFrame.write(htmlCode + cssCode + jsCode);
            outputFrame.close();
        }

        function submitCode(id_tugas) {
            let html = document.getElementById("html-code-" + id_tugas).value.trim();
            let css = document.getElementById("css-code-" + id_tugas).value.trim();
            let js = document.getElementById("js-code-" + id_tugas).value.trim();

            // Jika tidak diisi, tetap kirim string kosong
            html = html !== "" ? html : "";
            css = css !== "" ? css : "";
            js = js !== "" ? js : "";

            let formData = new FormData();
            formData.append("id_tugas", id_tugas);
            formData.append("html_code", html);
            formData.append("css_code", css);
            formData.append("js_code", js);

            fetch("submit_livecode.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === "success") {
                        location.reload();
                    }
                })
                .catch(error => console.error("Error:", error));
        }
    </script>

</body>

</html>