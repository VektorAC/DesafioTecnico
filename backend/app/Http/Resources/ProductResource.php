<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Domain\Ecommerce\ProductDto;

class ProductResource extends JsonResource {
    /** @var ProductDto */
    public $resource;
    public function toArray($request): array {
        return [
            'id'=>$this->resource->id,
            'title'=>$this->resource->title,
            'sku'=>$this->resource->sku,
            'price'=>$this->resource->price,
            'currency'=>$this->resource->currency,
            'created_at'=>$this->resource->createdAt,
            'image'=>$this->resource->image,
        ];
    }
}
