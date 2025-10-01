<?php $lang = locale(); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang==='ar'?'rtl':'ltr' ?>">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
    <h3>Order <?= htmlspecialchars($o['order_code']) ?></h3>
    <p>Customer: <?= htmlspecialchars($o['customer_name']) ?> (<?= htmlspecialchars((string)$o['customer_email']) ?>)</p>
    <table>
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
        <tbody>
        <?php foreach ($o['items'] as $it): ?>
            <tr>
                <td><?= htmlspecialchars($it['name']) ?></td>
                <td><?= (int)$it['qty'] ?></td>
                <td><?= number_format((float)$it['price'],2) ?></td>
                <td><?= number_format((float)$it['total'],2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p>
        Subtotal: <?= number_format((float)$o['subtotal'],2) ?> | 
        Shipping: <?= number_format((float)$o['shipping'],2) ?> | 
        Deposit: <?= number_format((float)$o['deposit'],2) ?> | 
        <strong>Total: <?= number_format((float)$o['total'],2) ?></strong>
    </p>
    <form method="post" action="/admin/order/status">
        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
        <label>Status
            <select name="status" required>
                <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button>Update & Notify</button>
    </form>
    <p><a href="/admin/orders">← Back</a></p>
</main>
</body>
</html>
