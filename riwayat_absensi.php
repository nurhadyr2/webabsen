<?php
require_once 'config.php';
cekLogin();
cekMahasiswa();

$mahasiswa_id = $_SESSION['user_id'];

$sql = "
    SELECT a.*, p.pertemuan_ke, p.topik, p.tanggal, 
           mk.nama_mk, mk.kode_mk
    FROM absensi a
    JOIN pertemuan p ON a.pertemuan_id = p.id
    JOIN mata_kuliah mk ON p.mata_kuliah_id = mk.id
    WHERE a.mahasiswa_id = $mahasiswa_id
    ORDER BY a.waktu_absen DESC
";
$hasil = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo"><span>Absensi</span>Kampus</div>
    <div class="nav-links">
        <a href="dashboard_mahasiswa.php">Dashboard</a>
        <a href="riwayat_absensi.php" class="aktif">Riwayat</a>
        <a href="logout.php">Keluar</a>
    </div>
    <div class="user-info"><?= htmlspecialchars($_SESSION['nama']) ?></div>
</nav>

<div class="container">
    <div class="page-title">Riwayat <span>Absensi</span></div>
    <div class="page-subtitle">Semua catatan kehadiran</div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mata Kuliah</th>
                        <th>Pertemuan</th>
                        <th>Topik</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Waktu Absen</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($hasil) == 0): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:#5a7ab0; padding:30px;">
                            Belum ada data absensi.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($hasil)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['kode_mk']) ?></strong><br>
                            <span style="font-size:12px;color:#8aabff"><?= htmlspecialchars($row['nama_mk']) ?></span>
                        </td>
                        <td style="text-align:center"><?= $row['pertemuan_ke'] ?></td>
                        <td><?= htmlspecialchars($row['topik'] ?? '-') ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                        <td>
                            <span class="badge badge-<?= $row['status_hadir'] ?>">
                                <?= strtoupper($row['status_hadir']) ?>
                            </span>
                        </td>
                        <td style="font-size:12px; color:#8aabff">
                            <?= date('d/m/Y H:i', strtotime($row['waktu_absen'])) ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="dashboard_mahasiswa.php" class="btn btn-outline">Kembali ke Dashboard</a>
</div>

<div class="footer">Sistem Absensi Kampus 2026</div>
</body>
</html>
