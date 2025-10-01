<?php
declare(strict_types=1);

namespace App\Models;

final class ContactSettings
{
    public function get(): array
    {
        $row = \db()->query('SELECT * FROM contact_settings WHERE id=1')->fetch();
        return $row ?: [
            'id' => 1,
            'phone' => null,
            'email' => null,
            'facebook' => null,
            'instagram' => null,
            'tiktok' => null,
            'whatsapp' => null,
        ];
    }

    public function save(array $data): void
    {
        $pdo = \db();
        $pdo->exec('INSERT IGNORE INTO contact_settings (id, updated_at) VALUES (1, NOW())');
        $stmt = $pdo->prepare('UPDATE contact_settings SET phone=?, email=?, facebook=?, instagram=?, tiktok=?, whatsapp=?, updated_at=NOW() WHERE id=1');
        $stmt->execute([
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['facebook'] ?? null,
            $data['instagram'] ?? null,
            $data['tiktok'] ?? null,
            $data['whatsapp'] ?? null,
        ]);
    }
}
?>
