<?php
/**
 * Database
 * Pembungkus koneksi PDO dengan pola Singleton: seluruh aplikasi memakai satu koneksi yang sama.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        try {
            $this->pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $ex) {
            die('Koneksi database gagal. Pastikan database "DigitalRestaurant" sudah diimpor (database.sql).');
        }
    }

    private function __clone() {}

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Jalankan query dengan parameter binding, kembalikan statement-nya.
    public function run(string $sql, array $params = []): PDOStatement
    {
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    // Satu baris hasil, atau false bila tidak ada.
    public function fetch(string $sql, array $params = [])
    {
        return $this->run($sql, $params)->fetch();
    }

    // Nilai kolom pertama dari baris pertama (untuk COUNT, SUM, dan sejenisnya).
    public function fetchColumn(string $sql, array $params = [])
    {
        return $this->run($sql, $params)->fetchColumn();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
