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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mahasiswa_id = intval($_POST['mahasiswa_id']);
    $uts = floatval($_POST['nilai_uts']);
    $uas = floatval($_POST['nilai_uas']);
    $tugas = floatval($_POST['nilai_tugas']);

    // nilai akhir: 30% UTS + 40% UAS + 30% tugas
    $akhir = ($uts * 0.30) + ($uas * 0.40) + ($tugas * 0.30);

    if     ($akhir >= 85) $grade = 'A';
    elseif ($akhir >= 80) $grade = 'A-';
    elseif ($akhir >= 75) $grade = 'B+';
    elseif ($akhir >= 70) $grade = 'B';
    elseif ($akhir >= 65) $grade = 'B-';
    elseif ($akhir >= 60) $grade = 'C+';
    elseif ($akhir >= 55) $grade = 'C';
    elseif ($akhir >= 40) $grade = 'D';
    else                  $grade = 'E';

    // kalau sudah ada nilainya, diupdate
    $sql = "INSERT INTO nilai (mahasiswa_id, mata_kuliah_id, nilai_uts, nilai_uas, nilai_tugas, nilai_akhir, grade)
            VALUES ($mahasiswa_id, $mk_id, $uts, $uas, $tugas, $akhir, '$grade')
            ON DUPLICATE KEY UPDATE
                nilai_uts = $uts,
                nilai_uas = $uas,
                nilai_tugas = $tugas,
                nilai_akhir = $akhir,
                grade = '$grade'";

    if (mysqli_query($conn, $sql)) {
        $pesan = "Nilai berhasil disimpan! Nilai akhir: $akhir | Grade: $grade";
        $tipe = "success";
    } else {
        $pesan = "Gagal menyimpan nilai!";
        $tipe = "error";
    }
}

$sql_mhs = "
    SELECT u.id, u.nim_nip, u.nama,
           n.nilai_uts, n.nilai_uas, n.nilai_tugas, n.nilai_akhir, n.grade
    FROM enrollment e
    JOIN users u ON e.mahasiswa_id = u.id
    LEFT JOIN nilai n ON n.mahasiswa_id = u.id AND n.mata_kuliah_id = $mk_id
    WHERE e.mata_kuliah_id = $mk_id
    ORDER BY u.nama
";
$daftar = mysqli_query($conn, $sql_mhs);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai - <?= htmlspecialchars($mk['nama_mk']) ?></title>
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
        <a href="dashboard_dosen.php" style="color:#8aabff;">Dashboard</a> &rsaquo; Input Nilai
    </div>

    <div class="page-title">Input Nilai — <span><?= htmlspecialchars($mk['nama_mk']) ?></span></div>
    <div class="page-subtitle">Kode: <?= $mk['kode_mk'] ?> | Formula: 30% UTS + 40% UAS + 30% Tugas</div>

    <?php if ($pesan): ?>
        <div class="alert alert-<?= $tipe == 'success' ? 'success' : 'error' ?>"><?= $pesan ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Daftar Nilai Mahasiswa</div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>UTS</th>
                        <th>UAS</th>
                        <th>Tugas</th>
                        <th>Nilai Akhir</th>
                        <th>Grade</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; while ($mhs = mysqli_fetch_assoc($daftar)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td style="color:#8aabff; font-weight:600"><?= htmlspecialchars($mhs['nim_nip']) ?></td>
                        <td><?= htmlspecialchars($mhs['nama']) ?></td>
                        <td><?= $mhs['nilai_uts']   ?? '<span style="color:#5a7ab0">-</span>' ?></td>
                        <td><?= $mhs['nilai_uas']   ?? '<span style="color:#5a7ab0">-</span>' ?></td>
                        <td><?= $mhs['nilai_tugas'] ?? '<span style="color:#5a7ab0">-</span>' ?></td>
                        <td><?= $mhs['nilai_akhir'] 
                            ? '<strong style="color:#2971FF">' . number_format($mhs['nilai_akhir'],2) . '</strong>'
                            : '<span style="color:#5a7ab0">-</span>' ?>
                        </td>
                        <td>
                            <?php if ($mhs['grade']): ?>
                                <span class="badge" style="background:rgba(41,113,255,0.2);color:#2971FF;border:1px solid #2971FF;">
                                    <?= $mhs['grade'] ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#5a7ab0">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-outline btn-sm" 
                                onclick="isiForm(<?= $mhs['id'] ?>, '<?= htmlspecialchars($mhs['nama']) ?>', <?= $mhs['nilai_uts'] ?? 0 ?>, <?= $mhs['nilai_uas'] ?? 0 ?>, <?= $mhs['nilai_tugas'] ?? 0 ?>)">
                                Input
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" id="form-nilai" style="display:none;">
        <div class="card-header">
            <div class="card-title">Input Nilai: <span id="nama-mhs" style="color:#2971FF"></span></div>
        </div>

        <form method="POST">
            <input type="hidden" name="mahasiswa_id" id="input-mhs-id">

            <div style="display:grid; grid-template-columns: repeat(3,1fr); gap:16px;">
                <div class="form-group">
                    <label>Nilai UTS (0-100)</label>
                    <input type="number" name="nilai_uts" id="input-uts" min="0" max="100" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Nilai UAS (0-100)</label>
                    <input type="number" name="nilai_uas" id="input-uas" min="0" max="100" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Nilai Tugas (0-100)</label>
                    <input type="number" name="nilai_tugas" id="input-tugas" min="0" max="100" step="0.01" required>
                </div>
            </div>

            <div style="font-size:12px; color:#8aabff; margin-bottom:16px;">
                Nilai akhir = (UTS × 30%) + (UAS × 40%) + (Tugas × 30%)
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:auto; padding:10px 24px;">Simpan Nilai</button>
                <button type="button" class="btn btn-outline" onclick="tutupForm()">Batal</button>
            </div>
        </form>
    </div>

    <a href="dashboard_dosen.php" class="btn btn-outline" style="margin-top:10px;">Kembali</a>
</div>

<div class="footer">Sistem Absensi Kampus 2024</div>

<script>
function isiForm(id, nama, uts, uas, tugas) {
    document.getElementById('form-nilai').style.display = 'block';
    document.getElementById('nama-mhs').textContent = nama;
    document.getElementById('input-mhs-id').value = id;
    document.getElementById('input-uts').value = uts;
    document.getElementById('input-uas').value = uas;
    document.getElementById('input-tugas').value = tugas;
    document.getElementById('form-nilai').scrollIntoView({ behavior: 'smooth' });
}

function tutupForm() {
    document.getElementById('form-nilai').style.display = 'none';
}
</script>
</body>
</html>
