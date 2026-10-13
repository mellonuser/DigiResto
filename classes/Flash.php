<?php
/**
 * Flash
 * Pesan satu kali pakai: disimpan di sesi dan dihapus setelah ditampilkan.
 */
class Flash
{
    // Simpan pesan untuk ditampilkan setelah redirect berikutnya.
    public static function set(string $msg, string $type = 'success'): void
    {
        $_SESSION['flash'] = [$msg, $type];
    }

    // Ambil pesan (jika ada) lalu hapus dari sesi.
    public static function get(): ?array
    {
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }
}
