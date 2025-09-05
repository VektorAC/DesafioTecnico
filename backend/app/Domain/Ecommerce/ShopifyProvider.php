<?php
namespace App\Domain\Ecommerce;

use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ShopifyProvider implements EcommerceProvider
{
    public function __construct(private Shop $shop) {}

    private function client() {
        $token = $this->shop->getCredentials()['access_token'] ?? '';
        return Http::withHeaders(['X-Shopify-Access-Token' => $token]);
    }

    private function base(): string {
        $ver = env('SHOPIFY_API_VERSION','2025-07');
        return "https://{$this->shop->domain}/admin/api/{$ver}";
    }

    public function getProducts(array $params = []): array
    {
        $limit = min(max((int)($params['per_page'] ?? 10), 1), 250);
        $page  = max((int)($params['page'] ?? 1), 1);
        $search= trim($params['search'] ?? '');

        $resp = $this->client()->get($this->base()."/products.json", ['limit'=>250]);
        $items = collect($resp->json('products') ?? []);
        if ($search !== '') {
            $s = Str::lower($search);
            $items = $items->filter(fn($p) =>
                Str::contains(Str::lower($p['title'] ?? ''), $s) ||
                collect($p['variants'] ?? [])->contains(fn($v) => Str::contains(Str::lower($v['sku'] ?? ''), $s))
            );
        }
        $total = $items->count();
        $data  = $items->forPage($page, $limit)->values()->map(function ($p) {
            $v = $p['variants'][0] ?? [];
            return [
                'id'        => (string)($p['id'] ?? ''),
                'title'     => $p['title'] ?? '',
                'sku'       => $v['sku'] ?? '',
                'price'     => (float)($v['price'] ?? 0),
                'currency'  => $v['presentment_prices'][0]['price']['currency_code'] ?? 'CLP',
                'image'     => $p['image']['src'] ?? null,
                'created_at'=> $p['created_at'] ?? null,
            ];
        })->all();

        return compact('data','total');
    }

    public function getOrders(array $params = []): array
    {
        $limit = min(max((int)($params['per_page'] ?? 10), 1), 250);
        $page  = max((int)($params['page'] ?? 1), 1);
        $from  = Carbon::parse($params['from'] ?? now()->subDays(30))->toIso8601String();
        $to    = Carbon::parse($params['to'] ?? now())->toIso8601String();

        $resp = $this->client()->get($this->base()."/orders.json", [
            'status'=>'any','created_at_min'=>$from,'created_at_max'=>$to,'limit'=>250
        ]);

        $orders = collect($resp->json('orders') ?? []);
        $total  = $orders->count();
        $data   = $orders->forPage($page, $limit)->values()->map(function ($o) {
            $fn = $o['customer']['first_name'] ?? '';
            $ln = $o['customer']['last_name'] ?? '';
            return [
                'id'           => (string)($o['id'] ?? ''),
                'order_number' => (string)($o['order_number'] ?? $o['name'] ?? ''),
                'date'         => $o['created_at'] ?? null,
                'customer'     => trim("$fn $ln") ?: '—',
                'total'        => (float)($o['total_price'] ?? 0),
                'currency'     => $o['currency'] ?? 'CLP',
                'status'       => $o['financial_status'] ?? 'pending',
                'items'        => collect($o['line_items'] ?? [])->map(fn($li) => [
                    'sku'=>$li['sku'] ?? '', 'title'=>$li['title'] ?? '', 'qty'=>$li['quantity'] ?? 0, 'price'=>(float)($li['price'] ?? 0),
                ])->all(),
            ];
        })->all();

        return compact('data','total');
    }

    public function getProductsForExport(array $params = []): \Illuminate\Support\Collection
    {
        $r = $this->getProducts([
            'per_page' => 100000,
            'page'     => 1,
            'search'   => $params['search'] ?? null,
        ]);

        return collect($r['data'])->map(fn($x) => [
            'ID'      => $x['id'],
            'Nombre'  => $x['title'],
            'SKU'     => $x['sku'],
            'Precio'  => $x['price'],
            'Moneda'  => $x['currency'],
            'Creado'  => $x['created_at'],
        ]);
    }

    public function getOrdersForExport(array $params = []): \Illuminate\Support\Collection
    {
        $r = $this->getOrders([
            'per_page' => 100000,
            'page'     => 1,
            'from'     => $params['from'] ?? null,
            'to'       => $params['to'] ?? null,
        ]);

        return collect($r['data'])->map(fn($x) => [
            'ID'      => $x['id'],
            'Orden'   => $x['order_number'],
            'Fecha'   => $x['date'],
            'Cliente' => $x['customer'],
            'Total'   => $x['total'],
            'Moneda'  => $x['currency'],
            'Estado'  => $x['status'],
        ]);
    }
}
