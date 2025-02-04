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
 *
 * Controller ini menggunakan TeamActivityService untuk:
 * - Mengambil data aktivitas tim berdasarkan tanggal
 * - Mengecek status aktivitas tim tertentu
 * - Menampilkan data workstation yang aktif
 *
 * Lihat TeamActivityService.php untuk detail implementasi service
 */
class ProduksiPegawaiController extends Controller
{
    /**
     * Instance dari TeamActivityService
     *
     * Service ini menyediakan method untuk:
     * - getActiveTeams() - Mengambil daftar tim yang aktif pada tanggal tertentu
     * - hasActivity() - Mengecek apakah tim memiliki aktivitas di tanggal tertentu
     *
     * @var TeamActivityService
     */
    protected TeamActivityService $teamActivityService;

    /**
     * Constructor untuk dependency injection
     *
     * Menerima instance TeamActivityService yang akan digunakan
     * di seluruh method dalam controller
     *
     * @param TeamActivityService $teamActivityService Service untuk query data aktivitas tim
     */
    public function __construct(TeamActivityService $teamActivityService)
    {
        $this->teamActivityService = $teamActivityService;
    }

    /**
     * Menampilkan halaman monitoring produksi
     *
     * Flow:
     * 1. Mengambil data workstation dari model Workstations
     * 2. Merender view Inertia dengan data workstation
     * 3. View akan menampilkan data dalam format tabel
     *
     * Path view: 'MonitoringProduksi/ProduksiPegawai'
     *
     * @param Workstations $workstations Model untuk akses data workstation
     * @return Response Response Inertia dengan data workstation
     */
    public function index(Workstations $workstations): Response
    {
        return Inertia::render('MonitoringProduksi/ProduksiPegawai', [
            'teams' => $workstations->listWorkstation(),
        ]);
    }
}
