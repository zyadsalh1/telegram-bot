<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;

final class ProductController
{
    public function show(int $id): void
    {
        $p = (new Product())->find($id);
        if (!$p) { http_response_code(404); echo 'Not Found'; return; }
        \view('public/product', ['p' => $p]);
    }
}
?>
