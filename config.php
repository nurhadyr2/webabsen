<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "absensi_db";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("<h3 style='color:red; text-align:center; margin-top:50px;'>
        Gagal konek ke database!<br>
        <small>" . mysqli_connect_error() . "</small>
    </h3>");
}

mysqli_set_charset($conn, "utf8");

session_start();

function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function cekDosen() {
    if ($_SESSION['role'] !== 'dosen') {
        header("Location: dashboard_mahasiswa.php");
        exit();
    }
}

function cekMahasiswa() {
    if ($_SESSION['role'] !== 'mahasiswa') {
        header("Location: dashboard_dosen.php");
        exit();
    }
}
?>
