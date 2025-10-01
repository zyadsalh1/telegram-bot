<?php $lang = locale(); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>textarea{min-height:160px}</style>
</head>
<body>
<main class="container">
    <h3>Edit Page</h3>
    <form method="post" action="/admin/page/save">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string)($page['id'] ?? '')) ?>">
        <label>Slug<input name="slug" required value="<?= htmlspecialchars((string)($page['slug'] ?? '')) ?>"></label>

        <h5>Arabic</h5>
        <label>Title<input name="title_ar" value="<?= htmlspecialchars((string)($tr['ar']['title'] ?? '')) ?>"></label>
        <label>Content<textarea name="content_ar"><?= htmlspecialchars((string)($tr['ar']['content'] ?? '')) ?></textarea></label>
        <label>Meta Title<input name="meta_title_ar" value="<?= htmlspecialchars((string)($tr['ar']['meta_title'] ?? '')) ?>"></label>
        <label>Meta Description<input name="meta_description_ar" value="<?= htmlspecialchars((string)($tr['ar']['meta_description'] ?? '')) ?>"></label>
        <label>Meta Keywords<input name="meta_keywords_ar" value="<?= htmlspecialchars((string)($tr['ar']['meta_keywords'] ?? '')) ?>"></label>

        <h5>English</h5>
        <label>Title<input name="title_en" value="<?= htmlspecialchars((string)($tr['en']['title'] ?? '')) ?>"></label>
        <label>Content<textarea name="content_en"><?= htmlspecialchars((string)($tr['en']['content'] ?? '')) ?></textarea></label>
        <label>Meta Title<input name="meta_title_en" value="<?= htmlspecialchars((string)($tr['en']['meta_title'] ?? '')) ?>"></label>
        <label>Meta Description<input name="meta_description_en" value="<?= htmlspecialchars((string)($tr['en']['meta_description'] ?? '')) ?>"></label>
        <label>Meta Keywords<input name="meta_keywords_en" value="<?= htmlspecialchars((string)($tr['en']['meta_keywords'] ?? '')) ?>"></label>

        <button type="submit"><?= t('save') ?></button>
    </form>
    <p><a href="/admin/pages">← Back</a></p>
</main>
</body>
</html>
