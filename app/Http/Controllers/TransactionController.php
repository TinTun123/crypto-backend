<?php

namespace App\Http\Controllers;

use App\Events\UserBalanceUpdated;
use App\Helpers\ImageHelper;
use App\Helpers\MyTransactionService;
use App\Models\SwapFee;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    //

    public function fetchWallet(Request $request) {

        $wallets = Wallet::with(['swappingFee.toWallet'])->get();
    
        
        return response()->json(['wallets' => $wallets]);
    }

    public function updateWallet(Request $request, Wallet $wallet) {

        $request->validate([
            'pro_fee' => ['required', 'numeric'],
            'normal_fee' => ['required', 'numeric'],
            'usd_equivalent' => ['required', 'numeric'],
            'address' => ['required', 'string'],
            'qrImage' => ['nullable', 'image'],
            'fees' => ['required', 'array'],
            'fees.*.to_wallet_id' => ['required', 'numeric'],
            'fees.*.fee' => ['required', 'numeric']
        ]);

        
        $wallet->pro_fee = $request->input('pro_fee');
        $wallet->normal_fee = $request->input('normal_fee');
        $wallet->address = $request->input('address');
        $wallet->usd_equivalent = $request->input('usd_equivalent');

        $qrFile = $request->file('qrImage');
        
        if(isset($qrFile)) {
            $wallet->QR_image_url = ImageHelper::storeImage($wallet->id, $request->file('qrImage'), 'qrImage', 'wallet');
        }


        $fees = $request->fees;


        foreach($fees as $fee) {
           
            $swapFee = SwapFee::where('from_wallet_id', $wallet->id)->where('to_wallet_id', $fee['to_wallet_id'])->first();

            if (isset($swapFee)) {

                $swapFee->fee = $fee['fee'];

                $swapFee->save();

            }
        }

        $wallet->save();

        return response()->json(['message' => 'all fine'], 200);

    }

    public function topUpCoin(Request $request, Wallet $wallet, User $user) {

        $request->validate([
            'amount' => ['required', 'numeric']
        ]);

        $userBalance = UserBalance::where('user_id', $user->id)
        ->where('wallet_id', $wallet->id)
        ->first();

        if (!isset($userBalance)) {
            return response()->json(['message' => 'User balance not found'], 404);
        }

        $userBalance->balance_amount += $request->input('amount');
        $userBalance->save();

        event(new UserBalanceUpdated());

        return response()->json(['message' => "User wallet topup to $userBalance->balance_amount", 'balance' => $userBalance], 200);

    }

    public function fetchBalance(Request $request, User $user) {

        $balance = UserBalance::where('user_id', $user->id)->with(['wallet'])->get();

        return response()->json(['message' => 'all find', 'balance' => $balance], 200);
        
    }

    public function sentCoin(Request $request) {
        
        $request->validate([
            'amount' => ['required', 'numeric', 'regex:/^\d*\.?\d*$/'],
            'feeType' => ['required'],
            'note' => ['required', 'string'],
            'address' => ['required', 'string'],
            'walletId' => ['required', 'exists:wallets,id'],
            'userId' => ['required', 'exists:users,id']
        ]);

        $transcationHelper = new MyTransactionService();

        $transcationHelper = $transcationHelper->initiateTransaction([
            'amount' => $request->input('amount'),
            'feeType' => $request->input('feeType'),
            'address' => $request->input('address'),
            'note' => $request->input('note'),
            'walletId' => $request->input('walletId'),
            'userId' => $request->input('userId')
        ]);


        if (!$transcationHelper['success']) {
            return response()->json(['message' => $transcationHelper['message']], $transcationHelper['code']);
        } else {
            return response()->json(['message' => $transcationHelper['message'], 'balance' => $transcationHelper['balance']], $transcationHelper['code']);
        }   

    }

    public function fetchTran(Request $request, User $user) {
        $transcation = Transaction::with('wallet')->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    
        if (isset($transcation)) {

            return response()->json(['transaction' => $transcation], 200);

        } else {

            return response()->json(['transaction' => []], 200);

        }
    }

    public function adminFetchTran(Request $request) {
        $transcation = Transaction::with(['user', 'wallet'])
        ->orderBy('created_at', 'desc')
        ->get();


        Log::info('transaction', [
            $transcation
        ]);

        return response()->json(['alltransaction' => $transcation], 200);
    }
}
