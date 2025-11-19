<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\GeneratedProducts;
use App\Models\Workstations;

use Illuminate\Support\Facades\Auth;

class OrderSiapPeriksaController extends Controller
{
    public function index()
    {
        $teamUser = Auth::user()->workstation_id;

        return Inertia::render('OrderBesar/PoSiapVerif', [
            'products' => GeneratedProducts::where('assigned_team', $teamUser)
                ->where('status', '<', 2)
                ->get(),
            'teamList' => Workstations::listWorkstation(),
            'crntTeam' => $teamUser,
        ]);
    }

    public function fetchWorkPo(string $team)
    {
        return GeneratedProducts::where('assigned_team', $team)
            ->where('status', '<', 2)
            ->get();
    }
}
