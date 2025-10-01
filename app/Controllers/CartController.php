<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;

final class CartController
{
    private function cart(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    private function save(array $cart): void
    {
        $_SESSION['cart'] = $cart;
    }

    public function add(): void
    {
        $id = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $p = (new Product())->find($id);
        if (!$p) { \redirect('/'); }
        $cart = $this->cart();
        if (!isset($cart[$id])) { $cart[$id] = ['id' => $id, 'name' => $p['name_' . \locale()], 'price' => (float)$p['price'], 'qty' => 0]; }
        $cart[$id]['qty'] += $qty;
        $this->save($cart);
        \redirect('/cart');
    }

    public function remove(): void
    {
        $id = (int)($_POST['product_id'] ?? 0);
        $cart = $this->cart();
        unset($cart[$id]);
        $this->save($cart);
        \redirect('/cart');
    }

    public function view(): void
    {
        $cart = $this->cart();
        \view('public/cart', ['cart' => $cart]);
    }
}
?>
