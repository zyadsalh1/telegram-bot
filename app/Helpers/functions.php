<?php
declare(strict_types=1);

use App\Config\Config;

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . App\Config\Config::DB_HOST . ';dbname=' . App\Config\Config::DB_NAME . ';charset=' . App\Config\Config::DB_CHARSET;
        $pdo = new PDO($dsn, App\Config\Config::DB_USER, App\Config\Config::DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $viewFile = __DIR__ . '/../Views/' . $template . '.php';
    if (!file_exists($viewFile)) {
        http_response_code(500);
        echo 'View not found';
        return;
    }
    include $viewFile;
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function ensure_device_cookie(): void
{
    if (!isset($_COOKIE['device_id'])) {
        $id = bin2hex(random_bytes(16));
        $hash = hash_hmac('sha256', $id, Config::COOKIE_SALT);
        setcookie('device_id', $id . '.' . $hash, time() + 86400 * 365, '/');
        $_COOKIE['device_id'] = $id . '.' . $hash;
    }
}

function get_device_id(): ?string
{
    if (!isset($_COOKIE['device_id'])) {
        return null;
    }
    [$id, $hash] = explode('.', $_COOKIE['device_id']) + [null, null];
    if (!$id || !$hash) {
        return null;
    }
    $expected = hash_hmac('sha256', $id, Config::COOKIE_SALT);
    if (!hash_equals($expected, $hash)) {
        return null;
    }
    return $id;
}

function setting(string $key, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        try {
            $stmt = db()->query('SELECT `key`, `value` FROM settings');
            $cache = [];
            foreach ($stmt->fetchAll() as $row) {
                $cache[$row['key']] = json_decode($row['value'], true);
            }
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}
?>
