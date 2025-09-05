<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HardcodedAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // credenciales fijas
        if (!($data['username'] === 'test' && $data['password'] === 'prueba123')) {
            return response()->json(['message' => 'Credenciales inválidas'], 422);
        }

        // obtiene/crea usuario “test”
        $user = User::firstOrCreate(
            ['email' => 'test@local.dev'],
            ['name' => 'Test', 'password' => bcrypt(Str::random(32))]
        );

        auth()->login($user);          // sesión guard web
        $request->session()->regenerate();

        return response()->json(['ok' => true]);
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->noContent();
    }
}
