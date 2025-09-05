<?php
namespace App\Domain\Ecommerce;

use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WooProvider implements EcommerceProvider
{
    public function __construct(private Shop $shop) {}

    private function base(): string {
        $url = rtrim($this->shop->domain, '/');               // p.ej. https://miwp.com
        return "{$url}/wp-json/wc/v3";
    }

    private function authParams(): array {
        $c = $this->shop->getCredentials();                   // ['ck' => '...', 'cs' => '...']
        return ['consumer_key' => $c['ck'] ?? '', 'consumer_secret' => $c['cs'] ?? ''];
    }

    public function getProducts(array $params = []): array
    {
        $per  = min(max((int)($params['per_page'] ?? 10), 1), 100);
        $page = max((int)($params['page'] ?? 1), 1);
        $search = trim($params['search'] ?? '');

        $query = array_merge($this->authParams(), ['per_page'=>$per, 'page'=>$page]);
        if ($search !== '') $query['search'] = $search;

        $resp = Http::get($this->base().'/products', $query);
        $items = collect($resp->json() ?? []);
        // Woo entrega totales en headers X-WP-Total; si no, calculamos
        $total = (int)($resp->header('X-WP-Total') ?? $items->count());

        $data = $items->map(function ($p) {
            $price = (float)($p['price'] ?? 0);
            $sku   = $p['sku'] ?? '';
            return [
                'id'        => (string)($p['id'] ?? ''),
                'title'     => $p['name'] ?? '',
                'sku'       => $sku,
                'price'     => $price,
                'currency'  => $p['currency'] ?? 'CLP',
                'image'     => $p['images'][0]['src'] ?? null,
                'created_at'=> $p['date_created'] ?? null,
            ];
        })->all();

        return compact('data','total');
    }

    public function getOrders(array $params = []): array
    {
        $per  = min(max((int)($params['per_page'] ?? 10), 1), 100);
        $page = max((int)($params['page'] ?? 1), 1);
        $from = Carbon::parse($params['from'] ?? now()->subDays(30))->toIso8601ZuluString();
        $to   = Carbon::parse($params['to'] ?? now())->toIso8601ZuluString();

        $query = array_merge($this->authParams(), [
            'per_page'=>$per, 'page'=>$page,
            'after'=>$from, 'before'=>$to,
        ]);

        $resp = Http::get($this->base().'/orders', $query);
        $items = collect($resp->json() ?? []);
        $total = (int)($resp->header('X-WP-Total') ?? $items->count());

        $data = $items->map(function ($o) {
            $billing = $o['billing'] ?? [];
            return [
                'id'           => (string)($o['id'] ?? ''),
                'order_number' => (string)($o['number'] ?? $o['id'] ?? ''),
                'date'         => $o['date_created'] ?? null,
                'customer'     => trim(($billing['first_name'] ?? '').' '.($billing['last_name'] ?? '')) ?: '—',
                'total'        => (float)($o['total'] ?? 0),
                'currency'     => $o['currency'] ?? 'CLP',
                'status'       => $o['status'] ?? 'pending',
                'items'        => collect($o['line_items'] ?? [])->map(fn($li)=>[
                    'sku'=>$li['sku'] ?? '', 'title'=>$li['name'] ?? '', 'qty'=>$li['quantity'] ?? 0, 'price'=>(float)($li['price'] ?? 0),
                ])->all(),
            ];
        })->all();

        return compact('data','total');
    }

    public function getProductsForExport(array $params = []): \Illuminate\Support\Collection
    {
        $r = $this->getProducts($params + ['per_page' => 100]);
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
        $r = $this->getOrders($params + ['per_page' => 100]);
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
