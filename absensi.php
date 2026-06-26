<?php
require_once 'config.php';
cekLogin();
cekMahasiswa();

$mahasiswa_id = $_SESSION['user_id'];
$mk_id = intval($_GET['mk_id'] ?? 0);

// pastikan mahasiswa terdaftar di matkul ini
$cek = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT e.*, mk.nama_mk, mk.kode_mk, mk.sks, u.nama AS nama_dosen
     FROM enrollment e
     JOIN mata_kuliah mk ON e.mata_kuliah_id = mk.id
     JOIN users u ON mk.dosen_id = u.id
     WHERE e.mahasiswa_id = $mahasiswa_id AND e.mata_kuliah_id = $mk_id"
));

if (!$cek) {
    echo "<div style='color:red;text-align:center;margin-top:50px;'>Kamu tidak terdaftar di mata kuliah ini!</div>";
    exit();
}

$pesan = "";
$tipe_pesan = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pertemuan_id'])) {
    $pertemuan_id = intval($_POST['pertemuan_id']);
    $status_hadir = $_POST['status_hadir'];

    // pertemuan harus milik matkul ini dan masih aktif
    $cek_pertemuan = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM pertemuan WHERE id = $pertemuan_id AND mata_kuliah_id = $mk_id AND status = 'aktif'"
    ));

    if (!$cek_pertemuan) {
        $pesan = "Pertemuan tidak ditemukan atau sudah ditutup!";
        $tipe_pesan = "error";
    } else {
        $sudah = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT id FROM absensi WHERE mahasiswa_id = $mahasiswa_id AND pertemuan_id = $pertemuan_id"
        ));

        if ($sudah) {
            $pesan = "Kamu sudah melakukan absensi untuk pertemuan ini!";
            $tipe_pesan = "error";
        } else {
            $sql_insert = "INSERT INTO absensi (mahasiswa_id, pertemuan_id, status_hadir)
                           VALUES ($mahasiswa_id, $pertemuan_id, '$status_hadir')";
            if (mysqli_query($conn, $sql_insert)) {
                $pesan = "Absensi berhasil disimpan sebagai: " . strtoupper($status_hadir);
                $tipe_pesan = "success";
            } else {
                $pesan = "Gagal menyimpan absensi. Coba lagi.";
                $tipe_pesan = "error";
            }
        }
    }
}

// semua pertemuan untuk matkul ini
$sql_pertemuan = "
    SELECT p.*, 
           a.id AS absen_id, 
           a.status_hadir, 
           a.waktu_absen
    FROM pertemuan p
    LEFT JOIN absensi a ON a.pertemuan_id = p.id AND a.mahasiswa_id = $mahasiswa_id
    WHERE p.mata_kuliah_id = $mk_id
    ORDER BY p.pertemuan_ke ASC
";
$daftar_pertemuan = mysqli_query($conn, $sql_pertemuan);

// rekap kehadiran
$sql_rekap = "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN a.status_hadir = 'hadir' THEN 1 ELSE 0 END) AS hadir,
        SUM(CASE WHEN a.status_hadir = 'izin'  THEN 1 ELSE 0 END) AS izin,
        SUM(CASE WHEN a.status_hadir = 'sakit' THEN 1 ELSE 0 END) AS sakit,
        SUM(CASE WHEN a.status_hadir = 'alpha' THEN 1 ELSE 0 END) AS alpha
    FROM pertemuan p
    LEFT JOIN absensi a ON a.pertemuan_id = p.id AND a.mahasiswa_id = $mahasiswa_id
    WHERE p.mata_kuliah_id = $mk_id
