<?php

namespace App\Http\Controllers;

use App\Domain\Ecommerce\EcommerceProvider;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Domain\Ecommerce\{EcommerceProviderFactory, ShopContext};

class OrderController extends Controller
{
    public function __construct(private ShopContext $ctx, private EcommerceProviderFactory $factory) {}

    public function index(Request $req)
    {
        $params = $req->only('page','per_page','from','to');
        $shop = $this->ctx->current($req->integer('shop_id'));
        $provider = $this->factory->forShop($shop);
        $res = $provider->getOrders($params);
        return response()->json($res);
    }

    public function export(Request $req, string $format)
    {
        $shop = $this->ctx->current($req->integer('shop_id'));
        $provider = $this->factory->forShop($shop);
        $rows = $provider->getOrdersForExport($req->all());

        if ($format === 'csv') {
            return $this->csvDownload('orders.csv', $rows->toArray());
        }

        if ($format === 'xlsx') {
            if (! class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
                return response()->json(['message'=>'Excel no instalado. Ejecuta: composer require maatwebsite/excel'], 501);
            }
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ArrayExport($rows->toArray()), 'orders.xlsx'
            );
        }

        return response()->json(['message'=>'Formato no soportado'], 400);
    }

    private function csvDownload(string $filename, array $rows): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if (empty($rows)) { fclose($out); return; }
            fputcsv($out, array_keys($rows[0])); // header
            foreach ($rows as $r) fputcsv($out, $r);
            fclose($out);
        }, $filename, $headers);
    }
}
