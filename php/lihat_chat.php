<?php
session_start();


include 'koneksi.php'; // Koneksi ke database

// Ambil id_proyek dari parameter URL
if (!isset($_GET['id_proyek'])) {
    die("Error: ID Proyek tidak ditemukan.");
}
$id_proyek = $_GET['id_proyek'];

// Query untuk mengambil detail proyek berdasarkan id_proyek
$query_proyek = $koneksi->prepare("SELECT * FROM tugas WHERE id_proyek = ?");
$query_proyek->bind_param("i", $id_proyek);
$query_proyek->execute();
$proyek = $query_proyek->get_result()->fetch_assoc();

if (!$proyek) {
    die("Error: Proyek tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Chat Diskusi Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
        }

        .chat-message {
            margin-bottom: 10px;
        }

        .chat-message .sender {
            font-weight: bold;
            color: #007bff;
        }

        .chat-message .time {
            font-size: 0.8em;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Monitor Chat Diskusi Siswa</h1>
        <hr>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Proyek: <?= htmlspecialchars($proyek['judul_tugas']) ?></h5>
            </div>
            <div class="card-body">
                <div class="chat-container">
                    <?php
                    // Query untuk mengambil chat dari proyek ini
                    $query_chat = $koneksi->prepare("
                        SELECT d.chat, p.namaLengkap, d.waktu 
                        FROM diskusi d 
                        JOIN pengguna p ON d.id_pengguna = p.id_pengguna 
                        WHERE d.id_proyek = ? 
                        ORDER BY d.waktu ASC
                    ");
                    $query_chat->bind_param("i", $id_proyek);
                    $query_chat->execute();
                    $result_chat = $query_chat->get_result();

                    if ($result_chat->num_rows > 0) {
                        while ($chat = $result_chat->fetch_assoc()):
                    ?>
                            <div class="chat-message">
                                <div class="sender"><?= htmlspecialchars($chat['namaLengkap']) ?></div>
                                <div class="message"><?= nl2br(htmlspecialchars($chat['chat'])) ?></div>
                                <div class="time"><?= date('d M Y H:i', strtotime($chat['waktu'])) ?></div>
                            </div>
                    <?php
                        endwhile;
                    } else {
                        echo "<div class='text-muted'>Belum ada chat untuk proyek ini.</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>