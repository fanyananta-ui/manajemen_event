<?php

include 'koneksi.php';

$id = $_GET['id'] ?? '';

if($id == ''){
    die('ID tidak ditemukan');
}

$data = mysqli_query(
    $conn,
    "SELECT file_event FROM event WHERE id='$id'"
);

$row = mysqli_fetch_assoc($data);

if($row && $row['file_event'] != ''){

    $file = "uploads/" . $row['file_event'];

    if(file_exists($file)){
        unlink($file);
    }
}

mysqli_query(
    $conn,
    "DELETE FROM event WHERE id='$id'"
);

header("Location: index.php");
exit;
?>