<?php
/**
 * Dashboard
 * Menyediakan data ringkasan harian untuk halaman Ringkasan (index.php).
 */
class Dashboard
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function totalByDate(string $date)
    {
        return $this->db->fetchColumn("SELECT COUNT(*) FROM reservations WHERE reservation_date=?", [$date]);
    }

    public function pendingByDate(string $date)
    {
        return $this->db->fetchColumn("SELECT COUNT(*) FROM reservations WHERE reservation_date=? AND status='pending'", [$date]);
    }

    public function guestsByDate(string $date)
    {
        return $this->db->fetchColumn("SELECT COALESCE(SUM(guests),0) FROM reservations WHERE reservation_date=? AND status<>'cancelled'", [$date]);
    }

    // Meja yang memiliki pemesanan aktif: [table_id => 1 jika sedang dilayani, 0 jika baru dipesan].
    public function tableStatuses(string $date): array
    {
        $rows = $this->db->fetchAll("SELECT table_id, MAX(status='seated') seated FROM reservations WHERE reservation_date=? AND status IN ('pending','confirmed','seated') GROUP BY table_id", [$date]);
        return array_column($rows, 'seated', 'table_id');
    }

    public function upcomingReservations(string $date, int $limit = 8): array
    {
        return $this->db->fetchAll("SELECT r.*, c.name cname, t.code tcode FROM reservations r
  JOIN customers c ON c.id=r.customer_id JOIN tables t ON t.id=r.table_id
  WHERE r.reservation_date=? AND r.status<>'cancelled' ORDER BY r.reservation_time LIMIT " . (int)$limit, [$date]);
    }

    // Data per jam digunakan untuk membuat visualisasi jam tersibuk: [jam => jumlah tamu].
    public function guestsPerHour(string $date): array
    {
        $rows = $this->db->fetchAll("SELECT HOUR(reservation_time) h, SUM(guests) g FROM reservations WHERE reservation_date=? AND status<>'cancelled' GROUP BY h", [$date]);
        return array_column($rows, 'g', 'h');
    }

    public function notesCount(string $date)
    {
        return $this->db->fetchColumn("SELECT COUNT(*) FROM reservations WHERE reservation_date=? AND notes<>'' AND status<>'cancelled'", [$date]);
    }
}
