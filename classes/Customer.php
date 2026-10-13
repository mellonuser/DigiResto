<?php
/**
 * Customer
 * Model tabel customers (data pelanggan restoran).
 */
class Customer extends BaseModel
{
    protected string $table = 'customers';

    // Pencarian mencakup nama dan nomor telepon, sedangkan riwayat dihitung lewat LEFT JOIN.
    public function search(string $q): array
    {
        return $this->db->fetchAll("SELECT c.*, COUNT(r.id) total, MAX(r.reservation_date) last_visit FROM customers c
    LEFT JOIN reservations r ON r.customer_id=c.id AND r.status<>'cancelled'
    WHERE c.name LIKE ? OR c.phone LIKE ? GROUP BY c.id ORDER BY c.name", ["%$q%", "%$q%"]);
    }

    // Daftar ringkas untuk pilihan pelanggan pada form pemesanan.
    public function options(): array
    {
        return $this->db->fetchAll("SELECT id, name, phone FROM customers ORDER BY name");
    }

    // Tambah pelanggan baru dan kembalikan ID-nya.
    public function create(string $name, string $phone, ?string $email = null, ?string $notes = null): int
    {
        $this->db->run("INSERT INTO customers (name, phone, email, notes) VALUES (?,?,?,?)",
            [$name, $phone, $email, $notes]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $phone, ?string $email, ?string $notes): void
    {
        $this->db->run("UPDATE customers SET name=?, phone=?, email=?, notes=? WHERE id=?",
            [$name, $phone, $email, $notes, $id]);
    }
}
