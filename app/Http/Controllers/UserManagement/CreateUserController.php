<?php

namespace App\Http\Controllers\UserManagement;

use App\Models\Users;
use App\Http\Controllers\Controller;
use App\Models\Workstations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Controller untuk mengelola pembuatan user baru
 *
 * Controller ini bertanggung jawab untuk:
 * - Menampilkan form pembuatan user
 * - Menyimpan data user baru ke database
 *
 * Tech Stack:
 * - Laravel 10 untuk backend
 * - Inertia.js untuk SSR
 * - Vue 3 untuk frontend
 *
 * Flow Aplikasi:
 * 1. Admin membuka halaman create user
 * 2. Admin mengisi form dengan data user baru
 * 3. System menyimpan data user ke database
 */
class CreateUserController extends Controller
{
    /**
     * Menampilkan halaman pembuatan user
     *
     * Method ini:
     * 1. Mengambil daftar workstation yang tersedia
     * 2. Merender view dengan data workstation
     *
     * @return \Inertia\Response Response Inertia dengan data workstation
     */
    public function index()
    {
        return Inertia::render('UserManagement/CreateUser', [
            'listTeam' => Workstations::select('id', 'workstation')->get(),
        ]);
    }

    /**
     * Menyimpan data user baru
     *
     * Method ini:
     * 1. Memulai database transaction
     * 2. Mencoba membuat user baru dengan data yang diberikan
     * 3. Mengenkripsi password user
     * 4. Menangani error jika terjadi masalah
     *
     * @param Request $request Request dengan data user baru
     * @return \Illuminate\Http\JsonResponse Response status operasi
     */
    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                Users::firstOrCreate([
                    'np' => $request->npUser,
                    'role' => $request->roleUser,
                    'password' => Hash::make($request->password),
                    'workstation_id' => $request->team,
                ]);
            });

            return response()->json([
                'message' => 'User berhasil dibuat'
            ], 200);

        } catch (QueryException $query) {
            return response()->json([
                'error' => 'Terjadi kesalahan, silakan periksa input Anda atau Hubungi Administrator'
            ], 500);
        }
    }
}
