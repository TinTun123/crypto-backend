<?php

namespace App\Observers;

use App\Models\UserBalance;
use Illuminate\Support\Facades\Log;

class UserBalanceObserver
{
    /**
     * Handle the UserBalance "created" event.
     */
    public function created(UserBalance $userBalance): void
    {
        //
        
    }

    /**
     * Handle the UserBalance "updated" event.
     */
    public function updated(UserBalance $userBalance): void
    {
        //

        $user = $userBalance->user;
        $totalUSD = $user->balance->sum(function ($balance) {

            return $balance->balance_amount * $balance->wallet->usd_equivalent;

        });


        $user->update(['total_usd' => $totalUSD]);
    }

    /**
     * Handle the UserBalance "deleted" event.
     */
    public function deleted(UserBalance $userBalance): void
    {
        //
    }

    /**
     * Handle the UserBalance "restored" event.
     */
    public function restored(UserBalance $userBalance): void
    {
        //
    }

    /**
     * Handle the UserBalance "force deleted" event.
     */
    public function forceDeleted(UserBalance $userBalance): void
    {
        //
    }
}
