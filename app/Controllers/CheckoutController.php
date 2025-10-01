<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\Notifications;

final class CheckoutController
{
    public function checkout(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        if (!$cart) { \redirect('/cart'); }

        $zoneId = (int)($_POST['zone_id'] ?? 0);
        $zones = new ShippingZone();
        $zone = $zoneId ? $zones->find($zoneId) : null;
        $subtotal = 0;
        $items = [];
        foreach ($cart as $c) {
            $line = $c['price'] * $c['qty'];
            $subtotal += $line;
            $items[] = [
                'product_id' => $c['id'],
                'name' => $c['name'],
                'qty' => $c['qty'],
                'price' => $c['price'],
                'total' => $line,
            ];
        }
        $shipping = $zone ? (float)$zone['delivery_fee'] : 0;

        // Deposit logic
        $depositCfg = \setting('deposit', [ 'enabled' => false, 'type' => 'percent', 'value' => 0, 'scope' => 'global' ]);
        $deposit = 0.0;
        $deviceRequired = false;
        if (!empty($depositCfg['enabled'])) {
            if (($depositCfg['scope'] ?? 'global') === 'per_device' && ($d = \get_device_id())) {
                $stmt = \db()->prepare('SELECT deposit_required FROM devices WHERE device_id=?');
                $stmt->execute([$d]);
                $deviceRequired = (bool)($stmt->fetch()['deposit_required'] ?? 0);
            }
            if (($depositCfg['scope'] ?? 'global') === 'global' || $deviceRequired) {
                if (($depositCfg['type'] ?? 'percent') === 'fixed') {
                    $deposit = (float)($depositCfg['value'] ?? 0);
                } else {
                    $deposit = round(($subtotal + $shipping) * ((float)($depositCfg['value'] ?? 0) / 100), 2);
                }
            }
        }

        $total = max(0, $subtotal + $shipping - 0);

        $orderData = [
            'customer_name' => $_POST['name'] ?? 'Customer',
            'customer_email' => $_POST['email'] ?? null,
            'customer_phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'zone_id' => $zoneId ?: null,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => 0,
            'deposit' => $deposit,
            'total' => $total,
        ];
        $orderId = (new Order())->create($orderData, $items);
        $order = (new Order())->find($orderId);

        // Send emails
        (new Notifications())->orderCreated($order, \locale());

        unset($_SESSION['cart']);
        echo 'Order placed. Code: ' . htmlspecialchars((string)$order['order_code']);
    }
}
?>
