<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DevTokenAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!($data['username'] === 'test' && $data['password'] === 'prueba123')) {
            return response()->json(['message' => 'Credenciales inválidas'], 422);
        }

        $user = User::firstOrCreate(
            ['email' => 'test@local.dev'],
            ['name' => 'Test', 'password' => bcrypt(Str::random(32))]
        );

        $plain = $user->createToken('dev')->plainTextToken;

        return response()->json(['token' => $plain]);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }
        return response()->noContent(); // 204
    }
}
