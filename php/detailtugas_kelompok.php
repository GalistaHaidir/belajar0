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


$id_proyek = $_GET['id_proyek'] ?? 0;
$id_pengguna = $_SESSION['id_pengguna'] ?? 0;

// Pastikan user login
if (!$id_pengguna) {
    die("Silakan login terlebih dahulu.");
}

// Ambil ID kelompok pengguna dari tabel akses_kelompok
$query_kelompok = $koneksi->prepare("
    SELECT id_kelompok FROM akses_kelompok WHERE id_pengguna = ?
");
$query_kelompok->bind_param("i", $id_pengguna);
$query_kelompok->execute();
$result_kelompok = $query_kelompok->get_result();
$data_kelompok = $result_kelompok->fetch_assoc();

if (!$data_kelompok) {
    die("Anda belum tergabung dalam kelompok mana pun.");
}

$id_kelompok = $data_kelompok['id_kelompok'];

// Ambil semua tugas berdasarkan proyek
$query_tugas = $koneksi->prepare("
    SELECT * FROM tugas WHERE id_proyek = ? ORDER BY dateline ASC
");
$query_tugas->bind_param("i", $id_proyek);
$query_tugas->execute();
$result_tugas = $query_tugas->get_result();
$tugas_list = $result_tugas->fetch_all(MYSQLI_ASSOC);

// Jika tidak ada tugas, tampilkan pesan
if (!$tugas_list) {
    die("Tidak ada tugas untuk proyek ini.");
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
    <title>Detail Tugas Kelompok</title>
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

        .chat-item {
            padding: 8px 12px;
            border-radius: 12px;
            margin-bottom: 8px;
            max-width: 75%;
            word-wrap: break-word;
            display: inline-block;
            position: relative;
        }

        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* Menyelaraskan nama dan waktu secara vertikal */
            font-size: 0.85em;
            margin-bottom: 4px;
        }

        .chat-nama {
            font-weight: bold;
            margin-right: 8px;
            /* Jarak antara nama dan waktu */
        }

        .chat-waktu {
            font-size: 0.75em;
            color: gray;
        }

        .chat-teman {
            background: #f1f1f1;
            align-self: flex-start;
            border-radius: 8px 8px 8px 0;
            padding: 6px 10px;
        }

        .chat-pengirim {
            background: #dcf8c6;
            color: black;
            align-self: flex-end;
            text-align: left;
            border-radius: 8px 8px 0 8px;
            padding: 6px 10px;
        }

        #chatBody {
            display: flex;
            flex-direction: column;
            padding: 10px;
            background: #e5ddd5;
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
                    <h1 class="mb-4 text-center text-primary">📚 Daftar Tugas dalam Proyek</h1>
                    <div class="row">
                        <?php foreach ($tugas_list as $tugas): ?>
                            <?php
                            // Cek apakah tugas sudah dikumpulkan
                            $query_pengumpulan = $koneksi->prepare("
            SELECT * FROM pengumpulan_tugas WHERE id_kelompok = ? AND id_tugas = ?
        ");
                            $query_pengumpulan->bind_param("ii", $id_kelompok, $tugas['id_tugas']);
                            $query_pengumpulan->execute();
                            $result_pengumpulan = $query_pengumpulan->get_result();
                            $pengumpulan = $result_pengumpulan->fetch_assoc();

                            // Warna kartu berdasarkan status
                            $card_class = $pengumpulan ? 'border-success' : 'border-warning';
                            $status_badge = $pengumpulan
                                ? '<span class="badge bg-success">✅ Sudah Dikumpulkan</span>'
                                : '<span class="badge bg-warning text-dark">⚠️ Belum Dikumpulkan</span>';
                            ?>

                            <div class="col-md-12 mb-3">
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
                                            <!-- Tab Pilihan Upload File atau Kode -->
                                            <h5 class="text-success mt-3">📤 Pilih Cara Mengumpulkan Tugas</h5>

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

                                            <div class="tab-content mt-3" id="tabContent">
                                                <!-- Tab Unggah File -->
                                                <div class="tab-pane fade show active" id="upload" role="tabpanel">
                                                    <form action="upload_tugas_kelompok.php" method="POST" enctype="multipart/form-data">
                                                        <input type="hidden" name="id_proyek" value="<?= $id_proyek; ?>">
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
                                                                    <textarea id="html-code-<?= htmlspecialchars($tugas['id_tugas']); ?>" class="form-control"></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="fw-bold">CSS</label>
                                                                    <textarea id="css-code-<?= htmlspecialchars($tugas['id_tugas']); ?>" class="form-control"></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="fw-bold">JavaScript</label>
                                                                    <textarea id="js-code-<?= htmlspecialchars($tugas['id_tugas']); ?>" class="form-control"></textarea>
                                                                </div>
                                                                <button class="btn btn-primary w-100 mt-2" onclick="runCode(<?= htmlspecialchars($tugas['id_tugas']); ?>)">
                                                                    ▶️ Jalankan Kode
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <h5 class="text-center">🔍 Output:</h5>
                                                            <div class="card p-3">
                                                                <iframe id="output-<?= htmlspecialchars($tugas['id_tugas']); ?>" class="w-100" style="height: 300px;"></iframe>
                                                            </div>

                                                            <!-- Form untuk mengirim kode -->
                                                            <form id="submit-code-form-<?= htmlspecialchars($tugas['id_tugas']); ?>" method="POST">
                                                                <input type="hidden" name="id_proyek" value="<?= htmlspecialchars($id_proyek); ?>">
                                                                <input type="hidden" name="id_tugas" value="<?= htmlspecialchars($tugas['id_tugas']); ?>">
                                                                <input type="hidden" id="html-input-<?= htmlspecialchars($tugas['id_tugas']); ?>" name="html_code">
                                                                <input type="hidden" id="css-input-<?= htmlspecialchars($tugas['id_tugas']); ?>" name="css_code">
                                                                <input type="hidden" id="js-input-<?= htmlspecialchars($tugas['id_tugas']); ?>" name="js_code">

                                                                <!-- Tombol submit di dalam form -->
                                                                <button type="submit" class="btn btn-success w-100 mt-3">
                                                                    📤 Simpan & Kumpulkan
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Status Pengumpulan -->
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
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="position-fixed end-0 top-50 translate-middle-y me-3" style="width: 320px; z-index: 1050;">
                    <div class="card shadow-lg border-primary">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h6 class="m-0">💬 Diskusi Kelompok</h6>
                            <button class="btn btn-sm btn-light" onclick="toggleChat()">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-body p-2" id="chatBody" style="height: 300px; overflow-y: auto;">

                        </div>
                        <div class="card-footer p-2">
                            <form id="chat-form" action="simpan_chat.php" method="POST">
                                <input type="hidden" name="id_proyek" value="<?= $id_proyek; ?>">
                                <input type="hidden" name="id_pengguna" value="<?= $_SESSION['id_pengguna']; ?>">
                                <input type="hidden" name="id_kelompok" value="<?= $id_kelompok; ?>">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="chat" id="chatInput" placeholder="Ketik pesan..." required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send"></i>
                                    </button>
                                </div>
                            </form>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        // Event listener untuk semua form dengan ID yang diawali "submit-code-form-"
        document.querySelectorAll("form[id^='submit-code-form-']").forEach(form => {
            form.addEventListener("submit", function(event) {
                event.preventDefault(); // Mencegah form refresh halaman

                let id_tugas = this.querySelector("[name='id_tugas']").value;
                let id_proyek = this.querySelector("[name='id_proyek']").value;
                let html = document.getElementById("html-code-" + id_tugas).value.trim();
                let css = document.getElementById("css-code-" + id_tugas).value.trim();
                let js = document.getElementById("js-code-" + id_tugas).value.trim();

                let formData = new FormData();
                formData.append("id_tugas", id_tugas);
                formData.append("id_proyek", id_proyek);
                formData.append("html_code", html);
                formData.append("css_code", css);
                formData.append("js_code", js);

                fetch("submit_codekelompok.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json()) // Pastikan response JSON
                    .then(data => {
                        alert(data.message);
                        if (data.status === "success") {
                            window.location.href = `detailtugas_kelompok.php?id_proyek=${id_proyek}`;
                        }
                    })
                    .catch(error => console.error("Error:", error));
            });
        });

        $(document).ready(function() {
            // Mengirim chat dengan AJAX
            $("#chat-form").submit(function(e) {
                e.preventDefault();
                $.post("simpan_chat.php", $(this).serialize(), function(data) {
                    if (data === "success") {
                        $("#chatInput").val("");
                        loadChat();
                    }
                });
            });

            // Fungsi untuk memperbarui chat secara otomatis
            function loadChat() {
                $("#chatBody").load("tampil_chat.php?id_proyek=<?= $id_proyek; ?>&id_kelompok=<?= $id_kelompok; ?>");
            }
            setInterval(loadChat, 3000); // Refresh chat setiap 3 detik
        });

        function toggleChat() {
            $("#chatBody").toggle();
        }
    </script>
</body>

</html>