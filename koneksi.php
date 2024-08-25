<?php
$servername = "localhost";
$username = "root";
$password = "6508460";
$dbname = "koleksi_buku";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
