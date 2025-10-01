<?php $lang = locale(); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang==='ar'?'rtl':'ltr' ?>">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
    <h3>Orders</h3>
    <table>
        <thead><tr><th>ID</th><th>Code</th><th>Customer</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= (int)$o['id'] ?></td>
                <td><?= htmlspecialchars($o['order_code']) ?></td>
                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                <td><?= number_format((float)$o['total'],2) ?></td>
                <td><?= htmlspecialchars($o['status']) ?></td>
                <td>
                    <a href="/admin/order/<?= (int)$o['id'] ?>">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="/admin">← <?= t('dashboard') ?></a></p>
</main>
</body>
</html>
