<?php
/**
 * OrderItem
 * Model tabel order_items (rincian menu yang dipesan pada sebuah pemesanan).
 */
class OrderItem extends BaseModel
{
    protected string $table = 'order_items';

    public function byReservation(int $reservationId): array
    {
        return $this->db->fetchAll("SELECT oi.*, m.name FROM order_items oi JOIN menus m ON m.id=oi.menu_id WHERE reservation_id=?", [$reservationId]);
    }

    // Harga disalin dari menu saat item dipesan, jadi perubahan harga menu tidak memengaruhi pesanan lama.
    public function add(int $reservationId, array $menu, int $qty): void
    {
        $this->db->run("INSERT INTO order_items (reservation_id, menu_id, qty, price) VALUES (?,?,?,?)",
            [$reservationId, $menu['id'], $qty, $menu['price']]);
    }

    public function removeFromReservation(int $itemId, int $reservationId): void
    {
        $this->db->run("DELETE FROM order_items WHERE id=? AND reservation_id=?", [$itemId, $reservationId]);
    }

    // Total dihitung dari harga saat item dipesan, bukan harga menu terbaru.
    public static function total(array $items)
    {
        return array_sum(array_map(fn($i) => $i['qty'] * $i['price'], $items));
    }
}
