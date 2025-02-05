<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\VerificationService;
use App\Services\TeamActivityService;

/**
 * Controller untuk mengelola data pendapatan harian pegawai
 *
 * Controller ini bertanggung jawab untuk:
 * - Mengambil data grade/verifikasi harian per pegawai
 * - Mendapatkan daftar tim yang aktif
 * - Memverifikasi workstation yang memiliki aktivitas
 *
 * Tech Stack:
 * - Laravel 10 untuk backend
 * - Service pattern untuk business logic
 * - Carbon untuk manipulasi tanggal
 *
 * Flow Aplikasi:
 * 1. Client mengirim request dengan tanggal dan tim (opsional)
 * 2. Controller memproses request menggunakan service terkait
 * 3. Response dikirim dalam format JSON
 */
class PendapatanHarianController extends Controller
{
    /**
     * Service untuk mengelola verifikasi dan perhitungan grade
     */
    protected VerificationService $verificationService;

    /**
     * Service untuk mengelola data aktivitas tim
     */
    protected TeamActivityService $teamActivityService;

    /**
     * Constructor untuk dependency injection service yang dibutuhkan
     */
    public function __construct(
        VerificationService $verificationService,
        TeamActivityService $teamActivityService
    ) {
        $this->verificationService = $verificationService;
        $this->teamActivityService = $teamActivityService;
    }

    /**
     * Mengambil data grade harian per pegawai
     *
     * Method ini:
     * 1. Memproses tanggal dari request (default: hari ini)
     * 2. Mengambil data verifikasi menggunakan service
     * 3. Mengembalikan data dalam format JSON
     *
     * @param Request $request {date?: string, team?: string}
     * @return JsonResponse Data verifikasi per pegawai
     */
    public function gradeHarian(Request $request): JsonResponse
    {
        $date = $request->date ? Carbon::parse($request->date)->startOfDay() : today();
        $verificationData = $this->verificationService->getDailyVerification($date, $request->team);

        return response()->json($verificationData);
    }

    /**
     * Mengambil daftar tim yang memiliki aktivitas
     *
     * Method ini:
     * 1. Memproses tanggal dari request (default: hari ini)
     * 2. Mengambil data tim aktif menggunakan service
     * 3. Mengembalikan daftar tim dalam format JSON
     *
     * @param Request $request {date?: string}
     * @return JsonResponse Array berisi ID tim yang aktif
     */
    public function getActiveTeams(Request $request): JsonResponse
    {
        $date = $request->date ? Carbon::parse($request->date)->startOfDay() : today();
        $activeTeams = $this->teamActivityService->getActiveTeams($date);

        return response()->json($activeTeams);
    }

    /**
     * Memverifikasi workstation yang memiliki aktivitas
     *
     * Method ini:
     * 1. Memproses tanggal dari request (default: hari ini)
     * 2. Mengecek workstation aktif menggunakan service
     * 3. Mengembalikan daftar workstation dalam format JSON
     *
     * @param Request $request {date?: string}
     * @return JsonResponse Array berisi ID workstation yang aktif
     */
    public function verifWorkstationNotEmpty(Request $request): JsonResponse
    {
        $date = $request->date ? Carbon::parse($request->date)->startOfDay() : today();
        $activeTeams = $this->teamActivityService->getActiveTeams($date);

        return response()->json($activeTeams);
    }
}
