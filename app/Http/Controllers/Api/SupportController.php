<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Support;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AdminNotification;
use App\Events\NewSupportComplaint;


class SupportController extends Controller
{
/**
* Store a new support request.
*/
public function store(Request $request)
{
        $validator = Validator::make($request->all(), [
        'role' => 'required|string|in:user,owner,delivery',
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
        'image' => 'nullable|image|max:5120', // max 5MB
    ]);


        if ($validator->fails()) {
        return response()->json([
        'status' => 0,
        'errors' => $validator->errors()
        ], 422);
     }


        // If your API uses authentication, you can get the authenticated user:
        $user = $request->user(); // requires auth middleware (sanctum/passport) to be present


        $imagePath = null;
        if ($request->hasFile('image')) {
        $image = $request->file('image');
        // store in storage/app/public/supports
        $imagePath = $image->store('supports', 'public');
       }


        $support = Support::create([
        'user_id' => $user ? $user->id : null,
        'role' => $request->input('role'),
        'subject' => $request->input('subject'),
        'message' => $request->input('message'),
        'image' => $imagePath,
        'status' => 'new',
       ]);

       broadcast(new NewSupportComplaint($support))->toOthers();
       //$admins = User::role(['admin', 'editor'])->get();
       $admin= User::role('admin')->orderBy('created_at', 'asc')->first();
       Notification::send($admin, new AdminNotification($support->user,type: 'admin_support',support: $support)); 

        return response()->json([
        'status' => 1,
        'message' => 'Support request created successfully',
        'data' => $support,
        ], 201);
    }
   }
 