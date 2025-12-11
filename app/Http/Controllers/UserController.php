<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Traits\HasImageUpload;
use Spatie\Permission\Models\Role;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserNotification;

class UserController extends Controller
{

    use HasImageUpload;

    public function updateProfile(ProfileRequest $request)
    {
        $user = User::findOrFail(Auth::id());

        // استدعاء التريت
        $this->handleImageUpdate($request, $user,'profile_images');

        return response()->json(new UserResource($user));
    }


   public function register (RegisterRequest $request){
    
    $userData = User::create(
            $request->validated()
    );
    $token = $userData->createToken('auth_Token')->plainTextToken;
    $userData -> api_token = $token;
    $role = $request->role ?? 'user';
    $userData->assignRole($role);
    $user = new UserResource($userData);
    return response()->json($user);
   }

  public function login(LoginRequest $request)
{
  
    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        return back()->withErrors([
            'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
        ])->withInput();
    }
    
    $userData = User::where('email', $request->email)->firstOrFail();
    Auth::login($userData);
    return redirect()->route('home')->with('success', 'تم تسجيل الدخول بنجاح');
}


// public function logout(Request $request)
// {
//     $user = Auth::user();

//     // If using token-based auth (Sanctum) and a bearer token was provided, revoke it
//     if ($request->bearerToken() && $user) {
//         try {
//             $token = $user->currentAccessToken();
//             if ($token) {
//                 $token->delete();
//             }
//         } catch (\Throwable $e) {
//             // ignore if method not available
//         }

//         if (isset($user->api_token)) {
//             $user->api_token = null;
//             $user->save();
//         }
//     }

//     // Invalidate session for web authentication
//     Auth::logout();
//     $request->session()->invalidate();
//     $request->session()->regenerateToken();

//     if ($request->expectsJson() || $request->ajax()) {
//         return response()->json(['success' => true, 'message' => 'تم تسجيل الخروج بنجاح']);
//     }

//     return redirect()->route('home')->with('success', 'تم تسجيل الخروج بنجاح');
// }

 public function index(Request $request)
{
    $query = User::query();

    // 🔍 البحث
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%$search%")
              ->orWhere('email', 'LIKE', "%$search%");
        });
    }

    // ✅ فلترة حسب أكثر من دور
    if ($request->filled('roles')) {
        $roles = $request->roles; // array
        $query->whereHas('roles', function($q) use ($roles) {
            $q->whereIn('id', $roles);
        });
    }

    // ✅ فلترة حسب أكثر من حالة
    if ($request->filled('statuses')) {
        $statuses = $request->statuses; // array
        $query->whereIn('status', $statuses);
    }

    $users = $query->paginate(10, ['*'], 'users_page')->withQueryString();
    $trashedusers = User::onlyTrashed()->paginate(10, ['*'], 'trashedusers_page')->withQueryString();
    $roles = Role::all();

    return view('admin.users.users', compact('users','trashedusers','roles'));
}



public function create() {
    $roles = Role::all(); // لجلب جميع الأدوار لتحديد دور المستخدم
    $hideSearch = true;
    return view('admin.users.create_user', compact('roles','hideSearch'));
}

public function store(RegisterRequest $request)
{


    $user = User::create($request->validated());

    // جلب الدور من قاعدة البيانات
    $role = Role::findByName($request->role);

    // تعيين الدور باستخدام spatie
    $user->assignRole($role);
    $user->email_verified_at = now();
    $user->save();

    return redirect()->route('users')->with('success', 'User added successfully.');
}


public function show($id)
{
    $isdelivery = false;
    $user = User::with(['orders' => function($q){
        $q->orderByDesc('created_at');
    }])->findOrFail($id);

    if($user->hasRole('delivery')){
        $isdelivery = true;
        $user->load(['deliveryManOrders' => function($q){
            $q->orderByDesc('updated_at');
        }]);
    }
    $roles = Role::all();

    $hideSearch = true;
    return view('admin.users.users_show', compact('user','isdelivery','roles','hideSearch'));
}

public function accept($id)
{
    $user = User::findOrFail($id);
    $user->status = '1'; // Active
    $user->save();

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $user,
       '  تم القبول ✅',
       ' تمت الموافقة على حسابك' . $user->name,
        [
                'type' => 'user_accepted',
                'user_id' => (string) $user->id,  // لازم قيم الـ data تكون نصوص
        ]);


    Notification::send($user, new UserNotification($user, 'accept'));

    return back()->with('success', 'تم قبول المستخدم');
}

public function ban(Request $request,$id)
{
    $user = User::with('stores')->findOrFail($id);
     if($user->status == '2'){ 
        // Unban
        $user->status = '1';
        foreach($user->stores as $store){
            $store->status = '1';
            $store->save();
        }
        $user->ban_reason = null;
        $user->ban_until = null;
        $user->save();

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $user,
       'تم الغاء حظر الحساب 🔓',
       'تم الغاء حظر حسابك  ' . $user->name,
        [
                'type' => 'user_unbanned',
                'user_id' => (string) $user->id,  // لازم قيم الـ data تكون نصوص
        ]);

        Notification::send($user, new UserNotification($user, 'unbanned'));

        if($request->ajax()){
            return response()->json(['success' => true, 'message' => 'تم إلغاء الحظر']);
        }
        return back()->with('success', 'تم إلغاء الحظر');
    } else {
        // Ban
        if($request->ban_reason) {
            $days = (int)$request->input('ban_until'); 
            $user->ban_reason = $request->ban_reason;
            $user->ban_until = Carbon::now()->addDays($days); 
        } elseif($request->quick_reason) {
            $user->ban_reason = $request->quick_reason;
            $user->ban_until = $request->ban_until?? null;
        } else {
            $user->ban_reason = "لا يوجد سبب محدد";
            $user->ban_until = $request->ban_until?? null;
        }

        $user->status = '2';
            foreach($user->stores as $store){
            $store->status = '2';
            $store->save();
    }
        $user->ban_count += 1;
        $user->save();
 // 🔥 إرسال إشعار
     $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $user,
       'تم حظر حسابك 🔒',
       'تم حظر حسابك ' . $user->name . ' حتى ' . $user->ban_until . ' بسبب ' . $user->ban_reason,
        [
        'type' => 'user_banned',
        'user_id' => (string) $user->id,  // لازم قيم الـ data تكون نصوص
        ]);

        Notification::send($user, new UserNotification($user, 'banned'));
        if($request->ajax()){
            return response()->json(['success' => true, 'message' => 'تم حظر حسابك']);
        }
        return back()->with('success', 'تم حظر حسابك');
    }

}

