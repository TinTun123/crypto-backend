<?php

namespace App\Http\Controllers;

use App\Events\UserBalanceUpdated;
use App\Helpers\ImageHelper;
use App\Helpers\MyTransactionService;
use App\Models\CoinswapTransaction;
use App\Models\SwapFee;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Notifications\NewTransactionNotification;
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

        $transcation = Transaction::create([
            'user_id' => $userBalance->user_id,
            'wallet_id' => $userBalance->wallet_id,
            'action' => 'Deposite',
            'note' => '',
            'amount' => $userBalance->balance_amount,
            'address' => $wallet->address,
            'fee' => 0,
            'fee_type' => 'normal',
            'state' => 'approved'
        ]);

        $transactionHelper = new MyTransactionService();
        $amount = $request->input('amount');
        $walletAbbr = $transactionHelper->getWalletAbbr($wallet->wallet_type);
        $data = [
            'message' => "Successful Received $amount $walletAbbr.",
            'status' => 'Successful'
        ];

        $user->notify(new NewTransactionNotification($data));

        return response()->json(['message' => "User wallet topup to $userBalance->balance_amount", 'balance' => $userBalance], 200);

    }

    public function initSwap(Request $request, User $user) {
        $request->validate([
            'from_wallet_id' => ['required', 'exists:wallets,id'],
            'to_wallet_id' => ['required', 'exists:wallets,id'],
            'amount' => ['required', 'numeric']
        ]);


        $fromBalance = $user->balance()->where('wallet_id', $request->input('from_wallet_id'))->first();
        $toBalance = $user->balance()->where('wallet_id', $request->input('to_wallet_id'))->first();

        $swapFee = SwapFee::where('from_wallet_id', $request->input('from_wallet_id'))
        ->where('to_wallet_id', $request->input('to_wallet_id'))->first();
        $amount = $request->input('amount');
        $fee = bcmul($swapFee->fee, 0.01, 10);
        $totalAmount = bcadd($fee, $amount, 10);

        $fromwallet = $fromBalance->wallet;
        $towallet = $toBalance->wallet;

        

        if (bccomp($totalAmount, $fromBalance->balance_amount, 10) !== -1) {
            return response()->json(['message' => 'Insufficient balance'], 200);
        }

        $remainbalance = bcsub($fromBalance->balance_amount, $totalAmount, 10);
        $divideRrsult = bcdiv($fromwallet->usd_equivalent, $towallet->usd_equivalent, 10);
        $addBalance = bcmul($divideRrsult, $amount, 10);

        $fromBalance->balance_amount = $remainbalance;
        // $toBalance->balance_amount = $addBalance;

        $fromBalance->save();
        // $toBalance->save();
        
        $swapTranscation = CoinswapTransaction::create([
            'user_id' => $user->id,
            'from_wallet_id' => $request->input('from_wallet_id'),
            'to_wallet_id' => $request->input('to_wallet_id'),
            'transfer_amount' => $totalAmount,
            'received_amount' => $addBalance,
        ]);



        $tranService = new MyTransactionService();

        $fromwalletAbbr = $tranService->getWalletAbbr($fromwallet->wallet_type);
        $towalletAbbr = $tranService->getWalletAbbr($towallet->wallet_type);
        $formattedAmount =  number_format($amount);
        $formattedBalance = number_format($addBalance);
        $data = [
            'message' => "Pending exchange request from  $formattedAmount $fromwalletAbbr to $formattedBalance $towalletAbbr.",
            'status' => 'pending'
        ];

        $user->notify(new NewTransactionNotification($data));


        return response()->json(['message' => 'Transaction record under processing.', 'fromBalance' => $fromBalance, 'toBalance' => $toBalance], 200);
    }

    public function fetchSwapTran(Request $request, User $user) {

        $swapHis = CoinswapTransaction::with(['user', 'fromWallet', 'toWallet'])->where('user_id', $user->id)->paginate(5);

        return response()->json(['message' => 'all fine', 'swaptrans' => $swapHis], 200);
    }

    public function adminFetchSwapTran(Request $request) {
        $swapHis = CoinswapTransaction::latest('created_at')->with(['user', 'fromWallet', 'toWallet'])->paginate(5);

        return response()->json(['swaptrans' => $swapHis], 200);
    }


    public function fetchBalance(Request $request, User $user) {

        $balance = UserBalance::where('user_id', $user->id)->with(['wallet.swappingFee'])->get();

        return response()->json(['message' => 'all find', 'balance' => $balance], 200);
        
    }

    public function AdminsentCoin(Request $request) {
        
        $request->validate([
            'amount' => ['required', 'numeric', 'regex:/^\d*\.?\d*$/'],
            'note' => ['required', 'string'],
            'address' => ['required', 'string'],
            'walletId' => ['required', 'exists:wallets,id'],
            'userId' => ['required', 'exists:users,id']
        ]);
        
        $userBalance = UserBalance::where('user_id', $request->input('userId'))
        ->where('wallet_id', $request->input('walletId'))->first();

        if (bccomp($userBalance->balance_amount, $request->input('amount'), 10) === -1) {
            return response()->json(['message' => 'Insufficient balance. Transaction process failed.'], 403);
        }

        $remain = bcsub($userBalance->balance_amount, $request->input('amount'), 10);

        $userBalance->balance_amount = $remain;

        $userBalance->save();

        $transcation = Transaction::create([
            'user_id' => $userBalance->user_id,
            'wallet_id' => $userBalance->wallet_id,
            'action' => 'Withdraw',
            'note' => $request->input('note'),
            'amount' => $request->input('amount'),
            'address' => $request->input('address'),
            'fee' => 0,
            'fee_type' => 'normal',
            'state' => 'approved'
        ]);
        event(new UserBalanceUpdated());

        $transactionHelper = new MyTransactionService();
        $wallet = $userBalance->wallet;
        $amount = $request->input('amount');
        $user = $userBalance->user;
        $walletAbbr = $transactionHelper->getWalletAbbr($wallet->wallet_type);

        $data = [
            'message' => "Successful Withdraw $amount $walletAbbr.",
            'status' => 'Successful'
        ];

        $user->notify(new NewTransactionNotification($data));

        return response()->json(['balance' => $userBalance, 'transaction' => $transcation, 'message' => "Transaction successful"], 200);

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
            'userId' => $request->input('userId'),
        ]);

        if (!$transcationHelper['success']) {
            return response()->json(['message' => $transcationHelper['message']], $transcationHelper['code']);
        } else {
            return response()->json(['message' => $transcationHelper['message'], 'balance' => $transcationHelper['balance']], $transcationHelper['code']);
        }   

    }

    public function approTran(Request $request, Transaction $transaction) {
        $transaction->state = 'approved';
        $transaction->save();

        $transaction['user'] = $transaction->user;
        $transaction['wallet'] = $transaction->wallet;

        $amount =   number_format($transaction->amount, 4);
        $transactionService = new MyTransactionService();

        $wallettype = $transaction['wallet']->wallet_type;
        $walletAbbr = $transactionService->getWalletAbbr($wallettype);

        $data = [
            'message' => "Successful transaction of $amount $walletAbbr.",
            'status' => 'Successful'
        ];

        $transaction['user']->notify(new NewTransactionNotification($data));

        return response()->json(['transaction' => $transaction], 200);
    }

    public function rollbackTran(Request $request, Transaction $transaction) {

        $userBalance = UserBalance::where('user_id', $transaction->user_id)
        ->where('wallet_id', $transaction->wallet_id)
        ->first();

        $totalrefund = bcadd($transaction->fee, $transaction->amount, 10);

        $userBalance->balance_amount += $totalrefund;

      

        $userBalance->save();

        $transaction->state = 'denied';

        $transaction->save();

        $transaction['user'] = $transaction->user;
        $transaction['wallet'] = $transaction->wallet;

        $amount =   number_format($transaction->amount, 4);
        $transactionService = new MyTransactionService();
        $wallettype = $transaction['wallet']->wallet_type;
        $walletAbbr = $transactionService->getWalletAbbr($wallettype);

        $data = [
            'message' => "Cancelled transaction of $amount $walletAbbr.",
            'status' => 'denied'
        ];

        $transaction['user']->notify(new NewTransactionNotification($data));
        
        return response()->json(['transaction' => $transaction], 200);
    }

    public function denySwap(Request $request, CoinswapTransaction $coinswaptransaction) {
        $user = $coinswaptransaction->user;
        $userBalance = $user->balance->where('wallet_id', $coinswaptransaction->from_wallet_id)->first();

        $transfer_amount = $coinswaptransaction->transfer_amount;
        $receive_amount = $coinswaptransaction->received_amount;
        $upamount = bcadd($transfer_amount, $userBalance->balance_amount, 10);

        $userBalance->balance_amount = $upamount;

        $userBalance->save();

        $coinswaptransaction->status = 'denied';
        $coinswaptransaction->save();

        $coinswaptransaction['from_wallet'] = $coinswaptransaction->fromWallet;
        $coinswaptransaction['to_wallet'] = $coinswaptransaction->toWallet;
        $coinswaptransaction['user'] = $coinswaptransaction->user();

        $tranService = new MyTransactionService();

        $fromwalletAbbr = $tranService->getWalletAbbr($coinswaptransaction['from_wallet']->wallet_type);
        $towalletAbbr = $tranService->getWalletAbbr($coinswaptransaction['to_wallet']->wallet_type);

        $data = [
            'message' => "Cancelled coin exchange request from $transfer_amount $fromwalletAbbr to $receive_amount $towalletAbbr",
            'status' => 'denied'
        ];

        $user->notify(new NewTransactionNotification($data));

        return response()->json(['message' => "$user->firstName $user->lastName's exchange request was denied", 'transaction' => $coinswaptransaction], 200);
    }

    public function approSwap(Request $request, CoinswapTransaction $coinswaptransaction) {
        $user = $coinswaptransaction->user;
        $userBalance = $user->balance->where('wallet_id', $coinswaptransaction->to_wallet_id)->first();

        $receive_amount = $coinswaptransaction->received_amount;
        $transfer_amount = $coinswaptransaction->transfer_amount;

        $updateAmount = bcadd($receive_amount, $userBalance->balance_amount, 10);

        $userBalance->balance_amount = $updateAmount;

        $userBalance->save();

        $coinswaptransaction->status = 'approve';
        $coinswaptransaction->save();

        $coinswaptransaction['from_wallet'] = $coinswaptransaction->fromWallet;
        $coinswaptransaction['to_wallet'] = $coinswaptransaction->toWallet;
        $coinswaptransaction['user'] = $coinswaptransaction->user();

        $tranService = new MyTransactionService();
        $fromwalletAbbr = $tranService->getWalletAbbr($coinswaptransaction['from_wallet']->wallet_type);
        $towalletAbbr = $tranService->getWalletAbbr($coinswaptransaction['to_wallet']->wallet_type);

        $data = [
            'message' => "Successful coin exchange from $transfer_amount $fromwalletAbbr to $receive_amount $towalletAbbr.",
            'status' => 'Successful'
        ];

        $user->notify(new NewTransactionNotification($data));

        return response()->json(['message' => "$user->firstName $user->lastName's exchange request was approved", 'transaction' => $coinswaptransaction], 200); 
    }



    public function fetchTran(Request $request, User $user) {

        $transcation = Transaction::with('wallet')->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->take(4)
        ->get()
        ->map(function ($transcation) {
            $transcation->formatted_date = $transcation->created_at->diffForHumans();

            return $transcation;

        });
    
        if (isset($transcation)) {

            return response()->json(['transaction' => $transcation], 200);

        } else {

            return response()->json(['transaction' => []], 200);

        }
    }

    public function adminFetchUserTran(Request $request, User $user) {


        $transcation = Transaction::with('wallet')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

        $transcation->each(function ($tran) {
            $tran->formatted_date = $tran->created_at->diffForHumans();
        });

        return response()->json(['transaction' => $transcation], 200);

    }

    public function fetchCoinTran(Request $request, Wallet $wallet, User $user) {

        $transcations = Transaction::with('wallet')->where('user_id', $user->id)
        ->where('wallet_id', $wallet->id)
        ->orderBy('created_at', 'desc')
        ->paginate(3);

        $transcations->each(function ($transcation) {
            $transcation->formatted_date = $transcation->created_at->diffForHumans();
        });

        return response()->json(['message' => 'all fine', 'transcations' => $transcations], 200);
    }

    public function adminFetchTran(Request $request) {
        $transcation = Transaction::with(['user', 'wallet'])
        ->orderBy('created_at', 'desc')
        ->paginate(5);

        return response()->json(['alltransaction' => $transcation], 200);
    }
}
