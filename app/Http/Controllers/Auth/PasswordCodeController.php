<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordCode;
use App\Models\User;
use App\Notifications\SendPasswordCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PasswordCodeController extends Controller
{
    public function sendCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        

          $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        // حذف الأكواد القديمة
        PasswordCode::where('email', $request->email)->delete();

        // توليد كود جديد
        $code = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        //$plain = random_int(10000, 99999); // 5 digits
        $hash = Hash::make((string)$code);
        PasswordCode::create([
            'email' => $request->email,
            'code' => $hash,
            'expires_at' => now()->addMinutes(5),
            'attempts' => 0,
        ]);

        // إرسال الكود
        Notification::send($user, new SendPasswordCode($code));

        return response()->json([
            'status' => 'success',
            'message' => 'Code sent successfully'
        ]);
    }



    
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:5',
        ]);


             $record = PasswordCode::where('email', $request->email)
        ->where('expires_at', '>', now())
        ->latest()
        ->first();

  if (!$record) {
            return response()->json(['message' => 'Invalid or expired code'], 400);
        }
     

      
        if ($record->attempts >= 5) {
            return response()->json(['code'=>['تم تجاوز عدد المحاولات.']], 400);
        }

        if (! Hash::check((string)$request->code, $record->code)) {
            $record->attempts += 1;
            $record->save();
            return response()->json(['code'=>['الرمز غير صحيح.']], 400);
        }

      

        // توليد توكن مؤقت (اختياري)
        //$token = Str::random(60);

        // حذف الكود بعد التحقق
        $record->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Code verified successfully',
            //'token' => $token,
        ]);
    }
}