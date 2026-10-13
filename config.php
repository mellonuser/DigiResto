<?php
// ===== Konfigurasi koneksi dan sesi aplikasi =====
const DB_HOST = 'localhost';
const DB_NAME = 'DigitalRestaurant';
const DB_USER = 'root';
const DB_PASS = '';

session_start();
date_default_timezone_set('Asia/Jakarta');

// Autoloader: setiap class dimuat otomatis dari folder classes/ (nama file = nama class).
spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

// Hentikan aplikasi lebih awal jika koneksi database belum tersedia.
Database::getInstance();
