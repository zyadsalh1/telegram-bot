<?php
declare(strict_types=1);

namespace App\Models;

final class EmailTemplate
{
    public function get(string $key, string $locale): ?array
    {
        $stmt = \db()->prepare('SELECT * FROM email_templates WHERE `key`=? AND locale=?');
        $stmt->execute([$key, $locale]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(): array
    {
        return \db()->query('SELECT * FROM email_templates ORDER BY `key`, locale')->fetchAll();
    }

    public function save(string $key, string $locale, string $subject, string $body): void
    {
        $stmt = \db()->prepare('INSERT INTO email_templates (`key`, locale, subject, body) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE subject=VALUES(subject), body=VALUES(body)');
        $stmt->execute([$key, $locale, $subject, $body]);
    }
}
?>
