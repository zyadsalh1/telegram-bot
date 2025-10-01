<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class Order
{
    private PDO $db;
    public function __construct(){ $this->db = \db(); }

    public function create(array $order, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $order['order_code'] = $order['order_code'] ?? strtoupper(bin2hex(random_bytes(4)));
            $stmt = $this->db->prepare('INSERT INTO orders (order_code, customer_name, customer_email, customer_phone, address, zone_id, subtotal, shipping, discount, deposit, total, status, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
            $stmt->execute([
                $order['order_code'], $order['customer_name'], $order['customer_email'] ?? null, $order['customer_phone'] ?? null, $order['address'] ?? null, $order['zone_id'] ?? null,
                $order['subtotal'], $order['shipping'], $order['discount'] ?? 0, $order['deposit'] ?? 0, $order['total'], $order['status'] ?? 'pending'
            ]);
            $orderId = (int)$this->db->lastInsertId();
            $stmtI = $this->db->prepare('INSERT INTO order_items (order_id, product_id, name, qty, price, total) VALUES (?,?,?,?,?,?)');
            foreach ($items as $it) {
                $stmtI->execute([$orderId, $it['product_id'], $it['name'], $it['qty'], $it['price'], $it['total']]);
            }
            $this->db->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id=?');
        $stmt->execute([$id]);
        $o = $stmt->fetch();
        if (!$o) return null;
        $o['items'] = $this->items($id);
        return $o;
    }

    public function items(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM order_items WHERE order_id=?');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM orders ORDER BY id DESC')->fetchAll();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE orders SET status=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$status, $id]);
    }
}
?>
