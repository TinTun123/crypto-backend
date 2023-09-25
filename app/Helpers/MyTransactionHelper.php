<?php

namespace App\Helpers;

use App\Events\UserBalanceUpdated;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MyTransactionService  {
    
    public function initiateTransaction(array $requestData) {

        $userBalance = UserBalance::with('user', 'wallet')
        ->where('user_id', $requestData['userId'])
        ->where('wallet_id', $requestData['walletId'])
        ->first();


        if (!$userBalance) {
            throw new \RuntimeException('User balance not found');
        }

        if ($this->isSuspend($userBalance->user)) {
            return ['message' => 'User account was under suspension', 'code' => 403, 'success' => false];
        }



        $totalAmount = 0;

        if ($requestData['feeType'] === 'pro') {

            $totalAmount = $this->getTotal($requestData['amount'], $userBalance->wallet->pro_fee);
            $totalFee = bcmul($requestData['amount'], ($userBalance->wallet->pro_fee * 0.01), 10);

        } else {

            $totalAmount = $this->getTotal($requestData['amount'], $userBalance->wallet->normal_fee);
            $totalFee = bcmul($requestData['amount'], ($userBalance->wallet->normal_fee * 0.01), 10);
        }


        if (!$this->isSufficient($totalAmount, $userBalance->balance_amount)) {
            return ['message' => 'Insufficient balance', 'code' => 403, 'success' => false];
        }

        $userBalance->balance_amount = $this->getRemaining($userBalance->balance_amount, $totalAmount);

        $userBalance->save();

        $transcation = Transaction::create([
            'user_id' => $userBalance->user->id,
            'wallet_id' => $userBalance->wallet->id,
            'action' => 'Withdraw',
            'note' => $requestData['note'],
            'amount' => $requestData['amount'],
            'address' => $requestData['address'],
            'fee' => $totalFee,
            'fee_type' => $requestData['feeType']
        ]);

        event(new UserBalanceUpdated());

        return ['message' => 'Transcation have been recorded and start processing.', 'code' => 200, 'success' => true, 'balance' => $userBalance];

    }
    private function isSuspend(User $user) {

        if (!$user->status) {
            return true;
        } else {
            return false;
        }
    }

    private function getTotal($amount, $fee) {
        $feeamount = bcmul($amount, $fee * 0.01, 10);
        $totalAmount = bcadd($amount, $feeamount, 10);

        return $totalAmount;
    }

    private function isSufficient($totalAmount, $balance) {

        $isSufficient = bccomp($balance, $totalAmount, 10);

        if ($isSufficient === -1) {
            return false;
        } else {
            return true;
        }
    }

    private function getRemaining($balance, $totalAmount) {

        $remain = bcsub($balance, $totalAmount, 10);


        return $remain;
    }
}