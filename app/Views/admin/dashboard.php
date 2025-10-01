<?php $lang = locale(); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= t('dashboard') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        body{padding:20px}
    </style>
    </head>
<body>
    <nav>
        <ul>
            <li><strong><?= t('dashboard') ?></strong></li>
        </ul>
        <ul>
            <li><a href="?lang=ar"><?= t('arabic') ?></a></li>
            <li><a href="?lang=en"><?= t('english') ?></a></li>
        </ul>
    </nav>
    <main class="container">
        <ul>
            <li><a href="/admin/settings"><?= t('settings') ?></a></li>
            <li><a href="/admin/zones"><?= t('shipping_zones') ?></a></li>
            <li><a href="/admin/devices">Devices</a></li>
        </ul>
    </main>
</body>
</html>
