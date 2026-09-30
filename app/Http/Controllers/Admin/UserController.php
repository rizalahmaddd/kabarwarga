<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users', ['users' => User::orderBy('name')->get()]);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        return back()->with('status', "{$user->name} sekarang bisa masuk sebagai pengurus.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('warning', 'Akun yang sedang dipakai tidak bisa dihapus.');
        }

        $user->tokens()->delete();
        $user->delete();

        return back()->with('status', "Akses {$user->name} dicabut.");
    }
}
