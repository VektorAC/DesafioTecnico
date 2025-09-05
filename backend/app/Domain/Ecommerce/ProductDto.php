<?php
namespace App\Domain\Ecommerce;

final class ProductDto {
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $sku,
        public readonly float  $price,
        public readonly string $currency,
        public readonly ?string $createdAt = null,
        public readonly ?string $image = null,
    ) {}
}
