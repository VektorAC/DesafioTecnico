<?php

namespace App\Domain\Ecommerce\DTO;

use JsonSerializable;

final class ProductDto implements JsonSerializable
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $title,
        public readonly string  $sku,
        public readonly float   $price,
        public readonly string  $currency,
        public readonly ?string $createdAt = null,
        public readonly ?string $image     = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'sku'        => $this->sku,
            'price'      => $this->price,
            'currency'   => $this->currency,
            'created_at' => $this->createdAt,
            'image'      => $this->image,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
