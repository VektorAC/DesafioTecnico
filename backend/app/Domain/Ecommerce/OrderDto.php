<?php
namespace App\Domain\Ecommerce;

final class OrderDto {
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly float  $total,
        public readonly string $currency,
        public readonly ?string $createdAt = null,
    ) {}
}
