<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class ShippingZone
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \db();
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM shipping_zones ORDER BY id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM shipping_zones WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO shipping_zones (name, country, cities, delivery_fee, min_order) VALUES (?,?,?,?,?)');
        $stmt->execute([
            $data['name'],
            $data['country'],
            json_encode($data['cities'] ?? []),
            $data['delivery_fee'],
            $data['min_order'] ?? 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE shipping_zones SET name=?, country=?, cities=?, delivery_fee=?, min_order=? WHERE id=?');
        $stmt->execute([
            $data['name'],
            $data['country'],
            json_encode($data['cities'] ?? []),
            $data['delivery_fee'],
            $data['min_order'] ?? 0,
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM shipping_zones WHERE id = ?');
        $stmt->execute([$id]);
    }
}
?>
