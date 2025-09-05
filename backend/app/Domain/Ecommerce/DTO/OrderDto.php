<?php

namespace App\Domain\Ecommerce\DTO;

use JsonSerializable;

final class OrderDto implements JsonSerializable
{
    /** @param OrderItemDto[] $items */
    public function __construct(
        public readonly string $id,
        public readonly string $orderNumber,
        public readonly string $date,
        public readonly string $customer,
        public readonly float  $total,
        public readonly string $currency,
        public readonly string $status,
        public readonly array  $items = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'order_number' => $this->orderNumber,
            'date'         => $this->date,
            'customer'     => $this->customer,
            'total'        => $this->total,
            'currency'     => $this->currency,
            'status'       => $this->status,
            'items'        => array_map(fn($i) => $i->toArray(), $this->items),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
