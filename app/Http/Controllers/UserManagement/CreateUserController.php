<?php

namespace App\Http\Controllers\UserManagement;

use App\Models\Users;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use Inertia\Inertia;

class CreateUserController extends Controller
{
    public function index()
    {
        return Inertia::render('UserManagement/CreateUser');
    }

    public function store(Request $request)
    {
        try{
            DB::transaction(function () use ($request) {
                Users::firstOrCreate([
                    'np'    => $request->npUser,
                    'role'  => $request->roleUser,
                    'password'  => Hash::make($request->password),
                ]);
            });
        } catch (QueryException $query) {
            return response()->json(['error' => 'Sry Something Went Wrong, Please Check Your Input'],500);
        }
    }
}
