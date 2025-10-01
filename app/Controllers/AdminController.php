<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\ShippingZone;

final class AdminController
{
    public function dashboard(): void
    {
        \view('admin/dashboard', []);
    }

    public function settings(): void
    {
        $deposit = \setting('deposit', [
            'enabled' => \App\Config\Config::DEPOSIT_ENABLED_DEFAULT,
            'type' => \App\Config\Config::DEPOSIT_TYPE_DEFAULT,
            'value' => \App\Config\Config::DEPOSIT_VALUE_DEFAULT,
            'scope' => \App\Config\Config::DEPOSIT_SCOPE_DEFAULT,
        ]);
        \view('admin/settings', ['deposit' => $deposit]);
    }

    public function saveSettings(): void
    {
        $payload = [
            'enabled' => isset($_POST['deposit_enabled']),
            'type' => $_POST['deposit_type'] ?? 'percent',
            'value' => (float)($_POST['deposit_value'] ?? 0),
            'scope' => $_POST['deposit_scope'] ?? 'global',
        ];
        $pdo = \db();
        $stmt = $pdo->prepare('INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');
        $stmt->execute(['deposit', json_encode($payload)]);
        \redirect('/admin/settings');
    }

    public function devices(): void
    {
        $devices = \db()->query('SELECT * FROM devices ORDER BY created_at DESC')->fetchAll();
        \view('admin/devices', ['devices' => $devices]);
    }

    public function toggleDeviceDeposit(): void
    {
        $deviceId = $_POST['device_id'] ?? '';
        $required = isset($_POST['required']);
        $stmt = \db()->prepare('UPDATE devices SET deposit_required=? WHERE device_id=?');
        $stmt->execute([$required ? 1 : 0, $deviceId]);
        \redirect('/admin/devices');
    }

    public function zones(): void
    {
        $zones = (new ShippingZone())->all();
        \view('admin/zones', ['zones' => $zones]);
    }

    public function zoneSave(): void
    {
        $zone = new ShippingZone();
        $data = [
            'name' => $_POST['name'] ?? '',
            'country' => $_POST['country'] ?? '',
            'cities' => array_filter(array_map('trim', explode(',', $_POST['cities'] ?? ''))),
            'delivery_fee' => (float)($_POST['delivery_fee'] ?? 0),
            'min_order' => (float)($_POST['min_order'] ?? 0),
        ];
        if (!empty($_POST['id'])) {
            $zone->update((int)$_POST['id'], $data);
        } else {
            $zone->create($data);
        }
        \redirect('/admin/zones');
    }

    public function zoneDelete(): void
    {
        if (!empty($_POST['id'])) {
            (new ShippingZone())->delete((int)$_POST['id']);
        }
        \redirect('/admin/zones');
    }
}
?>
