<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TransactionController extends Controller
{
    public function deposit(Request $request)
{
    try {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = Auth::user();

        $currentBalance = $user->transactions()
            ->selectRaw("SUM(CASE WHEN type = 'deposit' THEN amount WHEN type = 'transfer' THEN -amount ELSE 0 END) as balance")
            ->value('balance');

        if ($currentBalance < 0) {
            return response()->json(['error' => 'Usuário com saldo negativo não pode depositar.'], 403);
        }

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => $request->amount,
        ]);

        return response()->json(['message' => 'Depósito realizado com sucesso', 'transaction' => $transaction]);
    } catch (\Throwable $e) {
        return response()->json(['error' => 'Erro: ' . $e->getMessage()], 500);
    }
}
public function transfer(Request $request)
{
    try {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $sender = Auth::user();

        if ($sender->id === (int) $request->recipient_id) {
            return response()->json(['error' => 'Você não pode transferir para si mesmo.'], 400);
        }

   
        $currentBalance = $sender->transactions()
            ->selectRaw("SUM(CASE WHEN type IN ('deposit', 'reversal') THEN amount WHEN type = 'transfer' THEN -amount ELSE 0 END) as balance")
            ->value('balance');

       
        if ($currentBalance < $request->amount) {
            return response()->json(['error' => 'Saldo insuficiente.'], 400);
        }

        DB::beginTransaction();

   
        $transactionOut = Transaction::create([
            'user_id' => $sender->id,
            'type' => 'transfer',
            'amount' => $request->amount,
            'recipient_id' => $request->recipient_id,
        ]);

        Transaction::create([
            'user_id' => $request->recipient_id,
            'type' => 'deposit',
            'amount' => $request->amount,
            'related_transaction_id' => $transactionOut->id,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Transferência realizada com sucesso',
            'transaction' => $transactionOut,
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json(['error' => 'Erro: ' . $e->getMessage()], 500);
    }
}

public function revert($id)
{
    try {
        $user = Auth::user();
        $transaction = Transaction::findOrFail($id);

        if ($transaction->user_id !== $user->id) {
            return response()->json(['error' => 'Você não pode reverter esta transação.'], 403);
        }

        if ($transaction->reverted_at) {
            return response()->json(['error' => 'Esta transação já foi revertida.'], 400);
        }

       
        if ($transaction->type === 'deposit') {
        
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'reversal',
                'amount' => -$transaction->amount,
                'related_transaction_id' => $transaction->id,
            ]);
        }

        elseif ($transaction->type === 'transfer') {
          

            $recipientTransaction = Transaction::where('related_transaction_id', $transaction->id)->first();

            if (!$recipientTransaction) {
                return response()->json(['error' => 'Transação de destino não encontrada.'], 404);
            }

            $recipient = User::find($recipientTransaction->user_id);

            $recipientBalance = $recipient->transactions()
                ->selectRaw("SUM(CASE WHEN type IN ('deposit', 'transfer') THEN amount WHEN type = 'reversal' THEN -amount ELSE 0 END) as balance")
                ->value('balance');

            if ($recipientBalance < $transaction->amount) {
                return response()->json(['error' => 'Usuário de destino não tem saldo suficiente para reverter.'], 400);
            }

          
            Transaction::create([
                'user_id' => $recipient->id,
                'type' => 'reversal',
                'amount' => -$transaction->amount,
                'related_transaction_id' => $transaction->id,
            ]);

        
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'reversal',
                'amount' => $transaction->amount,
                'related_transaction_id' => $transaction->id,
            ]);
        }

        $transaction->reverted_at = now();
        $transaction->save();

        return response()->json(['message' => 'Transação revertida com sucesso.']);
    } catch (\Throwable $e) {
        return response()->json(['error' => 'Erro: ' . $e->getMessage()], 500);
    }
}


}
