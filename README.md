# DigiResto – Sistem Pemesanan Restoran (PHP Native OOP + Bootstrap 5)

## Instalasi (XAMPP/Laragon)
1. Salin folder `restoar` ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Buka phpMyAdmin → Import `database.sql` (membuat database `DigitalRestaurant` + data contoh).
3. Sesuaikan kredensial DB di `config.php` bila perlu.
4. Buka `http://localhost/restoar/` → login: **admin / admin123**.

Butuh PHP 8.0+ (ekstensi `pdo_mysql` dan `mbstring`) dan MySQL/MariaDB. Bootstrap dimuat dari CDN (perlu internet).

## Struktur kode (OOP)
Class dimuat otomatis oleh autoloader di `config.php` dari folder `classes/` (nama file = nama class).

| Class | Peran |
|-------|-------|
| `Database` | Koneksi PDO (Singleton) dan pembungkus query dengan parameter binding |
| `BaseModel` | Class induk model: `find()` dan `delete()` diwarisi semua model |
| `User`, `Customer`, `DiningTable`, `Menu`, `Reservation`, `OrderItem` | Model tabel (`extends BaseModel`) beserta aturan bisnisnya |
| `Dashboard`, `Report` | Penyedia data halaman Ringkasan dan Laporan (termasuk ekspor CSV) |
| `Auth` | Login, logout, proteksi halaman, token CSRF |
| `Flash` | Pesan satu kali pakai (notifikasi) |
| `Helper` | Fungsi bantu tampilan: escape output, rupiah, tanggal, badge, inisial, redirect |

Halaman (`index.php`, `reservations.php`, dst.) hanya membuat objek model dan memanggil method-nya,
sedangkan query SQL dan aturan bisnis berada di dalam class.
