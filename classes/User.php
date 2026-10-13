<?php
/**
 * User
 * Model tabel users (akun yang dapat login ke aplikasi).
 */
class User extends BaseModel
{
    protected string $table = 'users';

    public function findByUsername(string $username)
    {
        return $this->db->fetch("SELECT * FROM users WHERE username=?", [$username]);
    }

    public function countAll()
    {
        return $this->db->fetchColumn("SELECT COUNT(*) FROM users");
    }

    public function create(string $name, string $username, string $passwordHash, string $role): void
    {
        $this->db->run("INSERT INTO users (name, username, password, role) VALUES (?,?,?,?)",
            [$name, $username, $passwordHash, $role]);
    }
}
