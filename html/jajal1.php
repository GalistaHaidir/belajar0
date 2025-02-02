<?php
include("koneksi.php");

// Menangani operasi CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['upload'])) {
        $title = $_FILES['pdf_file']['name'];
        $file_path = 'pdf/' . basename($title);
        
        // Mengunggah file
        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $file_path)) {
            $sql = "INSERT INTO materi (title, file_path) VALUES ('$title', '$file_path')";
            $koneksi->query($sql);
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $sql = "SELECT file_path FROM materi WHERE id=$id";
        $result = $koneksi->query($sql);
        $row = $result->fetch_assoc();
        unlink($row['file_path']); // Menghapus file dari server
        $sql = "DELETE FROM materi WHERE id=$id";
        $koneksi->query($sql);
    }
}

// Menampilkan data
$sql = "SELECT id, title, file_path FROM materi";
$result = $koneksi->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>CRUD PDF</title>
</head>
<body>
    <h1>CRUD PDF</h1>
    
    <h2>Unggah File PDF</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="pdf_file" accept="application/pdf" required>
        <button type="submit" name="upload">Unggah</button>
    </form>

    <h2>Daftar File PDF</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nama File</th>
            <th>Aksi</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['title']; ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete">Hapus</button>
                </form>
                <a href="<?php echo $row['file_path']; ?>" target="_blank">Lihat</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php $koneksi->close(); ?>
</body>
</html>