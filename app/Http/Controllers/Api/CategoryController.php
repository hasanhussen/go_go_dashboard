<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use Illuminate\Http\Request;

use App\Http\Resources\StoreResource;
use App\Models\Store;

class CategoryController extends Controller
{
    public function getCategory(){
        $user  = Auth::user();
        $categories = Category::all();
        $notifications  = $user->notifications;
        $notOppendNotification = 0;
    foreach($notifications as $notification){
         if ($user->open_notifications == null || $notification->created_at > $user->open_notifications){
            $notOppendNotification++;
        }
    }
    $storeData = Store::where('status','1')->with('user')->orderByDesc('bayesian_score')->take(5)->get();
    $bestStores= StoreResource::collection($storeData);

     return response()->json([
      'categories' => $categories,
      'notOppendNotification' => $notOppendNotification,
      'bestStores'=>$bestStores
    ]);
    }
}
