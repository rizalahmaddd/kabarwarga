<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(['data' => Setting::allValues()]);
    }

    public function update(SettingRequest $request): JsonResponse
    {
        Setting::write($request->payload());

        return response()->json(['data' => Setting::allValues(), 'message' => 'Pengaturan disimpan.']);
    }

    public function password(PasswordRequest $request): JsonResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        return response()->json(['message' => 'Kata sandi diganti.']);
    }
}
