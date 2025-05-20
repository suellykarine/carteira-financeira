<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function register(Request $request) 
{
    try {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'message' => 'Usuário criado com sucesso!',
            'user' => $user
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
    

   public function login(Request $request)
{
    try {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']), 
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTrace()
        ], 500);
    }
}

public function me(Request $request)
{
    $user = $request->user();

    $balance = $user->transactions()
        ->selectRaw("
            SUM(
                CASE
                    WHEN type = 'deposit' THEN amount
                    WHEN type = 'transfer' THEN -amount
                    WHEN type = 'reversal' THEN amount
                    ELSE 0
                END
            ) as balance
        ")
        ->value('balance') ?? 0;

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'balance' => $balance,
        'transactions' => $user->transactions()->get()
    ]);
}
public function listUsers()
{
    $users = User::all(['id', 'name', 'email']); 
    return response()->json($users);
}

}