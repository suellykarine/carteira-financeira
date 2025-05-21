<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_transfer_if_balance_is_insufficient()
    {
   
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

      
        $token = $sender->createToken('auth_token')->plainTextToken;

      
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/transfer', [
                             'recipient_id' => $recipient->id,
                             'amount' => 100,
                         ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Saldo insuficiente.'
        ]);
    }
}
