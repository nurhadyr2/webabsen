<?php
require_once 'config.php';
cekLogin();
cekDosen();

$dosen_id = $_SESSION['user_id'];
$pesan = ""; $tipe = "";

// tambah mata kuliah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'tambah_mk') {
    $kode = strtoupper(trim($_POST['kode_mk']));
    $nama = trim($_POST['nama_mk']);
    $sks = intval($_POST['sks']);
    $semester = trim($_POST['semester']);

    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM mata_kuliah WHERE kode_mk = '$kode'"));
    if ($cek) {
        $pesan = "Kode mata kuliah '$kode' sudah dipakai!";
        $tipe = "error";
    } else {
        $sql = "INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, dosen_id, semester)
                VALUES ('$kode', '$nama', $sks, $dosen_id, '$semester')";
        if (mysqli_query($conn, $sql)) {
            $pesan = "Mata kuliah '$nama' berhasil ditambahkan!";
            $tipe = "success";
        } else {
            $pesan = "Gagal menambahkan mata kuliah!";
            $tipe = "error";
        }
    }
}

// daftarkan mahasiswa ke matkul
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'tambah_mhs') {
    $mk_id_enroll = intval($_POST['mk_id']);
    $nim_target = trim($_POST['nim_mahasiswa']);

    $target = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT id FROM users WHERE nim_nip = '$nim_target' AND role = 'mahasiswa'"
    ));
    if (!$target) {
        $pesan = "Mahasiswa dengan NIM '$nim_target' tidak ditemukan!";
        $tipe = "error";
    } else {
        $cek_enroll = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT id FROM enrollment WHERE mahasiswa_id = {$target['id']} AND mata_kuliah_id = $mk_id_enroll"
        ));
        if ($cek_enroll) {
            $pesan = "Mahasiswa sudah terdaftar di mata kuliah ini!";
            $tipe = "error";
        } else {
            mysqli_query($conn, "INSERT INTO enrollment (mahasiswa_id, mata_kuliah_id) VALUES ({$target['id']}, $mk_id_enroll)");
            $pesan = "Mahasiswa berhasil didaftarkan ke mata kuliah!";
            $tipe = "success";
        }
    }
}

$daftar_mk = mysqli_fetch_all(mysqli_query($conn,
    "SELECT * FROM mata_kuliah WHERE dosen_id = $dosen_id ORDER BY nama_mk"
), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mata Kuliah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo"><span>Absensi</span>Kampus</div>
    <div class="nav-links">
        <a href="dashboard_dosen.php">Dashboard</a>
        <a href="tambah_matkul.php" class="aktif">Tambah Matkul</a>
        <a href="logout.php">Keluar</a>
    </div>
    <div class="user-info"><?= htmlspecialchars($_SESSION['nama']) ?></div>
</nav>

<div class="container">
    <div class="page-title">Kelola <span>Mata Kuliah</span></div>
    <div class="page-subtitle">Tambah mata kuliah baru dan daftarkan mahasiswa</div>

    <?php if ($pesan): ?>
        <div class="alert alert-<?= $tipe == 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">

        <div class="card">
            <div class="card-header">
                <div class="card-title">Tambah Mata Kuliah Baru</div>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah_mk">
                <div class="form-group">
                    <label>Kode Mata Kuliah</label>
                    <input type="text" name="kode_mk" placeholder="Contoh: IF201" required>
                </div>
                <div class="form-group">
                    <label>Nama Mata Kuliah</label>
                    <input type="text" name="nama_mk" placeholder="Contoh: Pemrograman Web" required>
                </div>
                <div class="form-group">
                    <label>Jumlah SKS</label>
                    <select name="sks">
                        <option value="1">1 SKS</option>
                        <option value="2">2 SKS</option>
                        <option value="3" selected>3 SKS</option>
                        <option value="4">4 SKS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <input type="text" name="semester" placeholder="Contoh: Ganjil 2024/2025" required>
                </div>
                <button type="submit" class="btn btn-primary">Tambah Mata Kuliah</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Daftarkan Mahasiswa</div>
            </div>
            <?php if (empty($daftar_mk)): ?>
                <div class="alert alert-info">Buat mata kuliah dulu sebelum mendaftarkan mahasiswa.</div>
            <?php else: ?>
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah_mhs">
                <div class="form-group">
                    <label>Pilih Mata Kuliah</label>
                    <select name="mk_id" required>
                        <?php foreach ($daftar_mk as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['kode_mk'] . ' - ' . $m['nama_mk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>NIM Mahasiswa</label>
                    <input type="text" name="nim_mahasiswa" placeholder="Masukkan NIM mahasiswa" required>
                </div>
                <button type="submit" class="btn btn-success">Daftarkan Mahasiswa</button>
            </form>
            <?php endif; ?>
        </div>

    </div>

    <a href="dashboard_dosen.php" class="btn btn-outline" style="margin-top:10px;">Kembali ke Dashboard</a>
</div>

<div class="footer">Sistem Absensi Kampus 2024</div>
</body>
</html>
