<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Support;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AdminNotification;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        Support::where('status', 'new')->update(['status' => 'open']);

        $query = Support::with('user')->where('status', '!=', 'closed'); ;
if ($request->has('search') && $request->search != '') {
    $search = $request->search;
    $query->where(function($q) use ($search) {
        $q->where('subject', 'LIKE', "%$search%")
          ->orWhere('message', 'LIKE', "%$search%");
    });
}


        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $supports = $query->latest()->paginate(10);

        return view('admin.support.support', compact('supports'));
    }

    public function show($id)
    {
        $support = Support::with('user')->findOrFail($id);

        if ($support->status === 'new') {
        $support->status = 'open';
        $support->save();
    }

    $hideSearch = true;

        return view('admin.support.show', compact('support','hideSearch'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string|max:2000',
        ]);

        $support = Support::findOrFail($id);
        $support->reply = $request->reply;
        $support->status = 'resolved';
        $support->save();

    $notificationService = new \App\Services\NotificationService();

    $notificationService->sendToUser(
    $support->user,
    'تم الاستجابة للشكوى ',
    $support->reply,
        [
        'type' => 'user_support',
        'support_id' => (string) $support->id, // لازم قيم الـ data تكون نصوص
    ]);

    Notification::send($support->user, new AdminNotification($support->user,type: 'user_support',support: $support)); 


        return redirect()->back()->with('success', 'تم إرسال الرد بنجاح 💬');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:new,open,in_progress,resolved,closed',
        ]);

        $support = Support::findOrFail($id);
        $support->status = $request->status;
        $support->save();

        return redirect()->back()->with('success', 'تم تحديث الحالة ✅');
    }

    public function destroy($id)
    {
        $support = Support::findOrFail($id);
        $support->delete();

        return redirect()->back()->with('success', 'تم حذف الطلب 🗑️');
    }

    public function close($id)
{
    $complaint = Support::findOrFail($id);
    $complaint->status = 'closed';
    $complaint->save();

    return redirect()->back()->with('success', 'تم إغلاق الشكوى بنجاح ✅');
}

public function archive(){
    $complaints = Support::where('status', 'closed')
        ->orderBy('updated_at', 'desc')
        ->get();

    return view('admin.support.archive', compact('complaints'));

}

public function unreadCount()
{
    $count =  Support::where('status', 'new')->count();
    return response()->json(['unreadcount' => $count]);
}



}