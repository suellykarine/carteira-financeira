<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_deposit(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/deposit', [
                             'amount' => 50
                         ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'transaction' => [
                         'id',
                         'user_id',
                         'type',
                         'amount',
                         'created_at',
                         'updated_at'
                     ]
                 ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => 50
        ]);
    }

   public function test_user_can_transfer()
    {
        
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

       
        $token = $sender->createToken('auth_token')->plainTextToken;

       
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/deposit', [
                'amount' => 200
            ])
            ->assertStatus(200);

      
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/transfer', [
                'amount' => 100,
                'recipient_id' => $recipient->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'transaction' => [
                    'user_id',
                    'type',
                    'amount',
                    'recipient_id',
                    'updated_at',
                    'created_at',
                    'id',
                ],
            ]);
    }

    public function test_user_can_revert_transaction()
{
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $token = $sender->createToken('auth_token')->plainTextToken;

 
    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/deposit', [
            'amount' => 200
        ])
        ->assertStatus(200);

   
    $transferResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/transfer', [
            'amount' => 100,
            'recipient_id' => $recipient->id
        ])
        ->assertStatus(200)
        ->json();

    $transactionId = $transferResponse['transaction']['id'];

   
    $revertResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson("/api/transactions/{$transactionId}/revert");

    $revertResponse->assertStatus(200)
        ->assertJson([
            'message' => 'Transação revertida com sucesso.'
        ]);
}

}
