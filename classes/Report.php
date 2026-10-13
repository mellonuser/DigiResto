<?php
/**
 * Report
 * Menyusun data laporan pemesanan pada rentang tanggal tertentu dan mengekspornya ke CSV.
 */
class Report
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Rekap utama dikelompokkan per hari agar bisa dipakai untuk tabel dan kartu ringkasan.
    public function byDay(string $from, string $to): array
    {
        return $this->db->fetchAll("SELECT r.reservation_date d, COUNT(*) total, SUM(r.guests) guests,
    SUM(r.status='completed') done, SUM(r.status='cancelled') cancelled,
    COALESCE(SUM((SELECT SUM(qty*price) FROM order_items WHERE reservation_id=r.id)),0) revenue
    FROM reservations r WHERE r.reservation_date BETWEEN ? AND ? GROUP BY r.reservation_date ORDER BY r.reservation_date", [$from, $to]);
    }

    // Jumlah pemesanan per status: [status => jumlah].
    public function byStatus(string $from, string $to): array
    {
        $rows = $this->db->fetchAll("SELECT status, COUNT(*) n FROM reservations WHERE reservation_date BETWEEN ? AND ? GROUP BY status", [$from, $to]);
        return array_column($rows, 'n', 'status');
    }

    public function topCustomers(string $from, string $to): array
    {
        return $this->db->fetchAll("SELECT c.name, COUNT(*) n, SUM(r.guests) g FROM reservations r JOIN customers c ON c.id=r.customer_id
    WHERE r.reservation_date BETWEEN ? AND ? AND r.status<>'cancelled' GROUP BY c.id ORDER BY n DESC, g DESC LIMIT 5", [$from, $to]);
    }

    public function topMenus(string $from, string $to): array
    {
        return $this->db->fetchAll("SELECT m.name, SUM(oi.qty) qty, SUM(oi.qty*oi.price) rev FROM order_items oi
    JOIN menus m ON m.id=oi.menu_id JOIN reservations r ON r.id=oi.reservation_id
    WHERE r.reservation_date BETWEEN ? AND ? AND r.status<>'cancelled' GROUP BY m.id ORDER BY qty DESC LIMIT 5", [$from, $to]);
    }

    // Jumlahkan satu kolom rekap harian untuk nilai kartu di bagian atas laporan.
    public static function sum(array $byDay, string $key)
    {
        return array_sum(array_column($byDay, $key));
    }

    // Ekspor dilakukan sebelum template HTML dimuat agar output hanya berisi CSV.
    public function exportCsv(string $from, string $to, array $byDay): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=laporan_pemesanan_{$from}_{$to}.csv");
        $o = fopen('php://output', 'w');
        fputcsv($o, ['Tanggal', 'Pemesanan', 'Tamu', 'Selesai', 'Dibatalkan', 'Pendapatan']);
        foreach ($byDay as $r) {
            fputcsv($o, [$r['d'], $r['total'], $r['guests'], $r['done'], $r['cancelled'], $r['revenue']]);
        }
        exit;
    }
}
