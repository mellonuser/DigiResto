<?php
/**
 * Menu
 * Model tabel menus (daftar makanan dan minuman).
 */
class Menu extends BaseModel
{
    protected string $table = 'menus';

    public function allOrdered(): array
    {
        return $this->db->fetchAll("SELECT * FROM menus ORDER BY category, name");
    }

    public function create(string $name, string $category, int $price): void
    {
        $this->db->run("INSERT INTO menus (name, category, price) VALUES (?,?,?)", [$name, $category, $price]);
    }

    public function update(int $id, string $name, string $category, int $price): void
    {
        $this->db->run("UPDATE menus SET name=?, category=?, price=? WHERE id=?", [$name, $category, $price, $id]);
    }
}
