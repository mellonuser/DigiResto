<?php
/**
 * Helper
 * Kumpulan fungsi bantu tampilan (static) yang dipakai di seluruh halaman.
 */
class Helper
{
    // Escape output sebelum ditampilkan ke HTML untuk mencegah injeksi markup.
    public static function e($s): string
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }

    public static function rupiah($n): string
    {
        return 'Rp ' . number_format((int)$n, 0, ',', '.');
    }

    public static function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    // Ambil inisial dari satu atau dua kata pertama nama pengguna/pelanggan.
    public static function initials($name): string
    {
        $p = preg_split('/\s+/', trim($name));
        return strtoupper(mb_substr($p[0], 0, 1) . (isset($p[1]) ? mb_substr($p[1], 0, 1) : ''));
    }

    public static function badge($s): string
    {
        [$l, $c] = Reservation::STATUS[$s];
        return "<span class='badge rounded-pill text-bg-$c'>$l</span>";
    }

    // Format tanggal database (YYYY-MM-DD) menjadi format yang lebih mudah dibaca.
    public static function tgl($d): string
    {
        $bln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $t = strtotime($d);
        return date('j', $t) . ' ' . $bln[(int)date('n', $t)] . ' ' . date('Y', $t);
    }
}
