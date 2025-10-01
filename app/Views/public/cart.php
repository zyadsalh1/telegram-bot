<?php $lang = locale(); $cart = $cart ?? ($_SESSION['cart'] ?? []); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang==='ar'?'rtl':'ltr' ?>">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
    <h3>Cart</h3>
    <table>
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr></thead>
        <tbody>
        <?php $subtotal=0; foreach ($cart as $c): $line=$c['qty']*$c['price']; $subtotal+=$line; ?>
            <tr>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><?= (int)$c['qty'] ?></td>
                <td><?= number_format((float)$c['price'],2) ?></td>
                <td><?= number_format((float)$line,2) ?></td>
                <td>
                    <form method="post" action="/cart/remove">
                        <input type="hidden" name="product_id" value="<?= (int)$c['id'] ?>">
                        <button>Remove</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h4>Checkout</h4>
    <form method="post" action="/checkout">
        <label>Name<input name="name" required></label>
        <label>Email<input type="email" name="email"></label>
        <label>Phone<input name="phone"></label>
        <label>Address<textarea name="address"></textarea></label>
        <label>Zone
            <select name="zone_id">
                <?php $zones = (new \App\Models\ShippingZone())->all(); foreach ($zones as $z): ?>
                <option value="<?= (int)$z['id'] ?>"><?= htmlspecialchars($z['name']) ?> - <?= number_format((float)$z['delivery_fee'],2) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button>Place order</button>
    </form>
    <p><a href="/">Home</a></p>
</main>
</body>
</html>
