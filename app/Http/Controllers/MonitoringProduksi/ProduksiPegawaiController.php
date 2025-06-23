<?php

namespace App\Http\Controllers\MonitoringProduksi;

use App\Http\Controllers\Controller;
use App\Models\Workstations;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk monitoring aktivitas produksi pegawai
 */
class ProduksiPegawaiController extends Controller
{
    /**
     * Menampilkan halaman monitoring produksi pegawai
     */
    public function index(Workstations $workstations): Response
    {
        return Inertia::render('MonitoringProduksi/ProduksiPegawai', [
            'teams' => $workstations->listWorkstation(),
        ]);
    }
}
