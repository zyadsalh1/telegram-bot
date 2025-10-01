<?php
declare(strict_types=1);

// Simple autoloader (no composer dependency for cPanel simplicity)
spl_autoload_register(function(string $class): void {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require __DIR__ . '/Config/config.php';
require __DIR__ . '/Helpers/functions.php';

use App\Models\Device;

// Ensure device id cookie exists for per-device features
ensure_device_cookie();

// Initialize device record when first seen
$deviceId = get_device_id();
if ($deviceId) {
    try {
        $deviceModel = new Device();
        $deviceModel->ensureExists($deviceId, $_SERVER['HTTP_USER_AGENT'] ?? '', $_SERVER['REMOTE_ADDR'] ?? '');
    } catch (Throwable $e) {
        error_log('Device ensure error: ' . $e->getMessage());
    }
}
?>
