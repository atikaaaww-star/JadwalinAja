-- =====================================================================
-- JadwalinAja - SKEMA DATABASE
-- File ini dijalankan SEKALI (impor lewat phpMyAdmin) untuk membuat
-- database beserta seluruh tabelnya.
-- Relasi: users 1---N subjects, tasks, availability, study_slots
--         subjects 1---N tasks      tasks 1---N study_slots
-- =====================================================================

-- Buat database baru (jika belum ada) dengan dukungan huruf/emoji lengkap (utf8mb4)
CREATE DATABASE IF NOT EXISTS jadwalinaja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jadwalinaja;   -- semua tabel di bawah dibuat di database ini


-- ---------------------------------------------------------------------
-- TABEL users : menyimpan akun pengguna
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,   -- ID unik, naik otomatis
  nama VARCHAR(100) NOT NULL,                   -- nama tampilan
  email VARCHAR(150) NOT NULL UNIQUE,           -- UNIQUE: satu email hanya boleh satu akun
  password VARCHAR(255) NOT NULL,               -- berisi HASH (password_hash), bukan password asli
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- waktu akun dibuat (otomatis)
);


-- ---------------------------------------------------------------------
-- TABEL subjects : daftar mata pelajaran milik tiap pengguna
-- ---------------------------------------------------------------------
CREATE TABLE subjects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,                -- pemilik mata pelajaran
  nama VARCHAR(100) NOT NULL,                   -- contoh: "Matematika"
  warna CHAR(7) NOT NULL DEFAULT '#2563EB',     -- kode warna hex untuk penanda (titik berwarna)
  -- ON DELETE CASCADE: jika user dihapus, mata pelajarannya ikut terhapus
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- ---------------------------------------------------------------------
-- TABEL tasks : data tugas (inti aplikasi)
-- ---------------------------------------------------------------------
CREATE TABLE tasks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,                -- pemilik tugas
  subject_id INT UNSIGNED NULL,                 -- mata pelajaran (boleh kosong)
  judul VARCHAR(200) NOT NULL,                  -- judul tugas
  deskripsi TEXT NULL,                          -- catatan tambahan (opsional)
  deadline DATETIME NOT NULL,                   -- batas waktu pengumpulan (tanggal + jam)
  kesulitan TINYINT NOT NULL DEFAULT 3,         -- tingkat kesulitan 1..5 (dipakai di skor)
  bobot_nilai TINYINT NOT NULL DEFAULT 3,       -- pengaruh ke nilai akhir 1..5 (dipakai di skor)
  estimasi_jam DECIMAL(4,1) NOT NULL DEFAULT 1.0, -- perkiraan lama pengerjaan (jam), dipakai penyusun jadwal
  status ENUM('belum','proses','selesai') NOT NULL DEFAULT 'belum', -- hanya 3 nilai ini yang valid
  skor_prioritas DECIMAL(5,2) NOT NULL DEFAULT 0, -- hasil hitungan 0..100, makin besar makin penting
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  -- ON DELETE SET NULL: jika mata pelajaran dihapus, tugas TETAP ada (subject_id jadi kosong)
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
  -- Index mempercepat query "tugas milik user X dengan status Y, urut deadline"
  INDEX idx_user_status_deadline (user_id, status, deadline)
);


-- ---------------------------------------------------------------------
-- TABEL availability : waktu luang belajar mingguan (input pengguna)
-- Contoh baris: hari=1 (Senin), 19:00 - 21:00
-- ---------------------------------------------------------------------
CREATE TABLE availability (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  hari TINYINT NOT NULL,                        -- 1=Senin ... 7=Minggu (sesuai date('N') di PHP)
  jam_mulai TIME NOT NULL,                      -- jam mulai waktu luang
  jam_selesai TIME NOT NULL,                    -- jam selesai waktu luang
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- ---------------------------------------------------------------------
-- TABEL study_slots : HASIL penyusunan jadwal otomatis
-- Satu baris = satu sesi belajar untuk satu tugas pada tanggal & jam tertentu
-- ---------------------------------------------------------------------
CREATE TABLE study_slots (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  task_id INT UNSIGNED NOT NULL,                -- tugas yang dikerjakan pada sesi ini
  tanggal DATE NOT NULL,                        -- tanggal sesi
  jam_mulai TIME NOT NULL,
  jam_selesai TIME NOT NULL,
  selesai TINYINT(1) NOT NULL DEFAULT 0,        -- 0 = belum dikerjakan, 1 = sudah (dicentang)
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE, -- tugas dihapus -> sesinya ikut hilang
  INDEX idx_user_tanggal (user_id, tanggal)     -- mempercepat pencarian jadwal per tanggal
);
