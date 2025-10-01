<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Page;

final class PageController
{
    public function show(string $slug): void
    {
        $page = (new Page())->findBySlug($slug);
        if (!$page) { http_response_code(404); echo 'Not Found'; return; }
        $locale = \locale();
        $stmt = \db()->prepare('SELECT * FROM page_translations WHERE page_id=? AND locale=?');
        $stmt->execute([$page['id'], $locale]);
        $tr = $stmt->fetch();
        \view('public/page', ['page' => $page, 'tr' => $tr, 'locale' => $locale]);
    }
}
?>
