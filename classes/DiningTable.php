<?php
/**
 * DiningTable
 * Model tabel tables (meja restoran). Dinamai DiningTable agar tidak rancu dengan istilah "tabel" basis data.
 */
class DiningTable extends BaseModel
{
    protected string $table = 'tables';

    public function allActive(): array
    {
        return $this->db->fetchAll("SELECT * FROM tables WHERE is_active=1 ORDER BY id");
    }

    public function findActive(int $id)
    {
        return $this->db->fetch("SELECT * FROM tables WHERE id=? AND is_active=1", [$id]);
    }

    // Hitung pemesanan aktif per meja untuk menampilkan status operasional pada tanggal tertentu.
    public function allWithBookings(string $date): array
    {
        return $this->db->fetchAll("SELECT t.*, (SELECT COUNT(*) FROM reservations r WHERE r.table_id=t.id AND r.reservation_date=? AND r.status IN ('pending','confirmed','seated')) booked
    FROM tables t ORDER BY t.id", [$date]);
    }

    public function create(string $code, int $capacity, int $isActive): void
    {
        $this->db->run("INSERT INTO tables (code, capacity, is_active) VALUES (?,?,?)", [$code, $capacity, $isActive]);
    }

    public function update(int $id, string $code, int $capacity, int $isActive): void
    {
        $this->db->run("UPDATE tables SET code=?, capacity=?, is_active=? WHERE id=?", [$code, $capacity, $isActive, $id]);
    }
}
