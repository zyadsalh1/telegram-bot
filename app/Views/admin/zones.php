<?php $lang = locale(); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= t('shipping_zones') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
    <h3><?= t('shipping_zones') ?></h3>
    <form method="post" action="/admin/zones/save">
        <input type="hidden" name="id" value="">
        <label><?= t('name') ?><input name="name"></label>
        <label><?= t('country') ?><input name="country" placeholder="EG"></label>
        <label><?= t('cities') ?><input name="cities" placeholder="Cairo,Giza,Alex"></label>
        <label><?= t('delivery_fee') ?><input type="number" step="0.01" name="delivery_fee"></label>
        <label><?= t('min_order') ?><input type="number" step="0.01" name="min_order"></label>
        <button><?= t('add_zone') ?></button>
    </form>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th><?= t('name') ?></th>
                <th><?= t('country') ?></th>
                <th><?= t('cities') ?></th>
                <th><?= t('delivery_fee') ?></th>
                <th><?= t('min_order') ?></th>
                <th><?= t('actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($zones as $z): ?>
            <tr>
                <td><?= (int)$z['id'] ?></td>
                <td><?= htmlspecialchars($z['name']) ?></td>
                <td><?= htmlspecialchars($z['country']) ?></td>
                <td><?= htmlspecialchars(implode(',', json_decode($z['cities'] ?: '[]', true))) ?></td>
                <td><?= (float)$z['delivery_fee'] ?></td>
                <td><?= (float)$z['min_order'] ?></td>
                <td>
                    <form method="post" action="/admin/zones/delete" onsubmit="return confirm('Delete?')">
                        <input type="hidden" name="id" value="<?= (int)$z['id'] ?>">
                        <button type="submit"><?= t('delete') ?></button>
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
