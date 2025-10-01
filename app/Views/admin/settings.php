<?php $lang = locale(); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= t('settings') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
    <h3><?= t('deposit') ?></h3>
    <form method="post" action="/admin/settings">
        <label>
            <input type="checkbox" name="deposit_enabled" <?= !empty($deposit['enabled']) ? 'checked' : '' ?>>
            <?= t('deposit_enabled') ?>
        </label>
        <label>
            <?= t('deposit_type') ?>
            <select name="deposit_type">
                <option value="percent" <?= ($deposit['type'] ?? '') === 'percent' ? 'selected' : '' ?>>percent</option>
                <option value="fixed" <?= ($deposit['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>fixed</option>
            </select>
        </label>
        <label>
            <?= t('deposit_value') ?>
            <input type="number" step="0.01" name="deposit_value" value="<?= htmlspecialchars((string)($deposit['value'] ?? 0)) ?>">
        </label>
        <label>
            Scope
            <select name="deposit_scope">
                <option value="global" <?= ($deposit['scope'] ?? '') === 'global' ? 'selected' : '' ?>><?= t('scope_global') ?></option>
                <option value="per_device" <?= ($deposit['scope'] ?? '') === 'per_device' ? 'selected' : '' ?>><?= t('scope_per_device') ?></option>
            </select>
        </label>
        <button type="submit"><?= t('save') ?></button>
    </form>
    <p><a href="/admin">← <?= t('dashboard') ?></a></p>
</main>
</body>
</html>
