<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Inertia\Inertia;

class PasswordController extends Controller
{
    public function changePassword()
    {
        return Inertia::render('UserManagement/ChangePassword');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'oldPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8'],
        ]);

        if (!Hash::check($validated['oldPassword'], $user->password)) {
            return back()->withErrors(['oldPassword' => 'Password lama tidak cocok.']);
        }

        User::where('id', $user->id)->update([
            'password' => Hash::make($validated['newPassword']),
        ]);

        return back()->with('status', 'Password berhasil diubah.');

    }
}
