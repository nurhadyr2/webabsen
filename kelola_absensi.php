<?php
require_once 'config.php';
cekLogin();
cekDosen();

$dosen_id = $_SESSION['user_id'];
$mk_id = intval($_GET['mk_id'] ?? 0);

// pastikan matkul ini milik dosen yang login
$mk = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM mata_kuliah WHERE id = $mk_id AND dosen_id = $dosen_id"
));
if (!$mk) {
    echo "<div style='color:red;text-align:center;padding:50px;'>Mata kuliah tidak ditemukan!</div>";
    exit();
}

// semua mahasiswa yang terdaftar beserta rekapnya
$sql_mahasiswa = "
    SELECT u.id, u.nim_nip, u.nama,
           COUNT(DISTINCT p.id) AS total_pertemuan,
           SUM(CASE WHEN a.status_hadir = 'hadir' THEN 1 ELSE 0 END) AS hadir,
           SUM(CASE WHEN a.status_hadir = 'izin'  THEN 1 ELSE 0 END) AS izin,
           SUM(CASE WHEN a.status_hadir = 'sakit' THEN 1 ELSE 0 END) AS sakit,
           SUM(CASE WHEN a.status_hadir = 'alpha' THEN 1 ELSE 0 END) AS alpha
    FROM enrollment e
    JOIN users u ON e.mahasiswa_id = u.id
    LEFT JOIN pertemuan p ON p.mata_kuliah_id = $mk_id
    LEFT JOIN absensi a ON a.pertemuan_id = p.id AND a.mahasiswa_id = u.id
    WHERE e.mata_kuliah_id = $mk_id
    GROUP BY u.id
    ORDER BY u.nama
";
$hasil = mysqli_query($conn, $sql_mahasiswa);

// daftar pertemuan untuk tabel detail
$pertemuan_list = mysqli_fetch_all(
    mysqli_query($conn, "SELECT * FROM pertemuan WHERE mata_kuliah_id = $mk_id ORDER BY pertemuan_ke"),
    MYSQLI_ASSOC
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi - <?= htmlspecialchars($mk['nama_mk']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo"><span>Absensi</span>Kampus</div>
    <div class="nav-links">
        <a href="dashboard_dosen.php">Dashboard</a>
        <a href="tambah_matkul.php">Tambah Matkul</a>
        <a href="logout.php">Keluar</a>
    </div>
    <div class="user-info"><?= htmlspecialchars($_SESSION['nama']) ?></div>
</nav>

<div class="container">
    <div style="font-size:13px; color:#5a7ab0; margin-bottom:12px;">
        <a href="dashboard_dosen.php" style="color:#8aabff;">Dashboard</a> &rsaquo; Rekap Absensi
    </div>

    <div class="page-title">Rekap Absensi — <span><?= htmlspecialchars($mk['nama_mk']) ?></span></div>
    <div class="page-subtitle">Kode: <?= $mk['kode_mk'] ?> | Semester: <?= $mk['semester'] ?></div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Rekap Kehadiran Mahasiswa</div>
            <a href="tambah_pertemuan.php?mk_id=<?= $mk_id ?>" class="btn btn-primary btn-sm">Tambah Pertemuan</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th style="color:#00e676">Hadir</th>
                        <th style="color:#ffc107">Izin</th>
                        <th style="color:#82b4ff">Sakit</th>
                        <th style="color:#ff6b6b">Alpha</th>
                        <th>Total</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; while ($mhs = mysqli_fetch_assoc($hasil)): 
                    $total = $mhs['total_pertemuan'];
                    $persen = $total > 0 ? round(($mhs['hadir'] / $total) * 100) : 0;
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td style="color:#8aabff; font-weight:600"><?= htmlspecialchars($mhs['nim_nip']) ?></td>
                        <td><?= htmlspecialchars($mhs['nama']) ?></td>
                        <td style="color:#00e676; text-align:center"><?= $mhs['hadir'] ?></td>
                        <td style="color:#ffc107; text-align:center"><?= $mhs['izin'] ?></td>
                        <td style="color:#82b4ff; text-align:center"><?= $mhs['sakit'] ?></td>
                        <td style="color:#ff6b6b; text-align:center"><?= $mhs['alpha'] ?></td>
                        <td style="text-align:center"><?= $total ?></td>
                        <td>
                            <span style="color: <?= $persen >= 75 ? '#00e676' : '#ff6b6b' ?>; font-weight:700">
                                <?= $persen ?>%
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($pertemuan_list)): ?>
    <div class="card">
        <div class="card-header">
            <div class="card-title">Detail Per Pertemuan</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Pertemuan</th>
                        <th>Topik</th>
                        <th>Tanggal</th>
                        <th>Total Hadir</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pertemuan_list as $p): 
                    $jml_hadir = mysqli_fetch_assoc(mysqli_query($conn,
                        "SELECT COUNT(*) AS jml FROM absensi WHERE pertemuan_id = {$p['id']} AND status_hadir = 'hadir'"
                    ))['jml'];
                ?>
                    <tr>
                        <td style="font-weight:700; color:#2971FF">Pertemuan <?= $p['pertemuan_ke'] ?></td>
                        <td><?= htmlspecialchars($p['topik'] ?? '-') ?></td>
                        <td><?= date('d/m/Y', strtotime($p['tanggal'])) ?></td>
                        <td><?= $jml_hadir ?> mahasiswa</td>
                        <td><span class="badge badge-<?= $p['status'] ?>"><?= strtoupper($p['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <a href="dashboard_dosen.php" class="btn btn-outline">Kembali ke Dashboard</a>
</div>

<div class="footer">Sistem Absensi Kampus 2026</div>
</body>
</html>
