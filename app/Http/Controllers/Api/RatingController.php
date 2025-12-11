<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    // إضافة أو تحديث التقييم
    public function rateStore(Request $request, $store_id)
{
    $user_id = Auth::user()->id;
    $rating_value = $request->rating; // 1 - 5

    // نضيف أو نعدل تقييم المستخدم
    $rating = Rating::updateOrCreate(
        ['store_id' => $store_id, 'user_id' => $user_id],
        ['rating' => $rating_value]
    );

    // بعد إضافة/تعديل التقييم، نحسب القيم الجديدة
    $m = 50; // الحد الأدنى لعدد التقييمات الموثوق
    $C = DB::table('ratings')->avg('rating') ?? 0.0; // المتوسط العام لكل المتاجر

    $storeRatings = Rating::where('store_id', $store_id);
    $v = $storeRatings->count();
    $R = $storeRatings->avg('rating') ?? 0.0;

    $bayesian_score = ($v + $m) > 0 ? (($v * $R) + ($m * $C)) / ($v + $m) : $C;

    // نحدث جدول المتجر مباشرة
    $store = Store::findOrFail($store_id);
    $store->total_ratings = $v;
    $store->avg_rating = round($R, 2);
    $store->bayesian_score = round($bayesian_score, 2);
    $store->save();

    return response()->json([
        'message' => 'تم تحديث التقييم بنجاح',
        'store' => $store
    ]);
}
}