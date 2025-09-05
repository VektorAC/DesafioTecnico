<?php

namespace App\Domain\Ecommerce;

use Illuminate\Support\Collection;

interface EcommerceProvider {
    /** @return array{data: array<int,array>, total:int} */
    public function getProducts(array $params = []): array;
    /** @return array{data: array<int,array>, total:int} */
    public function getOrders(array $params = []): array;
    public function getProductsForExport(array $params = []): Collection;
    public function getOrdersForExport(array $params = []): Collection;
}

