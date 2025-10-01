<?php $lang = $locale ?? locale(); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string)($tr['meta_title'] ?? $tr['title'] ?? '')) ?></title>
    <?php if (!empty($tr['meta_description'])): ?>
    <meta name="description" content="<?= htmlspecialchars((string)$tr['meta_description']) ?>">
    <?php endif; ?>
    <?php if (!empty($tr['meta_keywords'])): ?>
    <meta name="keywords" content="<?= htmlspecialchars((string)$tr['meta_keywords']) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
    <h1><?= htmlspecialchars((string)($tr['title'] ?? '')) ?></h1>
    <article><?= $tr['content'] ?? '' ?></article>
</main>
</body>
</html>
