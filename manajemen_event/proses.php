<?php
session_start();
if (!isset($_SESSION['login'])) { exit; }
include 'koneksi.php';

// 1. PROSES TAMBAH DATA (CREATE)
if (isset($_POST['simpan_event'])) {
    $nama_event    = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tanggal_event = mysqli_real_escape_string($conn, $_POST['tanggal_event']);
    $harga_tiket   = (int)$_POST['harga_tiket'];
    $lokasi        = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $ttd_data      = $_POST['ttd_data']; // Data string Base64 dari Canvas

    // Insert ke tabel utama (events)
    $query = "INSERT INTO events_fany_2430511045 (nama_event, tanggal_event, harga_tiket, lokasi, ttd_panitia) 
              VALUES ('$nama_event', '$tanggal_event', $harga_tiket, '$lokasi', '$ttd_data')";
    
    if (mysqli_query($conn, $query)) {
        $event_id = mysqli_insert_id($conn); // Mengambil ID event terakhir yang baru saja disimpan

        // Pengaturan Direktori Penyimpanan Multiple File Upload
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder otomatis jika belum ada
        }

        // Loop array data multiple files
        foreach ($_FILES['brosur']['name'] as $index => $name) {
            if ($_FILES['brosur']['error'][$index] === 0) {
                $file_tmp  = $_FILES['brosur']['tmp_name'][$index];
                $file_name = time() . "_" . basename($_FILES['brosur']['name'][$index]);
                $target_file = $target_dir . $file_name;

                if (move_uploaded_file($file_tmp, $target_file)) {
                    // Simpan nama file ke tabel lampiran (event_files)
                    mysqli_query($conn, "INSERT INTO event_files_fany_2430511045 (event_id, nama_file) VALUES ($event_id, '$file_name')");
                }
            }
        }
        header("Location: index.php");
    } else {
        echo "Gagal menyimpan data ke database.";
    }
}

// 2. PROSES HAPUS DATA (DELETE)
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    // Hapus file fisik dari folder uploads terlebih dahulu
    $file_res = mysqli_query($conn, "SELECT nama_file_fany_2430511045 FROM event_files_fany_2430511045 WHERE event_id = $id");
    while ($f = mysqli_fetch_assoc($file_res)) {
        $path = "uploads/" . $f['nama_file'];
        if (file_exists($path)) {
            unlink($path); // Menghapus file fisik
        }
    }

    // Hapus data dari database (Relasi ON DELETE CASCADE menghapus file di tabel anak secara otomatis)
    mysqli_query($conn, "DELETE FROM event_fany_2430511045 WHERE id = $id");
    header("Location: index.php");
}
?>
