<?php

namespace App\Http\Controllers\MonitoringProduksi;

use App\Http\Controllers\Controller;
use App\Models\Workstations;
use App\Services\TeamActivityService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk monitoring aktivitas produksi pegawai
 *
 * Controller ini menggunakan pattern Service Layer dengan TeamActivityService untuk:
 * - Memisahkan business logic dari controller
 * - Memudahkan unit testing
 * - Meningkatkan reusability code
 *
 * Flow Aplikasi:
 * 1. Request masuk ke controller
 * 2. Controller memanggil service layer
 * 3. Service menghandle business logic
 * 4. Controller merender view dengan data
 *
 * Tech Stack:
 * - Laravel 10
 * - Inertia.js untuk SSR
 * - Vue 3 untuk frontend
 */
class ProduksiPegawaiController extends Controller
{
    /**
     * Instance TeamActivityService untuk handle business logic
     *
     * Service ini menyediakan method untuk:
     * - getActiveTeams(): Mengambil data tim aktif per tanggal
     * - hasActivity(): Cek status aktivitas tim
     *
     * Best Practice:
     * - Menggunakan dependency injection via constructor
     * - Type hinting untuk IDE support
     * - Single Responsibility Principle
     *
     * @var TeamActivityService
     */
    protected TeamActivityService $teamActivityService;

    /**
     * Constructor dengan dependency injection
     *
     * Dependency injection memudahkan:
     * - Unit testing dengan mock object
     * - Swap implementasi service
     * - Decoupling components
     *
     * @param TeamActivityService $teamActivityService Service untuk query aktivitas tim
     */
    public function __construct(TeamActivityService $teamActivityService)
    {
        $this->teamActivityService = $teamActivityService;
    }

    /**
     * Menampilkan halaman monitoring produksi
     *
     * Method ini:
     * 1. Mengambil data workstation
     * 2. Merender view dengan Inertia
     * 3. Meneruskan data ke frontend Vue
     *
     * Tips Development:
     * - Gunakan type hinting untuk autocomplete
     * - Response type untuk type safety
     * - Dokumentasi path view untuk navigasi
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
