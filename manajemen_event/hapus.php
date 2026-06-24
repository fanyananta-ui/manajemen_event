<?php
include 'koneksi.php';

// Ambil ID dari URL
$id = $_GET['id'];

// 1. Ambil nama file dari kolom yang benar yaitu 'file_event'
$sql = "SELECT file_event FROM event_fany_2430511045 WHERE id='$id'";
$query = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($query);

// 2. Hapus file fisik jika ada
if($row && !empty($row['file_event'])) {
    $file_path = 'uploads/' . $row['file_event'];
    if(file_exists($file_path)) {
        unlink($file_path);
    }
}

// 3. Hapus data dari database
mysqli_query($conn, "DELETE FROM event_fany_2430511045 WHERE id='$id'");

// 4. Kembali ke halaman utama
header("Location: index.php");
exit();
?>
