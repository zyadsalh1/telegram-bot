<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Services\Notifications;

final class AdminOrdersController
{
    public function list(): void
    {
        $orders = (new Order())->all();
        \view('admin/orders', ['orders' => $orders]);
    }

    public function view(int $id): void
    {
        $o = (new Order())->find($id);
        if (!$o) { http_response_code(404); echo 'Not Found'; return; }
        \view('admin/order_view', ['o' => $o]);
    }

    public function status(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $m = new Order();
        $m->updateStatus($id, $status);
        $o = $m->find($id);
        (new Notifications())->orderStatusChanged($o, \locale());
        \redirect('/admin/orders');
    }
}
?>
