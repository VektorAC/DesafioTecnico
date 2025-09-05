<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Domain\Ecommerce\ShopContext;
use App\Domain\Ecommerce\EcommerceProviderFactory;

class MetricsController extends Controller
{
    public function __construct(
        private ShopContext $ctx,
        private EcommerceProviderFactory $factory
    ) {}

    public function index(Request $req)
    {
        $req->validate([
            'shop_id' => 'nullable|integer',
            'from'    => 'nullable|date',
            'to'      => 'nullable|date',
        ]);

        $from = Carbon::parse($req->input('from', now()->subMonths(6)->startOfMonth()));
        $to   = Carbon::parse($req->input('to',   now()->endOfDay()));

        if ($from->gt($to)) {
            return response()->json(['message'=>'Parámetros de fecha inválidos'], 422);
        }

        $shop = $this->ctx->current($req->integer('shop_id'));
        $provider = $this->factory->forShop($shop);

        $orders = $provider->getOrders([
            'per_page' => 100000, 'page'=>1,
            'from' => $from->toIso8601String(),
            'to'   => $to->toIso8601String(),
        ])['data'] ?? [];

        $monthly = [];
        $status  = [];
        $top     = [];
        $grandTotal = 0.0;
        $orderCount = 0;

        foreach ($orders as $o) {
            $orderCount++;
            $total = (float)($o['total'] ?? 0);
            $grandTotal += $total;
            $mkey = Carbon::parse($o['date'] ?? $to)->format('Y-m');
            if (!isset($monthly[$mkey])) $monthly[$mkey] = ['month'=>$mkey, 'orders'=>0, 'total'=>0.0];
            $monthly[$mkey]['orders'] += 1;
            $monthly[$mkey]['total']  += $total;
            $st = (string)($o['status'] ?? 'unknown');
            $status[$st] = ($status[$st] ?? 0) + 1;

            foreach ($o['items'] ?? [] as $it) {
                $sku = (string)($it['sku'] ?? '');
                $key = $sku !== '' ? $sku : md5(($it['title'] ?? '') . '|'.$sku);
                $qty = (int)($it['qty'] ?? 0);
                $price = (float)($it['price'] ?? 0);
                if (!isset($top[$key])) {
                    $top[$key] = [
                        'sku'    => $sku,
                        'title'  => (string)($it['title'] ?? ''),
                        'qty'    => 0,
                        'revenue'=> 0.0,
                    ];
                }
                $top[$key]['qty']     += $qty;
                $top[$key]['revenue'] += $qty * $price;
            }
        }

        $cursor = $from->copy()->startOfMonth();
        $end    = $to->copy()->startOfMonth();
        $filled = [];
        while ($cursor->lte($end)) {
            $mk = $cursor->format('Y-m');
            $filled[] = $monthly[$mk] ?? ['month'=>$mk, 'orders'=>0, 'total'=>0.0];
            $cursor->addMonth();
        }

        usort($top, fn($a,$b) => $b['qty'] <=> $a['qty']);
        $topN = array_slice(array_values($top), 0, 10);

        $aov = $orderCount > 0 ? $grandTotal / $orderCount : 0.0;

        return response()->json([
            'range' => ['from'=>$from->toDateString(), 'to'=>$to->toDateString()],
            'kpis'  => [
                'orders' => $orderCount,
                'revenue'=> $grandTotal,
                'aov'    => $aov,
            ],
            'monthly_sales' => $filled,
            'top_products'  => $topN,
            'status_breakdown' => $status,
            'currency' => $orders[0]['currency'] ?? 'CLP',
        ]);
    }
}
