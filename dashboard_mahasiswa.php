<?php
require_once 'config.php';
cekLogin();
cekMahasiswa();

$mahasiswa_id = $_SESSION['user_id'];

// daftar mata kuliah yang diambil mahasiswa
$sql_matkul = "
    SELECT mk.*, u.nama AS nama_dosen,
           COUNT(DISTINCT p.id) AS total_pertemuan,
           COUNT(DISTINCT a.id) AS total_hadir
    FROM enrollment e
    JOIN mata_kuliah mk ON e.mata_kuliah_id = mk.id
    JOIN users u ON mk.dosen_id = u.id
    LEFT JOIN pertemuan p ON p.mata_kuliah_id = mk.id
    LEFT JOIN absensi a ON a.pertemuan_id = p.id AND a.mahasiswa_id = $mahasiswa_id AND a.status_hadir = 'hadir'
    WHERE e.mahasiswa_id = $mahasiswa_id
    GROUP BY mk.id
";
$hasil_matkul = mysqli_query($conn, $sql_matkul);

$total_mk = mysqli_num_rows($hasil_matkul);
$sql_stat = "
    SELECT 
        COUNT(*) AS total_pertemuan,
        SUM(CASE WHEN a.status_hadir = 'hadir' THEN 1 ELSE 0 END) AS total_hadir
    FROM enrollment e
    JOIN pertemuan p ON p.mata_kuliah_id = e.mata_kuliah_id
    LEFT JOIN absensi a ON a.pertemuan_id = p.id AND a.mahasiswa_id = $mahasiswa_id
    WHERE e.mahasiswa_id = $mahasiswa_id
";
$stat = mysqli_fetch_assoc(mysqli_query($conn, $sql_stat));
$persen_hadir = $stat['total_pertemuan'] > 0 
    ? round(($stat['total_hadir'] / $stat['total_pertemuan']) * 100) 
    : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo"><span>Absensi</span>Kampus</div>
    <div class="nav-links">
        <a href="dashboard_mahasiswa.php" class="aktif">Dashboard</a>
        <a href="riwayat_absensi.php">Riwayat</a>
        <a href="logout.php">Keluar</a>
    </div>
    <div class="user-info"><?= htmlspecialchars($_SESSION['nama']) ?> | <?= $_SESSION['nim_nip'] ?></div>
</nav>

<div class="container">
    <div class="page-title">Selamat datang, <span><?= explode(' ', $_SESSION['nama'])[0] ?></span>!</div>
    <div class="page-subtitle">Semester Ganjil 2025/2026</div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?= $total_mk ?></div>
            <div class="label">Mata Kuliah Diambil</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stat['total_pertemuan'] ?></div>
            <div class="label">Total Pertemuan</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stat['total_hadir'] ?></div>
            <div class="label">Total Hadir</div>
        </div>
        <div class="stat-card">
            <div class="number" style="color: <?= $persen_hadir >= 75 ? '#00e676' : '#ff6b6b' ?>">
                <?= $persen_hadir ?>%
            </div>
            <div class="label">Persentase Kehadiran</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Mata Kuliah Saya</div>
        </div>

        <?php
        mysqli_data_seek($hasil_matkul, 0);
        if (mysqli_num_rows($hasil_matkul) == 0):
        ?>
            <div class="alert alert-info">Kamu belum terdaftar di mata kuliah manapun.</div>
        <?php else: ?>
            <div class="matkul-grid">
            <?php while ($mk = mysqli_fetch_assoc($hasil_matkul)): 
                $persen = $mk['total_pertemuan'] > 0 
                    ? round(($mk['total_hadir'] / $mk['total_pertemuan']) * 100) 
                    : 0;
            ?>
                <div class="matkul-card">
                    <div class="kode"><?= htmlspecialchars($mk['kode_mk']) ?></div>
                    <div class="nama"><?= htmlspecialchars($mk['nama_mk']) ?></div>
                    <div class="info">
                        <span><?= $mk['sks'] ?> SKS</span>
                        <span><?= htmlspecialchars($mk['nama_dosen']) ?></span>
                    </div>
                    <div style="font-size:12px; color:#8aabff; margin-bottom:12px;">
                        Hadir <?= $mk['total_hadir'] ?>/<?= $mk['total_pertemuan'] ?> pertemuan
                        <span style="float:right; color: <?= $persen >= 75 ? '#00e676' : '#ff6b6b' ?>"><?= $persen ?>%</span>
                    </div>
                    <a href="absensi.php?mk_id=<?= $mk['id'] ?>" class="btn btn-primary">
                        Lihat & Absensi
                    </a>
                </div>
            <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="footer">Sistem Absensi Kampus 2026</div>
</body>
</html>
