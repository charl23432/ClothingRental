<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    // GET SETTINGS
    public function index()
    {
        $setting = Setting::first();

        return response()->json([
            'store_name'     => $setting->store_name ?? '',
            'gcash_number'   => $setting->gcash_number ?? '',
            'contact_number' => $setting->contact_number ?? '',
            'email'          => $setting->email ?? '',
            'address'        => $setting->address ?? '',
            'logo'           => $setting->logo ?? null,
        ]);
    }

    // SAVE / UPDATE SETTINGS
    public function update(Request $request)
    {
        $setting = Setting::firstOrCreate(['id' => 1]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/logo'), $filename);

            $setting->logo = 'uploads/logo/' . $filename;
        }

        $setting->store_name = $request->store_name;
        $setting->gcash_number = $request->gcash_number;
        $setting->contact_number = $request->contact_number;
        $setting->email = $request->email;
        $setting->address = $request->address;

        $setting->save();

        return response()->json([
            'message' => 'Settings updated successfully!',
            'data' => $setting
        ]);
    }
}