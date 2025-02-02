<?php
// Koneksi ke database
include 'koneksi.php';
// Jika metode permintaan adalah POST (jawaban dikirimkan)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;

    // Periksa jawaban
    foreach ($_POST as $question_id => $user_answer) {
        $question_id = intval(str_replace('q', '', $question_id)); // Hapus prefix 'q'
        $sql = "SELECT correct_option FROM soal WHERE id = $question_id";
        $result = $koneksi->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['correct_option'] == $user_answer) { // Gunakan perbandingan longgar
                $score++;
            }
        }
    }

    // Tampilkan skor
    echo "<h2>Skor Anda: $score</h2>";
    exit;
}

// Jika metode permintaan adalah GET (ambil soal)
$sql = "SELECT id, question, option_a, option_b, option_c, option_d FROM soal";
$result = $koneksi->query($sql);

$soal = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $soal[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latihan Soal Pilihan Ganda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .question {
            margin-bottom: 20px;
        }
        .options {
            list-style: none;
            padding: 0;
        }
        .options li {
            margin-bottom: 10px;
        }
        .options input {
            margin-right: 10px;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Latihan Soal</h1>
        <form method="POST" action="">
            <?php foreach ($soal as $index => $item): ?>
                <div class="question">
                    <p><?= ($index + 1) . ". " . $item['question']; ?></p>
                    <ul class="options">
                        <li><label><input type="radio" name="q<?= $item['id']; ?>" value="A"> <?= $item['option_a']; ?></label></li>
                        <li><label><input type="radio" name="q<?= $item['id']; ?>" value="B"> <?= $item['option_b']; ?></label></li>
                        <li><label><input type="radio" name="q<?= $item['id']; ?>" value="C"> <?= $item['option_c']; ?></label></li>
                        <li><label><input type="radio" name="q<?= $item['id']; ?>" value="D"> <?= $item['option_d']; ?></label></li>
                    </ul>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn">Kirim Jawaban</button>
        </form>
    </div>
</body>
</html>