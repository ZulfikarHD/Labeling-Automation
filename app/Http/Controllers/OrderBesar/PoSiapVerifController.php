<?php

namespace App\Http\Controllers\OrderBesar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\GeneratedProducts;
use App\Models\OrderMmea;
use App\Models\Workstations;
use Illuminate\Support\Facades\Auth;

class PoSiapVerifController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Workstations $workstations)
    {
        $teamUser = Auth::user()->workstation_id;

        return Inertia::render('OrderBesar/PoSiapVerif', [
            'products' => GeneratedProducts::where('assigned_team', $teamUser)->where('status', '<', 2)->get(),
            'teamList' => $workstations->listWorkstation(),
            'crntTeam' => $teamUser,
        ]);
    }

    public function callSpec(Request $request)
    {
        $getData = OrderMmea::where('order', $request->po)
            ->first();

        return $getData;
    }

    public function fetchWorkPo(String $team)
    {
        return GeneratedProducts::where('assigned_team', $team)
            ->where('status', '<', 2)
            ->get();
    }
}
