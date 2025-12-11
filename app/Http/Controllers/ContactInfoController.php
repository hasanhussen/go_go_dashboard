<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactInfo;

class ContactInfoController extends Controller
{
     public function edit()
    {
        $info = ContactInfo::first();
        return view('admin.contact.edit', compact('info'));
    }

    public function update(Request $request)
    {
        $info = ContactInfo::first();

        if(!$info){
            $info = ContactInfo::create([
                'description' => $request->description??'',
            'phone'       => $request->phone??'',
            'email'       => $request->email??'',
            'address'     => $request->address??'',
            'facebook'    => $request->facebook??'',
            'instagram'   => $request->instagram??'',
            'whatsapp'    => $request->whatsapp??'',
            ]);
            return redirect()->back()->with('success', 'تم تحديث المعلومات بنجاح');
        }

        $info->update([
            'description' => $request->description ?: $info->description??'',
            'phone'       => $request->phone ?: $info->phone??'',
            'email'       => $request->email ?: $info->email??'',
            'address'     => $request->address ?: $info->address??'',
            'facebook'    => $request->facebook ?: $info->facebook??'',
            'instagram'   => $request->instagram ?: $info->instagram??'',
            'whatsapp'    => $request->whatsapp ?: $info->whatsapp??'',
        ]);

        return redirect()->back()->with('success', 'تم تحديث المعلومات بنجاح');
    }
}
