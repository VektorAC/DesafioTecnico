<?php

namespace App\Http\Controllers;

use App\Domain\Ecommerce\EcommerceProvider;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Resources\ProductResource;
use App\Domain\Ecommerce\{EcommerceProviderFactory, ShopContext};

class ProductController extends Controller
{
    public function __construct(
        private ShopContext $ctx,
        private EcommerceProviderFactory $factory
    ) {}

    public function index(Request $req) {
        $shop = $this->ctx->current($req->integer('shop_id'));
        $provider = $this->factory->forShop($shop);
        $res = $provider->getProducts($req->only('page','per_page','search'));
        return response()->json($res);
    }

    public function export(Request $req, string $format) {
        $shop = $this->ctx->current($req->integer('shop_id'));
        $provider = $this->factory->forShop($shop);

        $rows = $provider->getProductsForExport($req->all());
        if ($format==='csv') { /* ... tu CSV service ... */ }
        if ($format==='xlsx'){ /* ... tu Excel export ... */ }
        return response()->json(['message'=>'Formato no soportado'], 400);
    }
}
