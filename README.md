# Sistem Absensi Mahasiswa

Aplikasi absensi berbasis web menggunakan PHP, MySQL, dan CSS biasa (tanpa framework).

## Struktur File

```
absensi/
  database.sql            import ke MySQL/phpMyAdmin
  config.php              koneksi database
  style.css               tampilan
  login.php               halaman login
  logout.php              logout

  dashboard_mahasiswa.php dashboard mahasiswa
  absensi.php             input absensi per matkul
  riwayat_absensi.php     riwayat absensi mahasiswa

  dashboard_dosen.php     dashboard dosen
  tambah_matkul.php       tambah matkul + daftar mahasiswa
  tambah_pertemuan.php    tambah & kelola pertemuan
  kelola_absensi.php      rekap kehadiran semua mahasiswa
  input_nilai.php         input nilai UTS/UAS/Tugas
```

## Cara Install

1. Install XAMPP (atau WAMP/Laragon), lalu jalankan Apache dan MySQL.
2. Copy folder `absensi` ke `C:\xampp\htdocs\absensi\`.
3. Buka http://localhost/phpmyadmin, buat database `absensi_db`, lalu import `database.sql`.
4. Sesuaikan username/password MySQL di `config.php` bila perlu:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "absensi_db";
```

5. Buka http://localhost/absensi/login.php.

## Fitur

Mahasiswa:

- Login dengan NIM
- Dashboard rekap kehadiran
- Daftar mata kuliah yang diambil
- Absensi tiap pertemuan (hadir/izin/sakit)
- Riwayat absensi

Dosen:

- Login dengan NIP
- Tambah mata kuliah dan daftarkan mahasiswa
- Tambah pertemuan serta buka/tutup absensi
- Rekap kehadiran semua mahasiswa
- Input nilai UTS, UAS, Tugas (nilai akhir dan grade dihitung otomatis)
