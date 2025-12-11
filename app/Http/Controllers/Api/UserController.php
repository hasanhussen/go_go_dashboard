<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Store;
use App\Traits\HasImageUpload;
use App\Models\User;
use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class UserController extends Controller
{
    use HasImageUpload;
   public function register (RegisterRequest $request){
    
    $data = $request->validated();
    unset($data['fcm_token']); // نتأكد ما نحاول نحفظه بالـ users table

    $userData = User::create($data);

    $token = $userData->createToken('auth_Token')->plainTextToken;
    $userData -> api_token = $token;
    $role = $request->role ?? 'user';
    $userData->assignRole($role); 
        if ($request->fcm_token != null) {
        FcmToken::firstOrCreate([
            'user_id' => $userData->id,
            'token'   => $request->fcm_token,
        ]);
    }
    event(new Registered($userData));
    $user = new UserResource($userData);
    return response()->json($user);
   }

  public function login(LoginRequest $request)
{
    // البحث بين الكل، حتى المحذوفين ناعمًا
    $userData = User::withTrashed()
        ->where('email', $request->email)
        ->first();

    // تحقق من وجود المستخدم
    if (! $userData) {
        return response()->json([
            'error' => 'invalid email or password'
        ], 401);
    }

    // تحقق من كلمة المرور يدويًا
    if (! Hash::check($request->password, $userData->password)) {
        return response()->json([
            'error' => 'invalid email or password'
        ], 401);
    }

    // ✅ إذا كان الحساب محذوف ناعمًا
    if ($userData->trashed()) {
        // ما تعمل تسجيل دخول فعلي، بس رجّع الحالة
        return response()->json([
            'error' => 'account_deleted',
            'deleted_at' => $userData->deleted_at,
        ], 403);
    }
    
// if($userData->fcm_token !== $request->fcm_token && $request->fcm_token != null){
//         $userData->fcm_token = $request->fcm_token;
//         $userData->save();
//     }

if($request->fcm_token != null){
        FcmToken::firstOrCreate([
            'user_id' => $userData->id,
            'token'   => $request->fcm_token,
        ]);

    }
    // ✅ حساب فعّال — أنشئ التوكن وسجّل دخول
    $token = $userData->createToken('auth_Token')->plainTextToken;
    $userData->api_token = $token;
    
    if($userData->email_verified_at == null){
        event(new Registered($userData));
    }
    

    $user = new UserResource($userData);

    return response()->json($user);
}

public function logout(Request $request){
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'error' => 'Token is invalid or expired'
        ], 401);
    }


    $user->tokens()->delete();
    // $user->update(['fcm_token' => null]);

    return response()->json(['error' => 'Logged out successfully']);
}



//    public function saveFcmToken(Request $request){
//     $request->validate([
//         'fcm_token' => 'required|string',
//     ]);
//     $user_id  = Auth::user()->id;
//     $userData = User::findOrFail($user_id);
//     $userData->fcm_token = $request->fcm_token;
//     $userData->save();
//      return response()->json([
//         'error' => 'FCM token saved successfully',
//         'fcm_token' => $userData->fcm_token
//     ]);
//    }

    public function getNotifications(){
    $user  = Auth::user();
    $user_id  = Auth::user()->id;
    $notifications  = $user->notifications;
    $userData = User::findOrFail($user_id);
    $userData->open_notifications = now();
    $userData->save();
    return response()->json($notifications);
   }

    public function markAsRead($notification_id){
    $notification  = Auth::user()->notifications()->findOrFail($notification_id);
    $notification->markAsRead();
    return response()->json(['notification marked as read']);
   }

public function markAllAsRead()
{
    $user = Auth::user();

    if ($user) {
        $user->unreadNotifications->markAsRead();
        return response()->json([
            'status' => true,
            'error' => 'All notifications marked as read',
        ]);
    }

    return response()->json([
        'status' => false,
        'error' => 'User not authenticated',
    ], 401);
}



    public function getProfile (){
    $user = Auth::user();
    $userData = User::withTrashed()->find($user->id);
    $user = new UserResource($userData);
    return response()->json($user);
   }

    public function updateProfile (ProfileRequest $request){
    $user_id  = Auth::user()->id;
    $userData = User::findOrFail($user_id );
    $userData = $this->handleImageUpdate($request, $userData, 'profile_images');
    $user = new UserResource($userData);
    return response()->json($user);
   }

 
}
