<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\SettingRequest;
use App\Models\Setting;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings', ['settings' => Setting::allValues()]);
    }

    public function update(SettingRequest $request)
    {
        Setting::write($request->payload());

        return back()->with('status', 'Pengaturan disimpan.');
    }

    public function password(PasswordRequest $request)
    {
        $request->user()->update(['password' => $request->validated('password')]);

        return back()->with('status', 'Kata sandi diganti.');
    }
}
