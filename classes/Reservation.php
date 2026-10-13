<?php
/**
 * Reservation
 * Model tabel reservations (pemesanan meja) beserta aturan bisnisnya:
 * status pemesanan, pembuatan kode otomatis, dan pengecekan bentrok meja.
 */
class Reservation extends BaseModel
{
    // Satu sumber data untuk label dan warna status di seluruh halaman.
    public const STATUS = [
        'pending'   => ['Menunggu', 'warning'],
        'confirmed' => ['Dikonfirmasi', 'success'],
        'seated'    => ['Dilayani', 'primary'],
        'completed' => ['Selesai', 'secondary'],
        'cancelled' => ['Dibatalkan', 'danger'],
    ];

    protected string $table = 'reservations';

    public static function isValidStatus($status): bool
    {
        return isset(self::STATUS[$status]);
    }

    // Jumlah pemesanan yang masih menunggu konfirmasi (untuk badge navigasi).
    public function countPending()
    {
        return $this->db->fetchColumn("SELECT COUNT(*) FROM reservations WHERE status='pending'");
    }

    // Satu pemesanan lengkap dengan data pelanggan dan meja.
    public function findDetail(int $id)
    {
        return $this->db->fetch("SELECT r.*, c.name cname, c.phone, c.email, t.code tcode, t.capacity FROM reservations r
    JOIN customers c ON c.id=r.customer_id JOIN tables t ON t.id=r.table_id WHERE r.id=?", [$id]);
    }

    // Susun filter secara dinamis, tetapi tetap gunakan parameter binding untuk nilai pengguna.
    public function search(string $q, string $date, string $status): array
    {
        $sql = "SELECT r.*, c.name cname, c.phone, t.code tcode FROM reservations r
        JOIN customers c ON c.id=r.customer_id JOIN tables t ON t.id=r.table_id WHERE 1=1";
        $p = [];
        if ($q !== '')      { $sql .= " AND (c.name LIKE ? OR r.code LIKE ? OR c.phone LIKE ?)"; array_push($p, "%$q%", "%$q%", "%$q%"); }
        if ($date !== '')   { $sql .= " AND r.reservation_date=?"; $p[] = $date; }
        if ($status !== '') { $sql .= " AND r.status=?"; $p[] = $status; }
        $sql .= " ORDER BY r.reservation_date DESC, r.reservation_time";
        return $this->db->fetchAll($sql, $p);
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->db->run("UPDATE reservations SET status=? WHERE id=?", [$status, $id]);
    }

    // Cek bentrok meja pada tanggal yang sama dengan jeda pemesanan kurang dari 2 jam.
    public function hasConflict(int $tableId, string $date, string $time, int $excludeId = 0): bool
    {
        return $this->db->fetchColumn("SELECT COUNT(*) FROM reservations WHERE table_id=? AND reservation_date=?
        AND status IN ('pending','confirmed','seated') AND id<>?
        AND ABS(TIME_TO_SEC(TIMEDIFF(reservation_time, ?))) < 7200", [$tableId, $date, $excludeId, $time]) > 0;
    }

    // Simpan pemesanan baru dan kembalikan ID-nya.
    public function create(array $r): int
    {
        $this->db->run("INSERT INTO reservations (customer_id, table_id, reservation_date, reservation_time, guests, status, notes) VALUES (?,?,?,?,?,?,?)",
            [$r['customer_id'], $r['table_id'], $r['reservation_date'], $r['reservation_time'], $r['guests'], $r['status'], $r['notes']]);
        $id = (int)$this->db->lastInsertId();
        // Kode dibuat setelah ID tersedia agar tetap unik dan mudah dilacak.
        $this->db->run("UPDATE reservations SET code=? WHERE id=?", ['RSV-' . (2400 + $id), $id]);
        return $id;
    }

    public function update(int $id, array $r): void
    {
        $this->db->run("UPDATE reservations SET customer_id=?, table_id=?, reservation_date=?, reservation_time=?, guests=?, status=?, notes=? WHERE id=?",
            [$r['customer_id'], $r['table_id'], $r['reservation_date'], $r['reservation_time'], $r['guests'], $r['status'], $r['notes'], $id]);
    }
}
