<?php

namespace App\Http\Controllers\user;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Models\UserPrivate;
use App\Notifications\UserPrivateKeyNotification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    //

    public function update(UpdateProfileRequest $request, $id) {
        
        $currentUser = User::find($id);

        $data = $request->validated();

        if(auth()->check()) {

            $user = Auth::user();

        } else {
            
            return response()->json(['message' => 'Unauthenticated access.'], 403);

        }

        if (isset($data['firstName'])) {
            $currentUser->firstName = $data['firstName'];
        }

        if(isset($data['lastName'])) {
            $currentUser->lastName = $data['lastName'];
        }
        
        if(isset($data['birthday'])) {
            $currentUser->birthdat = $data['birthday'];
        }

        if(isset($data['phone_number'])) {
            $currentUser->phone_number = $data['phone_number'];
        }

        if(isset($data['address'])) {
            $currentUser->address = $data['address'];
        }

        if (isset($data['id_front'])) {
            $currentUser->id_card = ImageHelper::storeImage($user->id, $data["id_front"], 'front', 'profile');
        }

        if(isset($data['id_back'])) {
            $currentUser->id_back = ImageHelper::storeImage($user->id, $data['id_back'], 'back', 'profile');
        }

        if(isset($data['profile_image'])) {
            $currentUser->profile_img = ImageHelper::storeImage($user->id, $data["profile_image"], 'profile', 'profile');
        }

        if($user->user_level !== 1) {

            $currentUser->save();
            return response()->json(['message' => 'User information updated'], 200);

        }

        if (isset($data['status'])) {

            $currentUser->status = $data['status'];

        }

        if (isset($data['isVerified'])) {

            $currentUser->isVerified = $data['isVerified'];

        }

        if(isset($data['note'])) {
            $currentUser->note = $data['note'];
        }

        if(isset($data['email'])) {
            $currentUser->email = $data['email'];
        }

        $currentUser->save();

        return response()->json(['message' => 'User information updated.'], 200);

    }

    public function getUser(Request $request, User $user) {

        return response()->json(['user' => $user], 200);

    }

    public function assignPrivateKey(Request $request, User $user) {

        $request->validate([
            'key' => ['required', 'string']
        ]);

        $privateKey = $user->privateKey ?? new UserPrivate();
        $privateKey->private_key = $request->input('key');
        $user->privateKey()->save($privateKey);

        $user->notify(new UserPrivateKeyNotification($privateKey->private_key));
        
        return response()->json(['message' => "Private has been assigned and mailed to $user->email"], 200);
        
    }

    public function getKey(Request $request, User $user) {
        
        $key = $user->privateKey;

        if (auth()->user()->user_level === 1) {
            return response()->json(['key' => $key], 200);
        } else {
            if (isset($key) && $key->isVerified) {
                return response()->json(['key' => $key], 200);
            } else {
                return response()->json(['key' => ''], 302);
            }
        }


    }

    public function uploadPriKey(Request $request, User $user) {

        $request->validate([
            'key' => ['required', 'string']
        ]);

        $assigneKey = $user->privateKey;

        if (isset($assigneKey) && $request->input('key') === $assigneKey->private_key) {

            $assigneKey->isVerified = 1;
            $assigneKey->save();

            return response()->json(['key' => $assigneKey], 200);

        } else {
            return response()->json(['message' => 'Uploaded key does not match with assigned one.'], 402);
        }
    }

    public function toogleLock(Request $request, User $user) {

        $request->validate([
            'state' => ['required', 'string_boolean']
        ]);

        $privatekey = $user->privateKey;
        Log::info('state', [
            $request->input('state') === 'true'
        ]);
        $message = '';
        if ($request->input('state') === 'true') {
            $privatekey->state = 1;
            $message = 'User private key was unlocked.';
        } else {
            $privatekey->state = 0;
            $message = 'User private key locked.';
        }

        $privatekey->save();


        

        return response()->json(['message' => $message], 200);
        
    }

    public function updatePwd(Request $request, User $user) {

        $request->validate([
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',             // Minimum length of 8 characters (you can adjust this)
                'regex:/[A-Z]/',     // Requires at least one uppercase letter
                'regex:/[a-z]/',     // Requires at least one lowercase letter
                'regex:/[0-9]/',
            ]
        ], [
            'password.required' => 'The password field is required.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.regex' => 'The password must include at least one uppercase letter, one lowercase letter, and one number.'
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'User password has been updated'], 200);
    }

    public function fetchUsers(Request $request) {
        
        $paginator = User::with(['privateKey', 'balance.wallet'])->where('user_level', 0)->paginate(8);

        return response()->json(['users' => $paginator], 200);
    }

    public function download(Request $request) {
        $disk = 'public';

        if (Storage::exists('public/' . $request->input('file'))) {

            $pathInfo = pathinfo($request->input('file'));
            $originalFileName = $pathInfo['basename'];

            $path = Storage::disk('public')->path($request->input('file'));
            $content = file_get_contents($path);

            $contnetType = ImageHelper::getContentTypeFromExtension($pathInfo['extension']);
            
            return response($content)->header('Content-Type', $contnetType)
            ->header('Content-Disposition', 'attachment; filename="' . $originalFileName . '"');

        } else {

            return response()->json(['message' => 'image not found'], 404);
            
        }
    }

    public function deleteUser(Request $request, User $user) {

        if (auth()->user()->isAdmin()) {

            $user->delete();
            return response()->json(['message' => "$user->firstName $user->lastName was delete.", 'user' => $user], 200);
            
        } else {

            return response()->json(['message' => 'unAuthorizated access.', 'user' => []], 200);
        }
    }
}
