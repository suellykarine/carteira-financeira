<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepositTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_deposit_if_balance_is_negative()
    {
    
        $user = User::factory()->create();

       
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'transfer',
            'amount' => 100,
            'recipient_id' => 1,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

  
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/deposit', [
                             'amount' => 50,
                         ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'Usuário com saldo negativo não pode depositar.'
        ]);
    }
}
