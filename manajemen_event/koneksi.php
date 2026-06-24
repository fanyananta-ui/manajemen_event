<?php
$host = "localhost";
$user = "ifummiid_kelasc";
$pass = "pemweb_db_c";
$db   = "ifummiid_kelasc";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
