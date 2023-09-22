<?php

namespace App\Http\Controllers;

use App\Models\ActivitiesLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivitiesLogController extends Controller
{
    //
    public function fetchLogs(Request $request, User $user) {

        Log::info('user', [
            $user
        ]);
        
        $logs = $user->activitiesLogs()->orderBy('created_at', 'desc')->get();

        
        
        return response()->json(['log' => $logs], 200);
    }
}
