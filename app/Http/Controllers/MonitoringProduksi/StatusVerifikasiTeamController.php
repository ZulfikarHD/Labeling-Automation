<?php

namespace App\Http\Controllers\MonitoringProduksi;

use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Services\TeamVerifikasiService;

/**
 * Controller untuk menangani status verifikasi tim
 */
class StatusVerifikasiTeamController extends Controller
{
    /**
     * Service untuk mengelola verifikasi tim
     * @var TeamVerifikasiService
     */
    private $teamService;

    /**
     * Constructor untuk menginisialisasi service yang dibutuhkan
     *
     * @param TeamVerifikasiService $teamService Service untuk verifikasi tim
     */
    public function __construct(TeamVerifikasiService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * Menampilkan halaman pemilihan status verifikasi tim
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('MonitoringProduksi/PilihStatusVerifikasiTeam', [
            'workstations' => $this->teamService->getAllWorkstations() // Mengambil semua workstation
        ]);
    }

    /**
     * Menampilkan detail produk dan label berdasarkan ID tim
     *
     * @param string $id ID tim yang ingin ditampilkan
     * @return \Inertia\Response
     */
    public function show(string $id)
    {
        $data = $this->teamService->getProductAndLabelsByTeam($id); // Mengambil data produk dan label berdasarkan tim

        if ($data) {
            return $this->renderMonitorView($data); // Menampilkan tampilan monitor jika data ada
        }

        return $this->renderTeamListView(); // Menampilkan daftar tim jika data tidak ada
    }

    /**
     * Menampilkan tampilan monitor dengan data produk dan label
     *
     * @param array $data Data produk dan label
     * @return \Inertia\Response
     */
    private function renderMonitorView($data)
    {
        return Inertia::render('MonitoringProduksi/StatusVerifikasiTeam', [
            'spec' => $data['product'], // Spesifikasi produk
            'dataRim' => $data['labels'], // Data label
            'team' => $data['team'], // Tim yang terkait
        ]);
    }

    /**
     * Menampilkan tampilan daftar tim
     *
     * @return \Inertia\Response
     */
    private function renderTeamListView()
    {
        return Inertia::render('MonitoringProduksi/PilihStatusVerifikasiTeam', [
            'workstations' => $this->teamService->getAllWorkstations() // Mengambil semua workstation
        ]);
    }
}
