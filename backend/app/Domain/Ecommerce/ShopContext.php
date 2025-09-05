<?php
namespace App\Domain\Ecommerce;
use App\Models\Shop;

class ShopContext {
    public function current(?int $shopId = null): ?Shop {
        if ($shopId) return Shop::find($shopId);
        // para prueba: última conectada
        return Shop::orderByDesc('id')->first();
    }
}
