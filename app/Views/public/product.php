<?php $lang = locale(); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang==='ar'?'rtl':'ltr' ?>">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($p['name_' . $lang]) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
    <h3><?= htmlspecialchars($p['name_' . $lang]) ?></h3>
    <p><?= number_format((float)$p['price'],2) ?></p>
    <form method="post" action="/cart/add">
        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
        <button>Add to cart</button>
    </form>
    <p><a href="/">Home</a></p>
    <p><a href="/cart">Cart</a></p>
    
</main>
</body>
</html>
