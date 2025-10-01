<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class Page
{
    private PDO $db;
    public function __construct() { $this->db = \db(); }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM pages ORDER BY id DESC')->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM pages WHERE slug=?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM pages WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function translations(int $pageId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM page_translations WHERE page_id=?');
        $stmt->execute([$pageId]);
        return $stmt->fetchAll();
    }

    public function upsert(array $data, array $translations): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare('UPDATE pages SET slug=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$data['slug'], $data['id']]);
            $pageId = (int)$data['id'];
        } else {
            $stmt = $this->db->prepare('INSERT INTO pages (slug, created_at, updated_at) VALUES (?, NOW(), NOW())');
            $stmt->execute([$data['slug']]);
            $pageId = (int)$this->db->lastInsertId();
        }

        foreach ($translations as $locale => $t) {
            $stmt = $this->db->prepare('INSERT INTO page_translations (page_id, locale, title, content, meta_title, meta_description, meta_keywords) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title), content=VALUES(content), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), meta_keywords=VALUES(meta_keywords)');
            $stmt->execute([$pageId, $locale, $t['title'] ?? '', $t['content'] ?? '', $t['meta_title'] ?? null, $t['meta_description'] ?? null, $t['meta_keywords'] ?? null]);
        }
        return $pageId;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM pages WHERE id=?');
        $stmt->execute([$id]);
    }
}
?>
