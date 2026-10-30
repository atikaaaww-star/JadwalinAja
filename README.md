# 📚 JadwalinAja

> **Atur tugas, tentukan prioritas, bagi waktu.**

JadwalinAja adalah aplikasi berbasis website yang membantu pelajar mengelola tugas sekolah, menentukan tugas yang perlu diprioritaskan, dan menyusun jadwal belajar dengan lebih teratur.

## 🎯 Latar Belakang

Pelajar sering menghadapi beberapa kendala saat mengatur tugas dan waktu belajar, di antaranya:

- ⏰ **Lupa tenggat waktu tugas** — Deadline dari berbagai mata pelajaran sering tersebar dan tidak tercatat dalam satu tempat.
- 📌 **Kesulitan menentukan prioritas** — Pelajar terkadang bingung menentukan tugas mana yang harus dikerjakan terlebih dahulu.
- 🗓️ **Bingung membagi waktu belajar** — Pelajar belum memiliki gambaran kapan dan berapa lama waktu yang sebaiknya digunakan untuk mengerjakan setiap tugas.

### 💡 Solusi yang Ditawarkan

JadwalinAja hadir sebagai solusi untuk membantu pelajar mengatur aktivitas akademik melalui tiga kebutuhan utama:

1. 📚 **Pengelolaan mata pelajaran dan tugas** — Mencatat mata pelajaran serta tugas dalam satu aplikasi.
2. 🚩 **Penentuan prioritas tugas** — Membantu melihat tugas yang perlu diselesaikan lebih dahulu melalui dashboard.
3. 🗓️ **Penyusunan jadwal belajar** — Membantu menyusun jadwal belajar otomatis berdasarkan waktu luang mingguan.

### 👥 Target Pengguna

Pelajar yang memiliki banyak tugas dari berbagai mata pelajaran dan ingin mengatur kegiatan belajar dengan lebih terencana.

---

## 🛠️ Teknologi yang Digunakan

Berikut teknologi dan perangkat yang digunakan dalam pengembangan JadwalinAja.

| Teknologi | Kegunaan |
|---|---|
| 🌐 HTML | Menyusun struktur halaman website. |
| 🎨 CSS | Mengatur tampilan dan tata letak website. |
| ⚙️ JavaScript | Menambahkan interaksi pada halaman website. |
| 🐘 PHP | Mengelola proses dan logika aplikasi di sisi server. |
| 🗄️ MySQL | Menyimpan data aplikasi. |

### 📦 Pustaka dan Font

- **Pustaka/modul eksternal:** Tidak menggunakan pustaka atau modul eksternal. Fitur aplikasi dibuat menggunakan kemampuan bawaan PHP dan JavaScript.
- **Font:** Menggunakan Poppins dan Inter, dengan font sistem sebagai alternatif apabila font utama tidak tersedia.

### 🔐 Keamanan Aplikasi

Beberapa mekanisme yang diterapkan untuk membantu menjaga keamanan data pengguna:

- Verifikasi akun pengguna melalui halaman login.
- Password disimpan menggunakan metode **MD5 Hashing**.
- Pengecekan kepemilikan data untuk memastikan pengguna hanya dapat mengakses data miliknya sendiri.

> ⚠️ **Catatan keamanan:** MD5 merupakan metode hashing lama dan tidak direkomendasikan untuk penyimpanan password pada aplikasi produksi. Untuk pengembangan selanjutnya, gunakan `password_hash()` dan `password_verify()` dari PHP.

### 💻 Lingkungan Pengembangan

- **XAMPP** — Menyediakan Apache, PHP, dan MySQL untuk menjalankan aplikasi secara lokal.
- **phpMyAdmin** — Membantu mengelola database MySQL melalui antarmuka web.

### 🚀 Platform Publikasi

Aplikasi dipublikasikan menggunakan layanan hosting dari **Jagoan Hosting** dan domain milik sekolah:

