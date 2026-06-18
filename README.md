# 📅 Web Manajemen Event (CRUD + AJAX & Digital Signature)

Proyek web sederhana untuk mengelola data event atau acara. Web ini sudah pakai AJAX supaya proses tambah, edit, dan hapusnya lancar tanpa perlu refresh halaman. Ada juga fitur keren seperti tanda tangan langsung di layar (Canvas) dan efek suara klik setiap kali tombolnya ditekan.

---

## 🔥 Fitur Utama

* **🔒 Login System** – Akses halaman dikunci menggunakan session PHP biasa[cite: 2, 4].
* **📊 Dashboard Ringkas** – Langsung menampilkan info total event, mana event yang masih aktif, dan mana yang sudah selesai[cite: 2].
* **⚡ Full AJAX CRUD** – Tambah, edit, dan hapus data berjalan di latar belakang tanpa reload halaman[cite: 2].
* **✍️ Tanda Tangan Digital** – Bisa coret-coret tanda tangan di canvas, lalu hasilnya otomatis diubah jadi Base64 untuk disimpan ke database[cite: 2, 6, 7].
* **📁 Upload File** – Bisa melampirkan berkas pendukung. Kalau data event dihapus atau diganti, file lama di folder server otomatis ikut terhapus supaya tidak memenuhi penyimpanan[cite: 1, 2, 6].
* **🔊 Efek Suara Interaktif** – Pakai Web Audio API native, jadi ada suara "klik" instan setiap kali berinteraksi dengan tombol atau tabel[cite: 2].
* **🎨 Tampilan Modern** – Didukung Bootstrap 5, DataTables (bisa ekspor ke Excel/PDF), animasi AOS, dan notifikasi SweetAlert2[cite: 2].

---

## 🛠️ Stack & Library

* **Backend:** PHP Native & MySQL (menggunakan ekstensi `mysqli`)[cite: 2, 3]
* **Frontend:** Bootstrap 5.3.3 & jQuery 3.7.1[cite: 2]
* **Plugins:** DataTables, SweetAlert2, dan AOS (Animate On Scroll)[cite: 2]
* **Fitur Native:** Web Audio API (sound effect) & HTML5 Canvas (tanda tangan)[cite: 2]

---

## 📂 Struktur File

* `koneksi.php` – Konfigurasi koneksi ke database MySQL[cite: 3].
* `login.php` & `logout.php` – Halaman autentikasi masuk dan keluar sistem[cite: 4, 5].
* `index.php` – Halaman utama (dashboard, tabel data, modal CRUD, dan logic canvas)[cite: 2].
* `hapus.php` & `proses.php` – Proses backend untuk hapus file fisik dan data[cite: 1, 6].
* `script.js` – Logika interaksi canvas tanda tangan dan pengiriman form[cite: 7].
* `style.css` – Custom tampilan, animasi judul, dan layout modal[cite: 8].
* `uploads/` – Folder otomatis tempat menyimpan file lampiran yang di-upload[cite: 2, 6].

---

## 🚀 Cara Install di Lokal

### 1. Persiapan
Pastikan komputer kamu sudah terinstal web server lokal seperti **XAMPP**, **Laragon**, atau sejenisnya.

### 2. Download Project
Taruh folder project ini di dalam direktori server lokal kamu (misalnya di folder `htdocs` kalau kamu pakai XAMPP).

### 3. Setup Database
1. Nyalakan module **Apache** dan **MySQL** di XAMPP Control Panel.
2. Buka browser, masuk ke `http://localhost/phpmyadmin/`, lalu buat database baru dengan nama `manajemen_event`.
3. Buat struktur tabelnya dan pastikan cocok dengan konfigurasi di file `koneksi.php`:
```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "manajemen_event";
