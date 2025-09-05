<?php
namespace App\Domain\Ecommerce;
use App\Models\Shop;

class EcommerceProviderFactory {
    public function forShop(?Shop $shop): EcommerceProvider {
        if (!$shop) return new MockEcommerceProvider();
        return match ($shop->provider) {
            'shopify' => new ShopifyProvider($shop),
            'woo'     => new WooProvider($shop),
            default   => new MockEcommerceProvider(),
        };
    }
}
