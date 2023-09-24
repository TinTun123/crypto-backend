<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserBalance;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;

class UserObserver
{

    public $afterCommit = true;
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
        $walletTypes = Wallet::pluck('id')->toArray();

        

        foreach($walletTypes as $walletType) {

            UserBalance::create([
                'user_id' => $user->id,
                'wallet_id' => $walletType,
                'balance_amount' => 0
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
