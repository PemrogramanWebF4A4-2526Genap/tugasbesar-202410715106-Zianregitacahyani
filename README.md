<div align="center">

# 💧 AquaGas

### Sistem Penjualan Air Galon & Gas LPG Berbasis Web

Aplikasi penjualan Air Galon Isi Ulang, Air Mineral (AQUA), dan Gas LPG 3KG berbasis PHP Native dengan tiga hak akses (Buyer, Seller, dan Administrator).

---

![PHP](https://img.shields.io/badge/PHP-Native-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML-5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS-3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-Development-FB7A24?style=for-the-badge)

---

**Tugas Besar Mata Kuliah Pemrograman Web**

Program Studi Informatika

Universitas Bhayangkara Jakarta Raya

</div>

---

# 📑 Daftar Isi

- [Tentang Project](#-tentang-project)
- [Fitur Utama](#-fitur-utama)
- [Hak Akses Sistem](#-hak-akses-sistem)
- [Tech Stack](#-tech-stack)
- [Struktur Folder](#-struktur-folder)
- [Struktur Database](#-struktur-database)
- [Instalasi](#-instalasi)
- [Panduan Penggunaan](#-panduan-penggunaan)
- [Dokumentasi Sistem](#-dokumentasi-sistem)
- [Testing Checklist](#-testing-checklist)
- [Troubleshooting](#-troubleshooting)
- [Future Development](#-future-development)
- [Author](#-author)
- [License](#-license)

---

# 📖 Tentang Project

AquaGas merupakan aplikasi penjualan berbasis website yang dirancang untuk mempermudah proses pemesanan Air Galon Isi Ulang, Air Mineral (AQUA), dan Gas LPG 3KG secara online.

Sistem ini dibangun menggunakan **PHP Native**, **MySQL**, **Bootstrap 5**, **HTML5**, **CSS3**, dan **JavaScript** dengan konsep **Multi User Role**, sehingga setiap pengguna memiliki hak akses sesuai kebutuhannya.

Melalui AquaGas, pelanggan dapat melakukan pemesanan produk secara online, memilih metode pembayaran, mengunggah bukti transfer, melihat status pesanan, hingga memberikan ulasan terhadap produk yang telah dibeli.

Di sisi lain, Seller dapat mengelola produk dan pesanan, sedangkan Administrator memiliki kontrol penuh terhadap seluruh sistem mulai dari pengelolaan produk, kategori, pengguna, monitoring transaksi, hingga laporan penjualan.

---

# ✨ Fitur Utama

## 🛍️ Manajemen Produk

- Menampilkan daftar produk
- Detail produk
- Kategori produk
- Pencarian produk
- Upload gambar produk
- Manajemen stok
- Harga produk

---

## 🛒 Sistem Pemesanan

- Keranjang Belanja
- Checkout
- Invoice
- Status Pesanan
- Riwayat Pembelian

---

## 💳 Pembayaran

- Cash On Delivery (COD)
- Transfer Bank
- Upload Bukti Transfer
- Validasi Pembayaran

---

## ⭐ Review Produk

- Rating Produk
- Ulasan Produk
- Riwayat Review

---

## 🔔 Notifikasi

- Update Status Pesanan
- Informasi Pembayaran
- Informasi Pesanan

---

## 📊 Dashboard

- Dashboard Buyer
- Dashboard Seller
- Dashboard Administrator

---

# 👥 Hak Akses Sistem

Project AquaGas memiliki **3 hak akses utama**, yaitu:

| Role             | Deskripsi                    |
| ---------------- | ---------------------------- |
| 👤 Buyer         | Melakukan pembelian produk   |
| 🛒 Seller        | Mengelola produk dan pesanan |
| 👨‍💼 Administrator | Mengelola seluruh sistem     |

---

# 🛠 Tech Stack

| Teknologi    | Digunakan Untuk  |
| ------------ | ---------------- |
| PHP Native   | Backend          |
| MySQL        | Database         |
| Bootstrap 5  | User Interface   |
| HTML5        | Struktur Halaman |
| CSS3         | Styling          |
| JavaScript   | Interaktivitas   |
| Font Awesome | Icon             |
| XAMPP        | Local Server     |

---

# 📂 Struktur Folder

```text
TugasBESARPemweb
│
├── admin/
│   ├── kategori/
│   ├── laporan/
│   ├── produk/
│   ├── pesanan/
│   ├── settings/
│   ├── user/
│   └── index.php
│
├── Penjual/
│   ├── index.php
│   ├── produk.php
│   ├── pesanan.php
│   └── ...
│
├── login/
│
├── css/
│
├── js/
│
├── image/
│
├── uploads/
│
├── bootstrap-5.3.8-dist/
│
├── koneksi.php
├── index.php
├── login.php
├── checkout.php
├── invoice.php
├── pesanan-saya.php
├── review.php
├── README.md
└── database.sql
```

---

# ⭐ Keunggulan Sistem

✅ Multi User Role

✅ Responsive Interface

✅ Upload Bukti Transfer

✅ Dashboard Statistik

✅ Monitoring Pesanan

✅ Report & Analytics

✅ Manajemen User

✅ Manajemen Produk

✅ Manajemen Kategori

✅ Invoice Otomatis

✅ Review Produk

✅ Notifikasi Pesanan

# 🗄 Struktur Database

AquaGas menggunakan database **MySQL** untuk menyimpan seluruh data transaksi, pengguna, produk, hingga laporan penjualan.

| Tabel         | Fungsi                                                         |
| ------------- | -------------------------------------------------------------- |
| users         | Menyimpan data seluruh pengguna (Buyer, Seller, Administrator) |
| categories    | Menyimpan kategori produk                                      |
| products      | Menyimpan informasi produk                                     |
| cart          | Menyimpan data keranjang belanja buyer                         |
| orders        | Menyimpan data transaksi                                       |
| order_items   | Menyimpan detail produk setiap transaksi                       |
| payments      | Menyimpan data pembayaran dan bukti transfer                   |
| reviews       | Menyimpan rating dan ulasan produk                             |
| notifications | Menyimpan notifikasi pengguna                                  |

---

## 🔗 Relasi Database

```text
Users
│
├── Cart
│
├── Orders
│      │
│      ├── Order Items
│      │          │
│      │          └── Products
│      │
│      └── Payments
│
└── Reviews
           │
           └── Products

Categories
      │
      └── Products
```

---

# 💻 Persyaratan Sistem

Sebelum menjalankan project, pastikan perangkat telah memenuhi kebutuhan berikut.

| Software  | Versi                   |
| --------- | ----------------------- |
| PHP       | 8.x                     |
| MySQL     | 8.x                     |
| Apache    | XAMPP                   |
| Bootstrap | 5                       |
| Browser   | Chrome / Edge / Firefox |

---

# ⚙ Instalasi

## 1️⃣ Clone Repository

```bash
git clone https://github.com/PemrogramanWebF4A4-2526Genap/tugasbesar-202410715106-Zianregitacahyani.git
```

atau download repository dalam bentuk ZIP.

---

## 2️⃣ Simpan Project

Pindahkan folder project ke dalam

```text
C:\xampp\htdocs\
```

Contoh

```text
C:\xampp\htdocs\TugasBESARPemweb
```

---

## 3️⃣ Jalankan XAMPP

Aktifkan

- Apache
- MySQL

Pastikan keduanya berjalan tanpa error.

---

## 4️⃣ Import Database

Buka

```text
http://localhost/phpmyadmin
```

Buat database

```text
aquagas
```

Kemudian import file

```text
database.sql
```

---

## 5️⃣ Jalankan Project

Buka browser

```text
http://localhost/TugasBESARPemweb
```

---

# 🔑 Hak Akses

Project memiliki tiga role pengguna.

## 👤 Buyer

Digunakan oleh pelanggan untuk melakukan pembelian produk.

### Hak Akses

- Login
- Register
- Melihat Produk
- Keranjang
- Checkout
- Upload Bukti Pembayaran
- Melihat Riwayat Pesanan
- Review Produk
- Print Invoice
- Melihat Notifikasi

---

## 🛒 Seller

Digunakan untuk mengelola produk dan pesanan.

### Hak Akses

- Dashboard
- Kelola Produk
- Tambah Produk
- Edit Produk
- Hapus Produk
- Melihat Pesanan
- Dashboard Penjualan

---

## 👨‍💼 Administrator

Administrator memiliki akses penuh terhadap sistem.

### Hak Akses

- Dashboard
- Kelola Produk
- Kelola Kategori
- Kelola User
- Monitoring Pesanan
- Report & Analytics
- System Settings

---

# 🚀 Alur Penggunaan Sistem

## Buyer

```text
Login
   │
   ▼
Home
   │
   ▼
Pilih Produk
   │
   ▼
Tambah Keranjang
   │
   ▼
Checkout
   │
   ▼
Isi Alamat
   │
   ▼
Pilih Pembayaran
   │
   ▼
Upload Bukti Transfer
   │
   ▼
Menunggu Verifikasi
   │
   ▼
Pesanan Diproses
   │
   ▼
Pesanan Dikirim
   │
   ▼
Pesanan Selesai
   │
   ▼
Review Produk
```

---

## Seller

```text
Login
   │
   ▼
Dashboard
   │
   ▼
Kelola Produk
   │
   ▼
Menerima Pesanan
   │
   ▼
Memproses Pesanan
   │
   ▼
Mengubah Status
```

---

## Administrator

```text
Login
   │
   ▼
Dashboard
   │
   ▼
Kelola Produk
   │
   ▼
Kelola User
   │
   ▼
Kelola Kategori
   │
   ▼
Monitoring Pesanan
   │
   ▼
Report & Analytics
   │
   ▼
System Settings
```

---

# 🔒 Keamanan Sistem

Beberapa mekanisme keamanan yang diterapkan pada AquaGas antara lain:

- Session Login
- Validasi Input Form
- Upload Bukti Pembayaran
- Validasi Data
- Hak Akses Berdasarkan Role
- Konfirmasi Pesanan
- Manajemen Status Pesanan
- Validasi Form Checkout

---

# 📈 Fitur Tambahan

Selain fitur utama, AquaGas juga menyediakan beberapa fitur pendukung seperti:

- Dashboard Statistik
- Produk Terlaris
- Riwayat Pesanan
- Detail Pesanan
- Invoice
- Review Produk
- Upload Bukti Transfer
- Monitoring Penjualan
- Report Analytics
- System Settings

# 📸 Dokumentasi Sistem

Berikut merupakan tampilan antarmuka (User Interface) dari aplikasi **AquaGas** berdasarkan hak akses pengguna.

---

# 👤 Buyer

Buyer merupakan pengguna yang melakukan pembelian produk Air Galon, Air Mineral, maupun Gas LPG melalui website AquaGas.

---

## 🔐 Login

Halaman login digunakan oleh pengguna yang telah memiliki akun untuk masuk ke dalam sistem menggunakan email dan password.

> Tambahkan screenshot

```text
assets/screenshots/login.png
```

---

## 📝 Register

Halaman registrasi digunakan oleh pengguna baru untuk membuat akun sebelum melakukan transaksi.

> Tambahkan screenshot

```text
assets/screenshots/register.png
```

---

## 🏠 Home

Halaman utama Buyer menampilkan informasi toko, produk unggulan, menu navigasi, serta shortcut menuju fitur utama.

> Tambahkan screenshot

```text
assets/screenshots/home.png
```

---

## 🛍 Daftar Produk

Buyer dapat melihat seluruh produk yang tersedia lengkap dengan gambar, kategori, stok, harga, serta tombol tambah ke keranjang.

> Tambahkan screenshot

```text
assets/screenshots/produk.png
```

---

## 🛒 Keranjang Belanja

Halaman ini digunakan untuk melihat seluruh produk yang telah dipilih sebelum melakukan checkout.

Fitur:

- Menambah jumlah produk
- Mengurangi jumlah produk
- Menghapus produk
- Melihat total belanja

> Tambahkan screenshot

```text
assets/screenshots/cart.png
```

---

## 💳 Checkout

Buyer mengisi alamat pengiriman, memilih metode pembayaran, kemudian melakukan checkout.

Fitur:

- Input alamat
- Pilih metode pembayaran
- Hitung total belanja
- Ongkos kirim

> Tambahkan screenshot

```text
assets/screenshots/checkout.png
```

---

## 💰 Konfirmasi Pembayaran

Buyer dapat mengunggah bukti transfer sebagai konfirmasi pembayaran.

Fitur:

- Upload Bukti Transfer
- Validasi File
- Status Pembayaran

> Tambahkan screenshot

```text
assets/screenshots/payment.png
```

---

## 📦 Riwayat Pesanan

Menampilkan seluruh transaksi yang pernah dilakukan oleh Buyer.

Informasi yang ditampilkan:

- Nomor Pesanan
- Status
- Total Pembayaran
- Metode Pembayaran
- Tanggal Transaksi

> Tambahkan screenshot

```text
assets/screenshots/orders.png
```

---

## 📄 Detail Pesanan

Buyer dapat melihat rincian produk yang dibeli.

Informasi:

- Produk
- Qty
- Harga
- Ongkir
- Total
- Status

> Tambahkan screenshot

```text
assets/screenshots/order-detail.png
```

---

## 🧾 Invoice

Invoice dapat dicetak sebagai bukti transaksi.

Informasi yang tersedia:

- Data Pembeli
- Produk
- Qty
- Harga
- Ongkir
- Total Pembayaran

> Tambahkan screenshot

```text
assets/screenshots/invoice.png
```

---

## ⭐ Review Produk

Buyer dapat memberikan rating serta ulasan terhadap produk yang telah diterima.

Fitur:

- Rating
- Komentar
- Riwayat Review

> Tambahkan screenshot

```text
assets/screenshots/review.png
```

---

## 🔔 Notifikasi

Halaman notifikasi menampilkan informasi perubahan status pesanan.

Contoh:

- Pesanan Diproses
- Pesanan Dikirim
- Pesanan Selesai

> Tambahkan screenshot

```text
assets/screenshots/notifikasi.png
```

---

# 🛒 Seller

Seller bertanggung jawab mengelola produk serta memproses pesanan pelanggan.

---

## 📊 Dashboard Seller

Dashboard menampilkan ringkasan aktivitas toko.

Informasi:

- Total Produk
- Total Pesanan
- Pendapatan
- Produk Terlaris
- Pesanan Terbaru

> Tambahkan screenshot

```text
assets/screenshots/seller-dashboard.png
```

---

## 📦 Kelola Produk

Seller dapat mengelola seluruh produk yang dijual.

Fitur:

- Tambah Produk
- Edit Produk
- Hapus Produk
- Update Stok

> Tambahkan screenshot

```text
assets/screenshots/seller-product.png
```

---

## 🚚 Manajemen Pesanan

Seller mengelola seluruh pesanan yang masuk.

Fitur:

- Konfirmasi Pesanan
- Ubah Status
- Melihat Bukti Pembayaran
- Detail Pesanan

> Tambahkan screenshot

```text
assets/screenshots/seller-order.png
```

---

## 📈 Dashboard Penjualan

Dashboard penjualan digunakan untuk memantau performa toko.

Informasi:

- Pendapatan
- Jumlah Pesanan
- Produk Terlaris
- Grafik Penjualan

> Tambahkan screenshot

```text
assets/screenshots/seller-report.png
```

---

# 👨‍💼 Administrator

Administrator memiliki hak akses penuh terhadap seluruh sistem.

---

## 📊 Dashboard Admin

Menampilkan seluruh informasi utama sistem.

> Tambahkan screenshot

```text
assets/screenshots/admin-dashboard.png
```

---

## 📦 Kelola Produk

Administrator dapat mengelola seluruh produk.

> Tambahkan screenshot

```text
assets/screenshots/admin-product.png
```

---

## ➕ Tambah Produk

Digunakan untuk menambahkan produk baru.

> Tambahkan screenshot

```text
assets/screenshots/admin-add-product.png
```

---

## 🗂 Kelola Kategori

Administrator mengelola kategori produk.

> Tambahkan screenshot

```text
assets/screenshots/admin-category.png
```

---

## ➕ Tambah Kategori

Digunakan untuk membuat kategori baru.

> Tambahkan screenshot

```text
assets/screenshots/admin-add-category.png
```

---

## 👥 Kelola User

Administrator mengelola seluruh akun pengguna.

> Tambahkan screenshot

```text
assets/screenshots/admin-user.png
```

---

## 📋 Monitoring Pesanan

Administrator dapat memonitor seluruh transaksi yang terjadi.

> Tambahkan screenshot

```text
assets/screenshots/admin-order.png
```

---

## 📊 Report & Analytics

Menampilkan laporan penjualan dan statistik sistem.

> Tambahkan screenshot

```text
assets/screenshots/admin-report.png
```

---

## ⚙️ System Settings

Administrator dapat mengubah informasi toko.

> Tambahkan screenshot

```text
assets/screenshots/admin-setting.png
```

---

# ✅ Testing Checklist

Berikut merupakan daftar pengujian fitur utama yang telah dilakukan pada sistem AquaGas.

## 👤 Buyer

| Fitur                       | Status      |
| --------------------------- | ----------- |
| Login                       | ✅ Berhasil |
| Register                    | ✅ Berhasil |
| Melihat Produk              | ✅ Berhasil |
| Pencarian Produk            | ✅ Berhasil |
| Tambah ke Keranjang         | ✅ Berhasil |
| Update Jumlah Produk        | ✅ Berhasil |
| Hapus Produk dari Keranjang | ✅ Berhasil |
| Checkout                    | ✅ Berhasil |
| Upload Bukti Pembayaran     | ✅ Berhasil |
| Riwayat Pesanan             | ✅ Berhasil |
| Detail Pesanan              | ✅ Berhasil |
| Invoice                     | ✅ Berhasil |
| Review Produk               | ✅ Berhasil |
| Notifikasi                  | ✅ Berhasil |

---

## 🛒 Seller

| Fitur               | Status      |
| ------------------- | ----------- |
| Login               | ✅ Berhasil |
| Dashboard           | ✅ Berhasil |
| Tambah Produk       | ✅ Berhasil |
| Edit Produk         | ✅ Berhasil |
| Hapus Produk        | ✅ Berhasil |
| Manajemen Pesanan   | ✅ Berhasil |
| Dashboard Penjualan | ✅ Berhasil |

---

## 👨‍💼 Administrator

| Fitur              | Status      |
| ------------------ | ----------- |
| Dashboard          | ✅ Berhasil |
| Kelola Produk      | ✅ Berhasil |
| Kelola Kategori    | ✅ Berhasil |
| Kelola User        | ✅ Berhasil |
| Monitoring Pesanan | ✅ Berhasil |
| Report & Analytics | ✅ Berhasil |
| System Settings    | ✅ Berhasil |

---

# 📌 Keunggulan Project

✔ Multi User Role

✔ Responsive Interface

✔ Dashboard Statistik

✔ Upload Bukti Transfer

✔ Review Produk

✔ Invoice Otomatis

✔ Monitoring Pesanan

✔ Report Analytics

✔ CRUD Produk

✔ CRUD Kategori

✔ CRUD User

✔ Manajemen Pesanan

✔ Sistem Notifikasi

---

# 🐞 Troubleshooting

## Database Tidak Terhubung

Pastikan:

- Apache aktif
- MySQL aktif
- Database **aquagas** telah dibuat
- File `koneksi.php` telah dikonfigurasi dengan benar

---

## Halaman Tidak Bisa Dibuka

Pastikan project berada pada folder

```text
htdocs/
```

kemudian akses

```text
http://localhost/TugasBESARPemweb
```

---

## Gambar Produk Tidak Muncul

Periksa folder

```text
uploads/
```

Pastikan file gambar telah berhasil diunggah.

---

## Upload Bukti Transfer Gagal

Pastikan:

- Format file sesuai
- Ukuran file tidak melebihi batas yang ditentukan
- Folder upload memiliki permission yang benar

---

## Login Gagal

Pastikan:

- Email telah terdaftar
- Password benar
- Session browser tidak bermasalah

---

# 🚀 Future Development

Project AquaGas masih dapat dikembangkan dengan berbagai fitur tambahan seperti:

- Pembayaran menggunakan Payment Gateway (Midtrans / Xendit)
- Integrasi API RajaOngkir untuk perhitungan ongkos kirim otomatis
- Fitur Live Chat antara Buyer dan Seller
- Sistem Voucher dan Promo
- Wishlist Produk
- Tracking Pengiriman
- Dashboard Statistik yang lebih interaktif
- Export Laporan ke PDF dan Excel
- Email Notification
- Push Notification
- Dark Mode
- Progressive Web App (PWA)
- Mobile Application (Android & iOS)

---

# 📊 Project Summary

| Keterangan         | Informasi                            |
| ------------------ | ------------------------------------ |
| Nama Project       | AquaGas                              |
| Jenis Project      | E-Commerce                           |
| Platform           | Website                              |
| Bahasa Pemrograman | PHP Native                           |
| Database           | MySQL                                |
| Frontend           | HTML5, CSS3, Bootstrap 5, JavaScript |
| Server             | Apache (XAMPP)                       |
| Role Pengguna      | Buyer, Seller, Administrator         |

---

# 👨‍💻 Developer

<div align="center">

## Zian Regita Cahyani

**NPM**

202410715106

**Program Studi**

Informatika

**Universitas**

Universitas Bhayangkara Jakarta Raya

---

Terima kasih telah menggunakan **AquaGas**.

Semoga project ini dapat menjadi solusi sederhana dalam proses penjualan Air Galon Isi Ulang, Air Mineral, dan Gas LPG berbasis web.

</div>

---

# 📜 License

Project ini dibuat sebagai **Tugas Besar Mata Kuliah Pemrograman Web** Universitas Bhayangkara Jakarta Raya.

Project ini diperuntukkan sebagai media pembelajaran dan pengembangan kemampuan dalam membangun aplikasi berbasis web menggunakan **PHP Native** dan **MySQL**.

Penggunaan project ini untuk tujuan akademik diperbolehkan dengan tetap mencantumkan identitas pengembang.

---

<div align="center">

Made with ❤️ by **Zian Regita Cahyani**

© 2026 AquaGas. All Rights Reserved.

Made with ❤️ by **Zian Regita Cahyani**

© 2026 AquaGas. All Rights Reserved.

</div>
