<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class Product
{
    private PDO $db;
    public function __construct(){ $this->db = \db(); }

    public function allActive(): array
    {
        return $this->db->query('SELECT * FROM products WHERE active=1 ORDER BY id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id=? AND active=1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
?>
