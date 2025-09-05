<?php

namespace App\Domain\Ecommerce\DTO;

use JsonSerializable;

final class OrderItemDto implements JsonSerializable
{
    public function __construct(
        public readonly string $sku,
        public readonly string $title,
        public readonly int    $qty,
        public readonly float  $price, 
    ) {}

    public function toArray(): array
    {
        return [
            'sku'   => $this->sku,
            'title' => $this->title,
            'qty'   => $this->qty,
            'price' => $this->price,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
