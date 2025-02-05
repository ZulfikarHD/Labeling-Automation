<?php

namespace App\Http\Controllers\MonitoringProduksi;

use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Services\TeamVerifikasiService;

/**
 * Controller untuk mengelola status verifikasi tim produksi
 *
 * Controller ini bertanggung jawab untuk:
 * - Menampilkan halaman monitoring status verifikasi tim
 * - Menampilkan detail produk dan label per tim
 * - Menggunakan TeamVerifikasiService untuk business logic
 *
 * Tech Stack:
 * - Laravel 10 untuk backend
 * - Inertia.js untuk SSR
 * - Vue 3 untuk frontend
 *
 * Flow Aplikasi:
 * 1. Request masuk ke controller
 * 2. Controller memanggil TeamVerifikasiService
 * 3. Service menghandle business logic
 * 4. Controller merender view dengan data
 */
class StatusVerifikasiTeamController extends Controller
{
    /**
     * Instance TeamVerifikasiService untuk handle business logic
     *
     * Service ini menyediakan method untuk:
     * - getAllWorkstations(): Mengambil data semua workstation
     * - getProductAndLabelsByTeam(): Mengambil data produk & label per tim
     *
     * Best Practice:
     * - Menggunakan dependency injection via constructor
     * - Type hinting untuk IDE support
     * - Single Responsibility Principle
     *
     * @var TeamVerifikasiService
     */
    private $teamService;

    /**
     * Constructor dengan dependency injection
     *
     * Dependency injection memudahkan:
     * - Unit testing dengan mock object
     * - Swap implementasi service
     * - Decoupling components
     *
     * @param TeamVerifikasiService $teamService Service untuk query data verifikasi
     */
    public function __construct(TeamVerifikasiService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * Menampilkan halaman utama status verifikasi
     *
     * Method ini:
     * 1. Mengambil data workstation dari service
     * 2. Merender view dengan Inertia
     * 3. Meneruskan data ke frontend Vue
     *
     * @return \Inertia\Response Response Inertia dengan data workstation
     */
    public function index()
    {
        return Inertia::render('MonitoringProduksi/PilihStatusVerifikasiTeam', [
            'workstations' => $this->teamService->getAllWorkstations()
        ]);
    }

    /**
     * Menampilkan detail status verifikasi per tim
     *
     * Flow Method:
     * 1. Ambil data produk & label dari service
     * 2. Jika data ditemukan, tampilkan monitor view
     * 3. Jika tidak ada, kembali ke list tim
     *
     * @param string $id ID tim yang akan ditampilkan
     * @return \Inertia\Response Response Inertia dengan data detail atau list
     */
    public function show(string $id)
    {
        $data = $this->teamService->getProductAndLabelsByTeam($id);

        if ($data) {
            return $this->renderMonitorView($data);
        }

        return $this->renderTeamListView();
    }

    /**
     * Helper method untuk render monitor view
     *
     * View ini menampilkan:
     * - Spesifikasi produk yang sedang dikerjakan
     * - Data label yang sudah dicetak
     * - Informasi tim yang bertugas
     *
     * @param array $data Data produk, label dan tim
     * @return \Inertia\Response Response Inertia dengan data monitoring
     */
    private function renderMonitorView($data)
    {
        return Inertia::render('MonitoringProduksi/StatusVerifikasiTeam', [
            'spec' => $data['product'],
            'dataRim' => $data['labels'],
            'team' => $data['team'],
        ]);
    }

    /**
     * Helper method untuk render team list view
     *
     * View ini menampilkan:
     * - Daftar semua workstation yang tersedia
     * - Digunakan saat tidak ada data spesifik tim
     *
     * @return \Inertia\Response Response Inertia dengan data workstation
     */
    private function renderTeamListView()
    {
        return Inertia::render('MonitoringProduksi/PilihStatusVerifikasiTeam', [
            'workstations' => $this->teamService->getAllWorkstations()
        ]);
    }
}
