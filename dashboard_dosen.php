<?php
require_once 'config.php';
cekLogin();
cekDosen();

$dosen_id = $_SESSION['user_id'];

// daftar matkul yang diajar dosen
$sql_matkul = "
    SELECT mk.*, 
           COUNT(DISTINCT e.mahasiswa_id) AS total_mahasiswa,
           COUNT(DISTINCT p.id) AS total_pertemuan
    FROM mata_kuliah mk
    LEFT JOIN enrollment e ON e.mata_kuliah_id = mk.id
    LEFT JOIN pertemuan p ON p.mata_kuliah_id = mk.id
    WHERE mk.dosen_id = $dosen_id
    GROUP BY mk.id
";
$hasil_matkul = mysqli_query($conn, $sql_matkul);
$total_matkul = mysqli_num_rows($hasil_matkul);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo"><span>Absensi</span>Kampus</div>
    <div class="nav-links">
        <a href="dashboard_dosen.php" class="aktif">Dashboard</a>
        <a href="tambah_matkul.php">Tambah Matkul</a>
        <a href="logout.php">Keluar</a>
    </div>
    <div class="user-info"><?= htmlspecialchars($_SESSION['nama']) ?></div>
</nav>

<div class="container">
    <div class="page-title">Dashboard <span>Dosen</span></div>
    <div class="page-subtitle">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?> — NIP: <?= $_SESSION['nim_nip'] ?></div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?= $total_matkul ?></div>
            <div class="label">Mata Kuliah Diajar</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Mata Kuliah Saya</div>
            <a href="tambah_matkul.php" class="btn btn-primary btn-sm">Tambah Matkul</a>
        </div>

        <?php if ($total_matkul == 0): ?>
            <div class="alert alert-info">Belum ada mata kuliah. Klik "Tambah Matkul" untuk mulai.</div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Mahasiswa</th>
                            <th>Pertemuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    mysqli_data_seek($hasil_matkul, 0);
                    while ($mk = mysqli_fetch_assoc($hasil_matkul)): ?>
                        <tr>
                            <td><strong style="color:#2971FF"><?= htmlspecialchars($mk['kode_mk']) ?></strong></td>
                            <td><?= htmlspecialchars($mk['nama_mk']) ?></td>
                            <td><?= $mk['sks'] ?> SKS</td>
                            <td><?= $mk['total_mahasiswa'] ?></td>
                            <td><?= $mk['total_pertemuan'] ?></td>
                            <td style="display:flex; gap:6px;">
                                <a href="kelola_absensi.php?mk_id=<?= $mk['id'] ?>" class="btn btn-outline btn-sm">Absensi</a>
                                <a href="input_nilai.php?mk_id=<?= $mk['id'] ?>" class="btn btn-success btn-sm">Nilai</a>
                                <a href="tambah_pertemuan.php?mk_id=<?= $mk['id'] ?>" class="btn btn-sm" style="background:#001F5C; color:#fff; border:1px solid #2971FF;">Pertemuan</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="footer">Sistem Absensi Kampus 2024</div>
</body>
</html>
