<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    //

    public function fetchNoti(Request $request, User $user) {
        $notifications = $user->notifications()->whereNull('read_at')->orderBy('created_at', 'desc')->get();

        $formattedNotis = $notifications->map(function ($noti) {
            $data = json_decode($noti->data, true);
            return [
                'id' => $noti->id,
                'message' => $data['message'],
                'status' => $data['status'],
                'created_at' => $noti->created_at->diffForHumans()
            ];
        });

        $unreadCount = $formattedNotis->count();

        return response()->json(['notifications' => $formattedNotis, 'unReadCount' => $unreadCount], 200);

    }

    public function markasRead(Request $request, User $user) {
        $notifications = $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json([], 200);

    }
}
