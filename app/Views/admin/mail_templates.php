<?php $lang = locale(); ?>
<!doctype html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Templates</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>textarea{min-height:180px}</style>
</head>
<body>
<main class="container">
    <h3>Email Templates</h3>
    <form method="post" action="/admin/mail/template/save">
        <label>Key
            <select name="key">
                <option value="order_new">order_new</option>
                <option value="order_status">order_status</option>
                <option value="invoice">invoice</option>
            </select>
        </label>
        <label>Locale
            <select name="locale">
                <option value="ar">ar</option>
                <option value="en">en</option>
            </select>
        </label>
        <label>Subject<input name="subject"></label>
        <label>Body (HTML) – variables: {{site}}, {{order_id}}, {{status}}, {{customer}}, {{total}}, {{invoice_html}}
            <textarea name="body"></textarea>
        </label>
        <button><?= t('save') ?></button>
    </form>
    <h5>Existing</h5>
    <table>
        <thead><tr><th>Key</th><th>Locale</th><th>Subject</th></tr></thead>
        <tbody>
        <?php foreach ($templates as $tpl): ?>
            <tr>
                <td><?= htmlspecialchars($tpl['key']) ?></td>
                <td><?= htmlspecialchars($tpl['locale']) ?></td>
                <td><?= htmlspecialchars($tpl['subject']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="/admin">← <?= t('dashboard') ?></a></p>
</main>
</body>
</html>
