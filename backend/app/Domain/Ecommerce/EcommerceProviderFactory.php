<?php
namespace App\Domain\Ecommerce;

use App\Models\Shop;

class EcommerceProviderFactory {
    public function forShop(?Shop $shop): EcommerceProvider {
        if (!$shop) {
            return new MockEcommerceProvider();
        }

        try {
            if ($shop->provider === 'shopify') {
                $creds = $shop->getCredentials();
                if (empty($creds['access_token'])) {
                    return new MockEcommerceProvider();
                }
                return new ShopifyProvider($shop);
            }

            if ($shop->provider === 'woo') {
                $creds = $shop->getCredentials();
                if (empty($creds['ck']) || empty($creds['cs'])) {
                    return new MockEcommerceProvider();
                }
                return new WooProvider($shop);
            }

            return new MockEcommerceProvider();
        } catch (\Throwable $e) {
            return new MockEcommerceProvider();
        }
    }
}
