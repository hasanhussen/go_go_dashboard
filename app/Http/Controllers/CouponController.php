<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponRequest;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // عرض صفحة الكوبونات مع الفلترة
    public function index(Request $request)
    {
        $query = Coupon::query();
        


    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->where('name', 'LIKE', "%$search%");
              
    }
        if ($request->filled('statuses')) {
            $query->whereIn('status', $request->statuses);
        }

        $coupons = $query->orderBy('id', 'desc')->paginate(10, ['*'], 'coupons_page')->withQueryString();
            // تحديث الحالة بناءً على التواريخ والعدد
foreach ($coupons as $coupon) {
    $now = now();

    if ($coupon->status !== 'inactive') {
        if ($coupon->count <= 0) {
            $coupon->status = 'exhausted';
        } elseif ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            $coupon->status = 'not_started';
        } elseif ($coupon->valid_to && $now->gt($coupon->valid_to)) {
            $coupon->status = 'expired';
        } else {
            $coupon->status = 'active';
        }
        $coupon->saveQuietly(); // حتى ما يسبب تحديث timestamps
    }
}


        return view('admin.coupons.coupons', compact('coupons'));
    }

    // صفحة إضافة كوبون
    public function create()
    {
        return view('admin.coupons.create_coupon');
    }

    // حفظ كوبون جديد
    public function store(CouponRequest $request)
    {
        Coupon::create($request->all());
        return redirect()->route('coupons.index')->with('success', 'Coupon created successfully.');
    }

    // صفحة تعديل كوبون
    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit_coupon', compact('coupon'));
    }

    // تحديث كوبون
    public function update(CouponRequest $request, Coupon $coupon)
    {
       $coupon->update($request->all());

$now = now();
if ($coupon->status !== 'inactive') {
    if ($coupon->count <= 0) {
        $coupon->status = 'exhausted';
    } elseif ($coupon->valid_from && $now->lt($coupon->valid_from)) {
        $coupon->status = 'not_started';
    } elseif ($coupon->valid_to && $now->gt($coupon->valid_to)) {
        $coupon->status = 'expired';
    } else {
        $coupon->status = 'active';
    }
    $coupon->saveQuietly();
}


        return redirect()->route('coupons.index')->with('success', 'Coupon updated successfully.');
    }

    // حذف كوبون
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('coupons.index')->with('success', 'Coupon deleted successfully.');
    }

    // تفعيل / إلغاء تفعيل
   public function toggle(Coupon $coupon)
{
    if ($coupon->status === 'inactive') {
        $now = now();

        if ($coupon->count <= 0) {
            $coupon->status = 'exhausted';
        } elseif ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            $coupon->status = 'not_started';
        } elseif ($coupon->valid_to && $now->gt($coupon->valid_to)) {
            $coupon->status = 'expired';
        } else {
            $coupon->status = 'active';
        }
    } else {
        $coupon->status = 'inactive';
    }

    $coupon->saveQuietly();

    return redirect()->route('coupons.index')->with('success', 'Coupon status updated.');
}


    // تحديث التواريخ فقط
    public function updateDate(Request $request, Coupon $coupon)
    {
        $request->validate([
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after_or_equal:valid_from',
        ]);

        $coupon->update([
            'valid_from' => $request->valid_from,
            'valid_to' => $request->valid_to,
        ]);

        return redirect()->route('coupons.index')->with('success', 'Coupon dates updated.');
    }
}
