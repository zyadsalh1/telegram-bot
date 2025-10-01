<?php
declare(strict_types=1);

namespace App\Config;

final class Config
{
    public const DB_HOST = 'localhost';
    public const DB_NAME = 'store_db';
    public const DB_USER = 'root';
    public const DB_PASS = '';
    public const DB_CHARSET = 'utf8mb4';

    public const APP_URL = '/';
    public const COOKIE_SALT = 'change-this-secret-salt';

    // Deposit settings defaults; can be overridden in DB settings
    public const DEPOSIT_ENABLED_DEFAULT = false;
    public const DEPOSIT_TYPE_DEFAULT = 'percent'; // fixed|percent
    public const DEPOSIT_VALUE_DEFAULT = 10.0; // 10%
    public const DEPOSIT_SCOPE_DEFAULT = 'global'; // global|per_device
}
?>
