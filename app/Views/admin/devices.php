<?php $lang = locale(); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Devices</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
    <h3>Devices</h3>
    <table>
        <thead><tr><th>ID</th><th>Deposit Required</th><th>Toggle</th></tr></thead>
        <tbody>
        <?php foreach ($devices as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['device_id']) ?></td>
                <td><?= $d['deposit_required'] ? 'Yes' : 'No' ?></td>
                <td>
                    <form method="post" action="/admin/devices/toggle">
                        <input type="hidden" name="device_id" value="<?= htmlspecialchars($d['device_id']) ?>">
                        <label>
                            <input type="checkbox" name="required" <?= $d['deposit_required'] ? 'checked' : '' ?> onchange="this.form.submit()">
                        </label>
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
