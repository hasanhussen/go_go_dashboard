<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
   public function checkCoupon(Request $request)
{
    $coupon = Coupon::where('name', $request->name)->first();

        if (!$coupon) {
            return response()->json(['error' => 'الكوبون غير موجود'], 404);
        }

        $status = $coupon->checkStatus();

        if($status === 'inactive') {
            return response()->json(['error' => 'الكوبون غير مفعل حاليا، يرجى اختيار كوبون آخر'], 400);
        }

        if ($status === 'not_started') {
            return response()->json(['error' => 'الكوبون غير فعال بعد، يرجى المحاولة لاحقًا'], 400);
        }

        if ($status === 'expired') {
            return response()->json(['error' => 'انتهت صلاحية الكوبون'], 400);
        }

        if ($status === 'exhausted') {
            return response()->json(['error' => 'تم استخدام الكوبون لأقصى عدد ممكن'], 400);
        }

        return response()->json([
            'id'=>$coupon->id,
            'success' => 'سيتم اضافة الكوبون إلى طلبك',
            'details'=> $coupon->details,
            'discount'=> $coupon->discount
        ]);
  
}
}