🌐 [smkn1ampelgading.sch.id](https://smkn1ampelgading.sch.id)

---

## 🤖 Pemanfaatan AI (AI Usage Disclosure)

Dalam proses pengembangan JadwalinAja, AI digunakan sebagai alat bantu untuk memberikan ide, menyusun kode, dan membantu proses perbaikan aplikasi.

### 🧠 Alat AI yang Digunakan

- **Claude (Anthropic)** melalui [claude.ai](https://claude.ai).

### ✨ Peran AI dalam Pengembangan

AI membantu proses pengembangan dalam beberapa hal berikut:

- 💡 Memberikan ide dan rancangan solusi untuk aplikasi.
- 🎨 Memberikan alternatif nama dan konsep logo, serta membantu membuat file logo dalam format SVG.
- 🧩 Menyusun kerangka program, mulai dari struktur folder, skema database, hingga kode PHP, CSS, dan JavaScript.
- 📝 Menambahkan komentar pada kode agar lebih mudah dipahami.
- 🐞 Membantu menganalisis dan memperbaiki kesalahan yang ditemukan setelah aplikasi diuji dan dipublikasikan (*deploy*).

### 💬 Contoh Prompt Utama

Berikut salah satu perintah yang digunakan saat meminta bantuan AI dalam merancang aplikasi:

> Saya ditantang untuk membuat aplikasi berbasis website yang dapat menyelesaikan masalah sehari-hari. Teknologi yang bisa saya gunakan adalah HTML, CSS, JavaScript, PHP, dan MySQL. Saya sering lupa tenggat waktu tugas, kesulitan menentukan prioritas, dan bingung membagi waktu belajar. Berikan saran solusi yang bisa saya terapkan melalui pembuatan aplikasi.

---

## 🔧 Penyesuaian dan Perbaikan Mandiri

Saran dan kode yang diberikan AI tidak langsung digunakan begitu saja. Setiap hasil dievaluasi dan disesuaikan kembali dengan kebutuhan aplikasi.

Berikut beberapa penyesuaian yang dilakukan selama proses pengembangan:

1. 🏷️ **Memilih nama aplikasi sendiri**  
   Dari berbagai alternatif nama yang diberikan AI, nama **JadwalinAja** dipilih berdasarkan pertimbangan sendiri. Beberapa alternatif lain yang sempat diberikan AI adalah PlanDone, TugasTertata, dan StudyFlow.

2. 🎨 **Mengembangkan desain logo**  
   Desain logo pertama dinilai terlalu sederhana. Oleh karena itu, dilakukan pengembangan dengan menambahkan grid tanggal berwarna, badge centang, dan tagline agar tampilannya lebih menarik.

3. 📖 **Menyesuaikan istilah dengan target pengguna**  
   AI awalnya menggunakan istilah "Mata Kuliah", padahal aplikasi ditujukan untuk pelajar. Setelah ditemukan ketidaksesuaian tersebut, istilahnya diganti menjadi "Mata Pelajaran" pada navbar, halaman, form, pesan, dan dokumentasi.

4. 🌏 **Memperbaiki masalah zona waktu**  
   Setelah aplikasi di-*deploy*, hitung mundur (*countdown*) deadline tidak sesuai dengan waktu yang dimasukkan. Hal ini terjadi karena zona waktu server berbeda dengan zona waktu lokal. Perbaikan dilakukan dengan mengatur zona waktu PHP (`date_default_timezone_set`) dan zona waktu sesi MySQL (`SET time_zone`) ke WIB.

5. 📚 **Mempelajari dan memahami kode program**  
   Komentar ditambahkan pada kode agar bagian styling maupun logika program lebih mudah dipahami. Hal ini dilakukan supaya pengembang dapat mempelajari dan menjelaskan kode yang digunakan.

---

## 📖 Panduan Penggunaan

### 🙋 A. Untuk Pengguna

Ikuti langkah-langkah berikut untuk menggunakan aplikasi JadwalinAja:

1. 🌐 **Buka website**  
   Kunjungi tautan aplikasi, kemudian klik tombol **Daftar**.

2. 📝 **Buat akun**  
   Isi nama, email, dan password dengan minimal 8 karakter.

3. 🔑 **Login ke aplikasi**  
   Masuk menggunakan email dan password yang telah didaftarkan.

4. 📚 **Tambahkan mata pelajaran**  
   Buka menu **Mata Pelajaran**, lalu tambahkan mata pelajaran beserta warna penandanya.

5. ➕ **Tambahkan tugas baru**  
   Buka menu **Tugas**, klik **+ Tugas Baru**, kemudian isi informasi berikut:
   - Judul tugas
   - Mata pelajaran
   - Deadline
   - Tingkat kesulitan
   - Pengaruh terhadap nilai akhir
   - Estimasi waktu pengerjaan

6. 📊 **Periksa dashboard**  
   Lihat daftar tugas berdasarkan prioritas. Tugas dengan prioritas tertinggi ditampilkan di bagian atas, dilengkapi warna deadline dan hitung mundur.

7. 🗓️ **Atur waktu luang**  
   Buka menu **Jadwal**, isi bagian **Waktu Luang Mingguan** sesuai jam belajar yang tersedia setiap hari, lalu klik **Simpan Waktu Luang**.

8. ⚡ **Buat jadwal otomatis**  
   Klik **Buat / Perbarui Jadwal Otomatis**. Sistem akan menampilkan jadwal belajar untuk 14 hari ke depan.

9. ✅ **Perbarui progres belajar**  
   Centang sesi belajar yang telah dikerjakan. Ubah status tugas menjadi **Proses** atau **Selesai** melalui menu Tugas.

### 👨‍💻 B. Untuk Pengembang

Jika ingin menjalankan JadwalinAja di komputer sendiri, ikuti langkah berikut:

**Persiapan:**
- Instal XAMPP dengan PHP versi 8.0 atau lebih baru.
- Pastikan folder proyek JadwalinAja tersedia di komputer.

**Langkah instalasi:**

1. 📂 **Salin folder proyek**  
   Pindahkan folder `jadwalinaja` ke dalam direktori `htdocs` milik XAMPP.

2. ▶️ **Jalankan server lokal**  
   Buka XAMPP, kemudian aktifkan layanan **Apache** dan **MySQL**.

3. 🗄️ **Impor database**  
   Buka phpMyAdmin melalui browser, lalu impor file `database/schema.sql` untuk membuat struktur database aplikasi.

4. ⚙️ **Sesuaikan konfigurasi database**  
   Buka file `config/database.php`. Sesuaikan username dan password MySQL apabila berbeda dengan konfigurasi bawaan.

5. 🌏 **Atur zona waktu**  
   Pastikan konfigurasi `APP_TIMEZONE` dan `DB_TIMEZONE` sesuai dengan zona waktu yang digunakan:

   | Zona waktu | Pengaturan |
   |---|---|
   | WIB | `+07:00` |
   | WITA | `+08:00` |
   | WIT | `+09:00` |

6. 🌐 **Jalankan aplikasi**  
   Buka browser dan akses alamat berikut:

   ```text
   https://smkn1ampelgading.sch.id/jadwalin/
   ```

---

## 👨‍💻 Identitas Pengembang

JadwalinAja dikembangkan oleh:

| Nama | Kelas | Kompetensi Keahlian | Sekolah |
|---|---|---|---|
| Rafael Egi Wijaya | XI RPL 1 | Rekayasa Perangkat Lunak | SMKN 1 Ampelgading |
| Atika Nazua | XI RPL 1 | Rekayasa Perangkat Lunak | SMKN 1 Ampelgading |

---
