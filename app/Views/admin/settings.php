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

    <h3><?= t('settings') ?> - Contact</h3>
    <form method="post" action="/admin/settings">
        <input type="hidden" name="_section" value="contact">
        <label>Phone<input name="phone" value="<?= htmlspecialchars((string)($contact['phone'] ?? '')) ?>"></label>
        <label>Email<input name="email" value="<?= htmlspecialchars((string)($contact['email'] ?? '')) ?>"></label>
        <label>Facebook<input name="facebook" value="<?= htmlspecialchars((string)($contact['facebook'] ?? '')) ?>"></label>
        <label>Instagram<input name="instagram" value="<?= htmlspecialchars((string)($contact['instagram'] ?? '')) ?>"></label>
        <label>TikTok<input name="tiktok" value="<?= htmlspecialchars((string)($contact['tiktok'] ?? '')) ?>"></label>
        <label>WhatsApp<input name="whatsapp" value="<?= htmlspecialchars((string)($contact['whatsapp'] ?? '')) ?>"></label>
        <button type="submit"><?= t('save') ?></button>
    </form>

    <h3>SMTP</h3>
    <?php $smtp = db()->query('SELECT * FROM smtp_settings WHERE id=1')->fetch() ?: []; ?>
    <form method="post" action="/admin/settings">
        <label>Host<input name="smtp_host" value="<?= htmlspecialchars((string)($smtp['host'] ?? '')) ?>" required></label>
        <label>Port<input type="number" name="smtp_port" value="<?= htmlspecialchars((string)($smtp['port'] ?? '587')) ?>"></label>
        <label>Username<input name="smtp_username" value="<?= htmlspecialchars((string)($smtp['username'] ?? '')) ?>"></label>
        <label>Password<input type="password" name="smtp_password" value="<?= htmlspecialchars((string)($smtp['password'] ?? '')) ?>"></label>
        <label>Encryption
            <select name="smtp_encryption">
                <option value="none" <?= (($smtp['encryption'] ?? '')==='none')?'selected':'' ?>>none</option>
                <option value="ssl" <?= (($smtp['encryption'] ?? '')==='ssl')?'selected':'' ?>>ssl</option>
                <option value="tls" <?= (($smtp['encryption'] ?? 'tls')==='tls')?'selected':'' ?>>tls</option>
            </select>
        </label>
        <label>From Email<input name="smtp_from_email" value="<?= htmlspecialchars((string)($smtp['from_email'] ?? '')) ?>"></label>
        <label>From Name<input name="smtp_from_name" value="<?= htmlspecialchars((string)($smtp['from_name'] ?? '')) ?>"></label>
        <button><?= t('save') ?></button>
    </form>
    <form method="post" action="/admin/mail/test" style="margin-top:10px">
        <label>Test To<input name="to" placeholder="you@example.com"></label>
        <button>Send Test</button>
    </form>
    <p><a href="/admin">← <?= t('dashboard') ?></a></p>
</main>
</body>
</html>
