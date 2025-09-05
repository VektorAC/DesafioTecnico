<?php
namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function shopifyConnect(Request $r) {
        $shop = $r->query('shop');
        abort_unless($shop && str_ends_with($shop, '.myshopify.com'), 400, 'shop inválido');

        $state = Str::random(32);
        $r->session()->put('shop_oauth_state', $state);

        $params = [
            'client_id'   => env('SHOPIFY_API_KEY'),
            'scope'       => env('SHOPIFY_SCOPES','read_products,read_orders'),
            'redirect_uri'=> env('SHOPIFY_REDIRECT_URI', env('APP_URL').'/shops/callback'),
            'state'       => $state,
        ];
        return redirect()->away("https://{$shop}/admin/oauth/authorize?".http_build_query($params));
    }


    public function shopifyCallback(Request $r) {
        $data = $r->query();
        $hmac = $data['hmac'] ?? '';
        unset($data['hmac'], $data['signature']);
        ksort($data);
        $computed = hash_hmac('sha256', urldecode(http_build_query($data)), env('SHOPIFY_API_SECRET'));
        abort_unless(hash_equals($hmac, $computed), 401, 'HMAC inválido');

        abort_unless($r->query('state') === $r->session()->pull('shop_oauth_state'), 401, 'state inválido');

        $shopDomain = $r->query('shop');
        $code = $r->query('code');

        $resp = \Http::asJson()->post("https://{$shopDomain}/admin/oauth/access_token", [
            'client_id'     => env('SHOPIFY_API_KEY'),
            'client_secret' => env('SHOPIFY_API_SECRET'),
            'code'          => $code,
        ]);
        abort_unless($resp->ok(), 400, 'No se pudo obtener token');

        $token = $resp->json('access_token');

        $shop = new Shop([
            'user_id'  => auth()->id() ?? 1,
            'provider' => 'shopify',
            'domain'   => $shopDomain,
            'scopes'   => env('SHOPIFY_SCOPES'),
            'status'   => 'connected',
        ]);
        $shop->setCredentials(['access_token' => $token]);
        $shop->save();

        return redirect('/products');
    }

    public function wooConnect(Request $r) {
        $r->validate([
            'domain' => 'required|url',
            'ck'     => 'required|string',
            'cs'     => 'required|string',
        ]);

        $shop = new Shop([
            'user_id'  => auth()->id() ?? 1,
            'provider' => 'woo',
            'domain'   => rtrim($r->input('domain'), '/'),
            'status'   => 'connected',
        ]);
        $shop->setCredentials(['ck' => $r->input('ck'), 'cs' => $r->input('cs')]);
        $shop->save();

        return response()->json(['ok' => true, 'shop_id' => $shop->id]);
    }
    public function index(Request $r) {
        $shops = Shop::orderByDesc('id')->get(['id','provider','domain','status','created_at']);
        return response()->json(['data' => $shops]);
    }
}
