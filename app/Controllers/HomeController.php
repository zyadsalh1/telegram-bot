<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;

final class HomeController
{
    public function index(): void
    {
        $products = (new Product())->allActive();
        \view('public/home', ['products' => $products]);
    }
}
?>
