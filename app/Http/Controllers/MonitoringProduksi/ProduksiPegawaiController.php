<?php

namespace App\Http\Controllers\MonitoringProduksi;

use App\Http\Controllers\Controller;
use App\Models\Workstations;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProduksiPegawaiController extends Controller
{
    public function index()
    {
        return Inertia::render('ProduksiPegawai', [
            'teams' => Workstations::all(),
        ]);
    }
}
