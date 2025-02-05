<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

/**
 * Controller untuk mengelola perubahan password user
 *
 * Controller ini bertanggung jawab untuk:
 * - Menampilkan form perubahan password
 * - Memvalidasi dan mengupdate password user
 *
 * Tech Stack:
 * - Laravel 10 untuk backend
 * - Inertia.js untuk SSR
 * - Vue 3 untuk frontend
 *
 * Flow Aplikasi:
 * 1. User membuka halaman change password
 * 2. User mengisi form dengan password lama dan baru
 * 3. System memvalidasi dan mengupdate password
 */
class PasswordController extends Controller
{
    /**
     * Menampilkan halaman perubahan password
     *
     * @return \Inertia\Response Response Inertia dengan form change password
     */
    public function changePassword()
    {
        return Inertia::render('UserManagement/ChangePassword');
    }

    /**
     * Memproses perubahan password user
     *
     * Method ini:
     * 1. Memvalidasi input password lama dan baru
     * 2. Mengecek kecocokan password lama
     * 3. Mengupdate password dengan hash baru
     *
     * Validasi:
     * - Password lama harus sesuai
     * - Password baru minimal 8 karakter
     *
     * @param Request $request Request dengan password lama dan baru
     * @return \Illuminate\Http\RedirectResponse Response dengan status operasi
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $validated = $request->validate([
            'oldPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8'],
        ]);

        // Cek kecocokan password lama
        if (!Hash::check($validated['oldPassword'], $user->password)) {
            return back()->withErrors(['oldPassword' => 'Password lama tidak cocok.']);
        }

        // Update password dengan hash baru
        User::where('id', $user->id)->update([
            'password' => Hash::make($validated['newPassword']),
        ]);

        return back()->with('status', 'Password berhasil diubah.');
    }
}
