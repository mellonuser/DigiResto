<?php
/**
 * Auth
 * Mengelola autentikasi: login, logout, proteksi halaman, dan token CSRF.
 */
class Auth
{
    // Lindungi halaman internal dari akses tanpa sesi login.
    public static function requireLogin(): void
    {
        if (empty($_SESSION['user'])) {
            Helper::redirect('login.php');
        }
    }

    public static function user(): array
    {
        return $_SESSION['user'];
    }

    // Sediakan akun awal agar instalasi baru bisa langsung digunakan.
    // Hapus mekanisme ini saat aplikasi dipindahkan ke production.
    public static function ensureDefaultUser(): void
    {
        $users = new User();
        if ($users->countAll() == 0) {
            $users->create('Raka Putra', 'admin', password_hash('admin123', PASSWORD_DEFAULT), 'Manajer');
        }
    }

    // Verifikasi username dan kata sandi; bila cocok, mulai sesi pengguna.
    public static function attempt(string $username, string $password): bool
    {
        $u = (new User())->findByUsername(trim($username));
        if ($u && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = $u;
            return true;
        }
        return false;
    }

    // Hapus seluruh data sesi agar pengguna benar-benar keluar dari aplikasi.
    public static function logout(): void
    {
        session_destroy();
    }

    // Sisipkan token CSRF yang sama ke setiap form POST.
    public static function csrfField(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
        return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
    }

    public static function checkCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            die('Token keamanan tidak valid. Muat ulang halaman.');
        }
    }
}
