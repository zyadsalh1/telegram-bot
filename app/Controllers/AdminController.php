<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\ShippingZone;
use App\Models\ContactSettings;
use App\Models\Page;

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
        $contact = (new ContactSettings())->get();
        \view('admin/settings', ['deposit' => $deposit, 'contact' => $contact]);
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
        // Save contact settings
        (new ContactSettings())->save([
            'phone' => $_POST['phone'] ?? null,
            'email' => $_POST['email'] ?? null,
            'facebook' => $_POST['facebook'] ?? null,
            'instagram' => $_POST['instagram'] ?? null,
            'tiktok' => $_POST['tiktok'] ?? null,
            'whatsapp' => $_POST['whatsapp'] ?? null,
        ]);
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

    public function pages(): void
    {
        $pages = (new Page())->all();
        \view('admin/pages', ['pages' => $pages]);
    }

    public function pageEdit(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $page = $id ? (new Page())->find($id) : null;
        $translations = $page ? (new Page())->translations((int)$page['id']) : [];
        $byLocale = [];
        foreach ($translations as $tr) { $byLocale[$tr['locale']] = $tr; }
        \view('admin/page_edit', ['page' => $page, 'tr' => $byLocale]);
    }

    public function pageSave(): void
    {
        $page = new Page();
        $data = [ 'id' => $_POST['id'] ?? null, 'slug' => trim($_POST['slug'] ?? '') ];
        $translations = [
            'ar' => [
                'title' => $_POST['title_ar'] ?? '',
                'content' => $_POST['content_ar'] ?? '',
                'meta_title' => $_POST['meta_title_ar'] ?? null,
                'meta_description' => $_POST['meta_description_ar'] ?? null,
                'meta_keywords' => $_POST['meta_keywords_ar'] ?? null,
            ],
            'en' => [
                'title' => $_POST['title_en'] ?? '',
                'content' => $_POST['content_en'] ?? '',
                'meta_title' => $_POST['meta_title_en'] ?? null,
                'meta_description' => $_POST['meta_description_en'] ?? null,
                'meta_keywords' => $_POST['meta_keywords_en'] ?? null,
            ],
        ];
        $id = $page->upsert($data, $translations);
        \redirect('/admin/pages');
    }

    public function pageDelete(): void
    {
        if (!empty($_POST['id'])) { (new Page())->delete((int)$_POST['id']); }
        \redirect('/admin/pages');
    }
}
?>
