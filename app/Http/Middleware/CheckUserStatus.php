<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
           {
        $user = $request->user();// استخدام api guard

        // مستخدم غير مصادق
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'يجب تسجيل الدخول.',
            ], 401);
        }

       
        if ($user->status == '0') {
            return response()->json([
                'status' => false,
                'message' => 'لم يتم الموافقة على حسابك ولا يمكنك تنفيذ هذا الطلب حالياَ.',
            ], 403);
        }


        if ($user->status == '2') {
            return response()->json([
                'status' => false,
                'message' => 'حسابك محظور ولا يمكنك تنفيذ هذا الطلب.',
            ], 403);
        }

        return $next($request);
    }
    }
}
