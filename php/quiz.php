<?php

include 'koneksi.php';

// Cek metode permintaan
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Ambil soal dari database
    $sql = "SELECT id, question, option_a, option_b, option_c, option_d FROM soal";
    $result = $koneksi->query($sql);

    $soal = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $soal[] = $row;
        }
    }

    // Kembalikan data dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($soal);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Periksa jawaban
    $score = 0;

    foreach ($_POST as $question_id => $user_answer) {
        $question_id = str_replace('q', '', $question_id); // Hapus prefix 'q'
        $sql = "SELECT correct_option FROM soal WHERE id = $question_id";
        $result = $koneksi->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['correct_option'] === $user_answer) {
                $score++;
            }
        }
    }

    // Kembalikan skor
    echo $score;
}

$koneksi->close();
?>