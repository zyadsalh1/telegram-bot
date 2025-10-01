<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class Device
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \db();
    }

    public function ensureExists(string $deviceId, string $userAgent, string $ip): void
    {
        $stmt = $this->db->prepare('INSERT INTO devices (device_id, user_agent, ip, created_at) VALUES (?,?,?,NOW()) ON DUPLICATE KEY UPDATE user_agent = VALUES(user_agent), ip = VALUES(ip)');
        $stmt->execute([$deviceId, $userAgent, $ip]);
    }

    public function setDepositRequired(string $deviceId, bool $required): void
    {
        $stmt = $this->db->prepare('UPDATE devices SET deposit_required = ? WHERE device_id = ?');
        $stmt->execute([$required ? 1 : 0, $deviceId]);
    }

    public function isDepositRequired(string $deviceId): ?bool
    {
        $stmt = $this->db->prepare('SELECT deposit_required FROM devices WHERE device_id = ?');
        $stmt->execute([$deviceId]);
        $row = $stmt->fetch();
        return $row ? (bool)$row['deposit_required'] : null;
    }
}
?>