";
$rekap = mysqli_fetch_assoc(mysqli_query($conn, $sql_rekap));
$persen = $rekap['total'] > 0 ? round(($rekap['hadir'] / $rekap['total']) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi - <?= htmlspecialchars($cek['nama_mk']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo"><span>Absensi</span>Kampus</div>
    <div class="nav-links">
        <a href="dashboard_mahasiswa.php">Dashboard</a>
        <a href="riwayat_absensi.php">Riwayat</a>
        <a href="logout.php">Keluar</a>
    </div>
    <div class="user-info"><?= htmlspecialchars($_SESSION['nama']) ?></div>
</nav>

<div class="container">
    <div style="font-size:13px; color:#5a7ab0; margin-bottom:12px;">
        <a href="dashboard_mahasiswa.php" style="color:#8aabff;">Dashboard</a> &rsaquo; Absensi
    </div>

    <div class="page-title"><span><?= htmlspecialchars($cek['kode_mk']) ?></span> — <?= htmlspecialchars($cek['nama_mk']) ?></div>
    <div class="page-subtitle">
        <?= htmlspecialchars($cek['nama_dosen']) ?> &nbsp;|&nbsp; <?= $cek['sks'] ?> SKS
    </div>

    <?php if ($pesan): ?>
        <div class="alert alert-<?= $tipe_pesan == 'success' ? 'success' : 'error' ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid" style="margin-bottom:20px;">
        <div class="stat-card">
            <div class="number" style="color:<?= $persen >= 75 ? '#00e676' : '#ff6b6b' ?>"><?= $persen ?>%</div>
            <div class="label">Kehadiran</div>
        </div>
        <div class="stat-card">
            <div class="number" style="color:#00e676"><?= $rekap['hadir'] ?></div>
            <div class="label">Hadir</div>
        </div>
        <div class="stat-card">
            <div class="number" style="color:#ffc107"><?= intval($rekap['izin']) + intval($rekap['sakit']) ?></div>
            <div class="label">Izin / Sakit</div>
        </div>
        <div class="stat-card">
            <div class="number" style="color:#ff6b6b"><?= $rekap['alpha'] ?></div>
            <div class="label">Alpha</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Daftar Pertemuan</div>
            <?php if ($persen < 75): ?>
                <span style="font-size:12px; color:#ff6b6b;">Kehadiran di bawah 75%!</span>
            <?php endif; ?>
        </div>

        <?php if (mysqli_num_rows($daftar_pertemuan) == 0): ?>
            <div class="alert alert-info">Belum ada pertemuan yang dibuat oleh dosen.</div>
        <?php else: ?>
            <?php while ($p = mysqli_fetch_assoc($daftar_pertemuan)): ?>
            <div class="pertemuan-item">
                <div class="pertemuan-info">
                    <div class="nomor">PERTEMUAN <?= $p['pertemuan_ke'] ?></div>
                    <div class="topik"><?= htmlspecialchars($p['topik'] ?? 'Tidak ada topik') ?></div>
                    <div class="tanggal"><?= date('d F Y', strtotime($p['tanggal'])) ?></div>
                </div>

                <div>
                    <?php if ($p['absen_id']): ?>
                        <div class="sudah-absen">
                            <span class="badge badge-<?= $p['status_hadir'] ?>">
                                <?= strtoupper($p['status_hadir']) ?>
                            </span>
                            <span style="font-size:11px; color:#5a7ab0;">
                                <?= date('H:i', strtotime($p['waktu_absen'])) ?>
                            </span>
                        </div>

                    <?php elseif ($p['status'] == 'aktif'): ?>
                        <form method="POST" style="display:flex; gap:8px; align-items:center;">
                            <input type="hidden" name="pertemuan_id" value="<?= $p['id'] ?>">
                            <select name="status_hadir" style="width:auto;">
                                <option value="hadir">Hadir</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                            </select>
                            <button type="submit" class="btn btn-success btn-sm">Absen</button>
                        </form>

                    <?php else: ?>
                        <span style="font-size:12px; color:#5a7ab0;">Tidak hadir</span>
                        <span class="badge badge-alpha" style="margin-left:6px;">ALPHA</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <a href="dashboard_mahasiswa.php" class="btn btn-outline">Kembali ke Dashboard</a>
</div>

<div class="footer">Sistem Absensi Kampus 2024</div>
</body>
</html>
