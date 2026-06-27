<?php
require_once 'config.php';
cekLogin();
cekDosen();

$dosen_id = $_SESSION['user_id'];
$mk_id = intval($_GET['mk_id'] ?? 0);

$mk = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM mata_kuliah WHERE id = $mk_id AND dosen_id = $dosen_id"
));
if (!$mk) {
    echo "<div style='color:red;text-align:center;padding:50px;'>Tidak ditemukan!</div>";
    exit();
}

$pesan = ""; $tipe = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi'])) {
    if ($_POST['aksi'] == 'tambah') {
        // nomor pertemuan otomatis lanjut dari yang terakhir
        $no = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT MAX(pertemuan_ke) AS max_no FROM pertemuan WHERE mata_kuliah_id = $mk_id"
        ))['max_no'] ?? 0;
        $no_baru = $no + 1;
        $topik = trim($_POST['topik']);
        $tanggal = $_POST['tanggal'];

        $sql = "INSERT INTO pertemuan (mata_kuliah_id, pertemuan_ke, topik, tanggal, status)
                VALUES ($mk_id, $no_baru, '$topik', '$tanggal', 'aktif')";
        if (mysqli_query($conn, $sql)) {
            $pesan = "Pertemuan ke-$no_baru berhasil ditambahkan!";
            $tipe = "success";
        } else {
            $pesan = "Gagal menambahkan pertemuan!";
            $tipe = "error";
        }
    }

    if ($_POST['aksi'] == 'ubah_status') {
        $p_id = intval($_POST['pertemuan_id']);
        $status_baru = $_POST['status_baru'];
        mysqli_query($conn, "UPDATE pertemuan SET status = '$status_baru' WHERE id = $p_id AND mata_kuliah_id = $mk_id");
        $pesan = "Status pertemuan berhasil diubah!";
        $tipe = "success";
    }
}

$daftar = mysqli_query($conn, "SELECT * FROM pertemuan WHERE mata_kuliah_id = $mk_id ORDER BY pertemuan_ke");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pertemuan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo"><span>Absensi</span>Kampus</div>
    <div class="nav-links">
        <a href="dashboard_dosen.php">Dashboard</a>
        <a href="logout.php">Keluar</a>
    </div>
    <div class="user-info"><?= htmlspecialchars($_SESSION['nama']) ?></div>
</nav>

<div class="container">
    <div style="font-size:13px; color:#5a7ab0; margin-bottom:12px;">
        <a href="dashboard_dosen.php" style="color:#8aabff;">Dashboard</a> &rsaquo; Kelola Pertemuan
    </div>

    <div class="page-title">Kelola Pertemuan — <span><?= htmlspecialchars($mk['nama_mk']) ?></span></div>
    <div class="page-subtitle">Tambah pertemuan dan atur status absensi</div>

    <?php if ($pesan): ?>
        <div class="alert alert-<?= $tipe == 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Tambah Pertemuan Baru</div>
        </div>
        <form method="POST">
            <input type="hidden" name="aksi" value="tambah">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label>Topik Pertemuan</label>
                    <input type="text" name="topik" placeholder="Contoh: Pengantar PHP" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Pertemuan</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:auto; padding:10px 24px;">
                Tambah Pertemuan
            </button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Daftar Pertemuan</div>
        </div>

        <?php if (mysqli_num_rows($daftar) == 0): ?>
            <div class="alert alert-info">Belum ada pertemuan. Tambahkan pertemuan pertama di atas.</div>
        <?php else: ?>
            <?php while ($p = mysqli_fetch_assoc($daftar)): ?>
            <div class="pertemuan-item">
                <div class="pertemuan-info">
                    <div class="nomor">PERTEMUAN <?= $p['pertemuan_ke'] ?></div>
                    <div class="topik"><?= htmlspecialchars($p['topik'] ?? 'Tidak ada topik') ?></div>
                    <div class="tanggal"><?= date('d F Y', strtotime($p['tanggal'])) ?></div>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge badge-<?= $p['status'] ?>"><?= strtoupper($p['status']) ?></span>
                    <form method="POST">
                        <input type="hidden" name="aksi" value="ubah_status">
                        <input type="hidden" name="pertemuan_id" value="<?= $p['id'] ?>">
                        <?php if ($p['status'] == 'aktif'): ?>
                            <input type="hidden" name="status_baru" value="selesai">
                            <button type="submit" class="btn btn-danger btn-sm">Tutup Absensi</button>
                        <?php else: ?>
                            <input type="hidden" name="status_baru" value="aktif">
                            <button type="submit" class="btn btn-success btn-sm">Buka Absensi</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <a href="dashboard_dosen.php" class="btn btn-outline">Kembali ke Dashboard</a>
</div>

<div class="footer">Sistem Absensi Kampus 6</div>
</body>
</html>
