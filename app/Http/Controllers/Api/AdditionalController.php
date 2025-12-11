<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AdditionalRequest;
use App\Models\Additional;
use App\Models\Meal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdditionalController extends Controller
{
//     public function getAdditional()
// {
//     $additionals = Additional::all();
//     return response()->json($additionals);

// }

    public function getStoreAdditional($store_id){
    $additionals = Additional::where('store_id',$store_id)->get();;
    return response()->json($additionals);
    }

  public function addAdditional(AdditionalRequest $request){

    $validated =  $request->validated();
    $existing = Additional::where('store_id',$request->store_id)->where('name',$request->name)->first();
   if ($existing) {
    return response()->json(['error' => 'هذه الإضافة موجودة بالفعل في القائمة'], 400);
}
    $additionals = Additional::create($validated);
    return response()->json($additionals);
 }

  public function deleteadditional($additionalId){
    $additional = Additional::findOrFail($additionalId);
    $additional->delete();
    return response()->json([
        'success' => 'تم حذف الإضافة بنجاح'
    ]);
 }

  public function editadditional(AdditionalRequest $request,$additionalId){
     $validated =  $request->validated();
     if (!$request->has('quantity') || $request->quantity === null) {
    $validated['quantity'] = null;
}
    $additional = Additional::findOrFail($additionalId);
    $additional->update($validated);
    return response()->json([
        'success' => 'تم تعديل الإضافة بنجاح'
    ]);
 }

}
