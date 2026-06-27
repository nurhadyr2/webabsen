CREATE DATABASE IF NOT EXISTS absensi_db;
USE absensi_db;

-- Tabel users (mahasiswa & dosen)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim_nip VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('mahasiswa', 'dosen') DEFAULT 'mahasiswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel mata kuliah
CREATE TABLE mata_kuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_mk VARCHAR(10) UNIQUE NOT NULL,
    nama_mk VARCHAR(100) NOT NULL,
    sks INT DEFAULT 2,
    dosen_id INT,
    semester VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dosen_id) REFERENCES users(id)
);

-- Tabel pertemuan
CREATE TABLE pertemuan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT NOT NULL,
    pertemuan_ke INT NOT NULL,
    topik VARCHAR(200),
    tanggal DATE NOT NULL,
    status ENUM('aktif', 'selesai') DEFAULT 'aktif',
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id)
);

-- Tabel enrollment
CREATE TABLE enrollment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    mata_kuliah_id INT NOT NULL,
    FOREIGN KEY (mahasiswa_id) REFERENCES users(id),
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id)
);

-- Tabel absensi
CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    pertemuan_id INT NOT NULL,
    status_hadir ENUM('hadir', 'izin', 'sakit', 'alpha') DEFAULT 'hadir',
    waktu_absen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES users(id),
    FOREIGN KEY (pertemuan_id) REFERENCES pertemuan(id),
    UNIQUE KEY unik_absen (mahasiswa_id, pertemuan_id)
);

-- Tabel nilai
CREATE TABLE nilai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    mata_kuliah_id INT NOT NULL,
    nilai_uts DECIMAL(5,2),
    nilai_uas DECIMAL(5,2),
    nilai_tugas DECIMAL(5,2),
    nilai_akhir DECIMAL(5,2),
    grade CHAR(2),
    FOREIGN KEY (mahasiswa_id) REFERENCES users(id),
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id),
    UNIQUE KEY unik_nilai (mahasiswa_id, mata_kuliah_id)
);

-- Data contoh. Password semua user: password123
INSERT INTO users (nim_nip, nama, password, role) VALUES
('D001', 'Dr. Budi Santoso', '$2y$10$JCpU8iaDpdBQY0V7ndjxMOMVaQ4yNe02h.Wnnd0SHNdFbCeMwo0Zy', 'dosen'),
('D002', 'Dr. Sari Dewi', '$2y$10$JCpU8iaDpdBQY0V7ndjxMOMVaQ4yNe02h.Wnnd0SHNdFbCeMwo0Zy', 'dosen'),
('2021001', 'Ahmad Rizki', '$2y$10$JCpU8iaDpdBQY0V7ndjxMOMVaQ4yNe02h.Wnnd0SHNdFbCeMwo0Zy', 'mahasiswa'),
('2021002', 'Siti Nurhaliza', '$2y$10$JCpU8iaDpdBQY0V7ndjxMOMVaQ4yNe02h.Wnnd0SHNdFbCeMwo0Zy', 'mahasiswa'),
('2021003', 'Bima Prasetyo', '$2y$10$JCpU8iaDpdBQY0V7ndjxMOMVaQ4yNe02h.Wnnd0SHNdFbCeMwo0Zy', 'mahasiswa');

INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, dosen_id, semester) VALUES
('IF101', 'Metereology', 3, 1, 'Ganjil 2025/2026'),
('IF102', 'Rules and Relugation', 3, 1, 'Ganjil 2025/2026'),
('IF103', 'Air Law', 2, 2, 'Ganjil 2025/2026');

INSERT INTO pertemuan (mata_kuliah_id, pertemuan_ke, topik, tanggal) VALUES
(1, 1, 'Pengenalan Algoritma', '2026-09-02'),
(1, 2, 'Tipe Data & Variabel', '2026-09-09'),
(1, 3, 'Percabangan IF-ELSE', '2026-09-16'),
(2, 1, 'Pengenalan Basis Data', '2026-09-03'),
(2, 2, 'Entity Relationship Diagram', '2026-09-10'),
(3, 1, 'Pengenalan Jaringan', '2026-09-04');

INSERT INTO enrollment (mahasiswa_id, mata_kuliah_id) VALUES
(3, 1), (3, 2), (3, 3),
(4, 1), (4, 2),
(5, 1), (5, 3);

INSERT INTO absensi (mahasiswa_id, pertemuan_id, status_hadir) VALUES
(3, 1, 'hadir'), (3, 2, 'hadir'), (3, 4, 'hadir'),
(4, 1, 'izin'), (4, 4, 'hadir'),
(5, 1, 'hadir');

INSERT INTO nilai (mahasiswa_id, mata_kuliah_id, nilai_uts, nilai_uas, nilai_tugas, nilai_akhir, grade) VALUES
(3, 2, 80.00, 85.00, 90.00, 85.00, 'A'),
(4, 2, 70.00, 75.00, 80.00, 75.00, 'B+');
