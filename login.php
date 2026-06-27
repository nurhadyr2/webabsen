<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'dosen') {
        header("Location: dashboard_dosen.php");
    } else {
        header("Location: dashboard_mahasiswa.php");
    }
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim_nip = trim($_POST['nim_nip']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE nim_nip = '$nim_nip'";
    $hasil = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($hasil);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['nim_nip'] = $user['nim_nip'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'dosen') {
            header("Location: dashboard_dosen.php");
        } else {
            header("Location: dashboard_mahasiswa.php");
        }
        exit();
    } else {
        $error = "NIM/NIP atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-box">
        <h2>Sistem Absensi</h2>
        <p class="subtitle">Masuk menggunakan NIM atau NIP</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>NIM / NIP</label>
                <input type="text" name="nim_nip" placeholder="Contoh: 2021001 atau D001" 
                       value="<?= htmlspecialchars($_POST['nim_nip'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn btn-primary">Masuk</button>
        </form>

    </div>
</div>
</body>
</html>
