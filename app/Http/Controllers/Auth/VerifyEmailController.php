<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Factory;

class VerifyEmailController extends Controller
{
    public function verify($id, $hash)
    {
        $user = User::findOrFail($id);

        // تحقق من hash
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response('Invalid verification link', 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response('Email already verified.');
        }

        $user->markEmailAsVerified();

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
        $user,
       ' تأكيد البريد الالكتروني ✅',
       'تم التحقق من صلاحية بريدك الالكتروني ',
        [
        'type' => 'email_verified', // لازم قيم الـ data تكون نصوص
        ]);

    Notification::send($user, new UserNotification ($user, 'accept'));

   return response()->noContent(); // 204 No Content


    }
}

