<?php $lang = locale(); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pages</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
    <h3>Pages</h3>
    <p><a href="/admin/page/edit">+ New Page</a></p>
    <table>
        <thead><tr><th>ID</th><th>Slug</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($pages as $p): ?>
            <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['slug']) ?></td>
                <td>
                    <a href="/admin/page/edit?id=<?= (int)$p['id'] ?>"><?= t('edit') ?></a>
                    <form method="post" action="/admin/page/delete" style="display:inline" onsubmit="return confirm('Delete?')">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button><?= t('delete') ?></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="/admin">← <?= t('dashboard') ?></a></p>
</main>
</body>
</html>
