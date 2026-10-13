<?php
/**
 * BaseModel
 * Class induk untuk semua model tabel. Menyediakan operasi umum (cari satu data dan hapus)
 * yang diwarisi oleh Customer, DiningTable, Menu, Reservation, OrderItem, dan User.
 */
abstract class BaseModel
{
    protected Database $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id=?", [$id]);
    }

    public function delete(int $id): void
    {
        $this->db->run("DELETE FROM {$this->table} WHERE id=?", [$id]);
    }
}