public function destroy(Request $request,$id)
{
    $user = User::findOrFail($id);
    $auth = Auth::user();
   if ($user->hasRole('admin')) {
    if($auth->created_at == $user->created_at){
        $admins = User::role(['admin'])->get();
        if($admins <= 1){
            return back()->with('error', '❌  يجب أن يكون هناك مدير واحد على الأقل.');
        }
    }
      if ($auth->created_at > $user->created_at) {
        return back()->with('error', '❌ لا يمكنك حذف مدير أقدم منك.');
    }
   } 

        // إذا اختر الادمن سبب سريع أو كتب سبب مخصص
    if($request->delete_reason) {
        $user->delete_reason = $request->delete_reason;
    } elseif($request->quick_reason) {
        $user->delete_reason = $request->quick_reason;
    } else {
        $user->delete_reason = "لا يوجد سبب محدد";
    }

    
    $user->save(); // حفظ السبب قبل الحذف

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $user,
       'تم رفض حسابك ❌',
       'تم رفض حسابك ' . $user->name . ' بسبب ' . ($user->delete_reason ?? 'غير محدد'),
        [
        'type' => 'user_rejected',
        'user_id' => (string) $user->id,  // لازم قيم الـ data تكون نصوص
        ]);

    Notification::send($user, new UserNotification($user, 'reject'));
    $user->delete();
    return back()->with('success', 'تم نقل المستخدم إلى المحذوفات');
}


    // استرجاع متجر من المحذوفات (إذا رفض الأدمن الحذف)
    public function restoreTrasheduser($user_id)
    {
        $user = User::withTrashed()->findOrFail($user_id);
        $user->restore();
        $user->delete_reason = null; // بترجع كمنتج مخفية
        $user->save();

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $user,
       'تم استرجاع حسابك ♻️',
       'تم استرجاع حسابك ' . $user->name . ' من المحذوفات',
        [
        'type' => 'user_restored',
        'user_id' => (string) $user->id,  // لازم قيم الـ data تكون نصوص
        ]);

        Notification::send($user, new UserNotification($user, 'restored'));


        return back()->with('success', 'تم استرجاع المستخدم من المحذوفات');
        
    }

    // حذف نهائي (مخصص للأدمن فقط)
    public function forceDeleteuser($user_id)
    {
        $user = User::onlyTrashed()->findOrFail($user_id);
        $fcmTokens = $user->fcmTokens()->pluck('token')->toArray();
        if (count($fcmTokens) > 0){
        $firebase = (new Factory)->withServiceAccount(config('services.firebase.credentials'))->createMessaging();

// مثال: إزالة الاشتراك من التوبكس
$topics = ['delivery', 'owner', 'users'];
foreach ($topics as $topic) {
    foreach ($fcmTokens as $token){
        $firebase->unsubscribeFromTopic($token, $topic);
    }
    
    }

    }

      $user->forceDelete();

        return back()->with('success', 'تم الحذف النهائي للمستخدم');
    }


public function emptyTrash ()
{
    $trashedUsers = User::onlyTrashed()->get();

    foreach ($trashedUsers as $user) {

        // اذا عندو fcm_token فقط
        $fcmTokens = $user->fcmTokens()->pluck('token')->toArray();
        if (count($fcmTokens) > 0) {

            $firebase = (new Factory)
                ->withServiceAccount(config('services.firebase.credentials'))
                ->createMessaging();

            // حذف الاشتراك من جميع التوبكس
            $topics = ['delivery', 'owner', 'users'];

            foreach ($topics as $topic) {
                foreach ($fcmTokens as $token){
                $firebase->unsubscribeFromTopic($token, $topic);
                }
            }
        }

        // حذف نهائي
        $user->forceDelete();
    }

    return back()->with('success', 'تم إفراغ سلة المحذوفات بنجاح');
}

public function updateUserRole(Request $request, $id)
{
    $auth = Auth::user();       // الأدمن المتصل
    $user = User::findOrFail($id);


    $request->validate([
        'role' => 'required|string|exists:roles,name',
    ]);

    $newRole = $request->role;


    if ($auth->id == $user->id) {
         $admins = User::role(['admin'])->get();
        if($admins <= 1 && !$newRole === 'admin'){
            return back()->with('error', '❌  يجب أن يكون هناك مدير واحد على الأقل.');
        }
    }


    if ($user->hasRole('admin') && $auth->created_at > $user->created_at) {
        return back()->with('error', '❌ لا يمكنك تعديل دور مدير أقدم منك.');
    }



    $user->syncRoles([$newRole]);


    return back()->with('success', '✔ تم تحديث دور المستخدم بنجاح.');
}


 
}