<?php

namespace App\Domain\Ecommerce;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MockEcommerceProvider implements EcommerceProvider
{
    public function getProducts(array $params = []): array
    {
        $perPage = (int)($params['per_page'] ?? 10);
        $page    = max(1, (int)($params['page'] ?? 1));
        $search  = trim($params['search'] ?? '');

        $all = collect(range(1, 120))->map(function ($i) {
            return [
                'id'        => (string)$i,
                'title'     => "Producto $i",
                'sku'       => "SKU-$i",
                'price'     => 990 + $i,
                'currency'  => config('app.currency', env('CURRENCY', 'CLP')),
                'image'     => "https://picsum.photos/seed/p$i/200/200",
                'created_at'=> now()->subDays($i)->toDateTimeString(),
            ];
        });

        if ($search !== '') {
            $all = $all->filter(fn($p) =>
                Str::contains(Str::lower($p['title']), Str::lower($search)) ||
                Str::contains(Str::lower($p['sku']), Str::lower($search))
            );
        }

        $total = $all->count();
        $data  = $all->forPage($page, $perPage)->values()->all();
        return compact('data','total');
    }

    public function getOrders(array $params = []): array
    {
        $perPage = (int)($params['per_page'] ?? 10);
        $page    = max(1, (int)($params['page'] ?? 1));
        $from    = Carbon::parse($params['from'] ?? now()->subDays(30))->startOfDay();
        $to      = Carbon::parse($params['to']   ?? now())->endOfDay();

        $all = collect(range(1, 200))->map(function ($i) {
            $date = now()->subDays($i % 40);
            return [
                'id'           => (string)$i,
                'order_number' => "ORD-$i",
                'date'         => $date->toDateTimeString(),
                'customer'     => "Cliente $i",
                'total'        => 10000 + ($i * 37),
                'currency'     => config('app.currency', env('CURRENCY', 'CLP')),
                'status'       => ['paid','pending','fulfilled','canceled'][$i % 4],
                'items'        => [
                    ['sku'=>"SKU-$i-1", 'title'=>"Producto $i-1", 'qty'=>1, 'price'=>3990],
                    ['sku'=>"SKU-$i-2", 'title'=>"Producto $i-2", 'qty'=>2, 'price'=>1990],
                ],
            ];
        })->filter(fn($o) => Carbon::parse($o['date'])->between($from, $to));

        $total = $all->count();
        $data  = $all->forPage($page, $perPage)->values()->all();
        return compact('data','total');
    }

    public function getProductsForExport(array $params = []): Collection
    {
        $res = $this->getProducts(['per_page'=>100000, 'page'=>1, 'search'=>$params['search'] ?? null]);
        return collect($res['data'])->map(fn($p) => [
            'ID'=>$p['id'], 'Nombre'=>$p['title'], 'SKU'=>$p['sku'],
            'Precio'=>$p['price'], 'Moneda'=>$p['currency'], 'Creado'=>$p['created_at'],
        ]);
    }

    public function getOrdersForExport(array $params = []): Collection
    {
        $res = $this->getOrders([
            'per_page'=>100000, 'page'=>1,
            'from'=>$params['from'] ?? null, 'to'=>$params['to'] ?? null
        ]);
        return collect($res['data'])->map(fn($o) => [
            'ID'=>$o['id'], 'Orden'=>$o['order_number'], 'Fecha'=>$o['date'],
            'Cliente'=>$o['customer'], 'Total'=>$o['total'], 'Moneda'=>$o['currency'], 'Estado'=>$o['status'],
        ]);
    }
}
