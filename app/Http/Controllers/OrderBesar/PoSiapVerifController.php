<?php

namespace App\Http\Controllers\OrderBesar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\{
    GeneratedProducts,
    OrderMmea,
    Workstations
};
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk menangani PO yang siap diverifikasi
 *
 * Controller ini bertanggung jawab untuk:
 * - Menampilkan daftar PO yang siap verifikasi per tim
 * - Mengambil spesifikasi PO
 * - Mengambil daftar PO per tim
 *
 * Flow Aplikasi:
 * 1. User membuka halaman verifikasi PO
 * 2. System menampilkan PO yang belum diverifikasi untuk tim user
 * 3. User dapat melihat detail spesifikasi PO
 * 4. User dapat melihat daftar PO per tim
 *
 * Tech Stack:
 * - Laravel 10 untuk backend
 * - Inertia.js untuk SSR
 * - Vue 3 untuk frontend
 */
class PoSiapVerifController extends Controller
{
    /**
     * Menampilkan halaman daftar PO siap verifikasi
     *
     * Method ini:
     * 1. Mengambil ID tim dari user yang login
     * 2. Mengambil daftar PO untuk tim tersebut
     * 3. Merender view dengan data yang diperlukan
     *
     * @param Workstations $workstations Model untuk akses data workstation
     * @return \Inertia\Response Response Inertia dengan data PO dan tim
     */
    public function index(Workstations $workstations)
    {
        $teamUser = Auth::user()->workstation_id;

        return Inertia::render('OrderBesar/PoSiapVerif', [
            'products' => GeneratedProducts::where('assigned_team', $teamUser)
                ->where('status', '<', 2)
                ->get(),
            'teamList' => $workstations->listWorkstation(),
            'crntTeam' => $teamUser,
        ]);
    }

    /**
     * Mengambil spesifikasi PO
     *
     * Method ini mengambil detail spesifikasi PO dari database
     * berdasarkan nomor PO yang diberikan
     *
     * @param Request $request Request dengan nomor PO
     * @return OrderMmea|null Data spesifikasi PO
     */
    public function callSpec(Request $request): ?OrderMmea
    {
        return OrderMmea::where('order', $request->po)->first();
    }

    /**
     * Mengambil daftar PO per tim
     *
     * Method ini mengambil semua PO yang:
     * - Ditugaskan ke tim tertentu
     * - Belum selesai diverifikasi (status < 2)
     *
     * @param string $team ID tim yang akan diambil datanya
     * @return \Illuminate\Database\Eloquent\Collection Koleksi data PO
     */
    public function fetchWorkPo(string $team)
    {
        return GeneratedProducts::where('assigned_team', $team)
            ->where('status', '<', 2)
            ->get();
    }
}
