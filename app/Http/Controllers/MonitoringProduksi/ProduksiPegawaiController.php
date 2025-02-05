<?php

namespace App\Http\Controllers\MonitoringProduksi;

use App\Http\Controllers\Controller;
use App\Models\Workstations;
use App\Services\TeamActivityService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk menangani monitoring produksi pegawai
 */
class ProduksiPegawaiController extends Controller
{
    /**
     * Service untuk mengelola aktivitas tim
     */
    protected TeamActivityService $teamActivityService;

    /**
     * Constructor untuk menginisialisasi service yang dibutuhkan
     *
     * @param TeamActivityService $teamActivityService Service untuk aktivitas tim
     */
    public function __construct(TeamActivityService $teamActivityService)
    {
        $this->teamActivityService = $teamActivityService;
    }

    /**
     * Menampilkan halaman monitoring produksi
     *
     * @param Workstations $workstations Model untuk data workstation
     * @return Response Response Inertia yang berisi data untuk view
     */
    public function index(Workstations $workstations): Response
    {
        return Inertia::render('MonitoringProduksi/ProduksiPegawai', [
            'teams' => $workstations->listWorkstation(),
        ]);
    }
}
