<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\VerificationService;
use App\Services\TeamActivityService;

/**
 * Controller untuk mengelola data pendapatan harian
 */
class PendapatanHarianController extends Controller
{
    /**
     * Service untuk verifikasi data
     */
    protected VerificationService $verificationService;

    /**
     * Service untuk aktivitas tim
     */
    protected TeamActivityService $teamActivityService;

    /**
     * Constructor untuk menginisialisasi service yang dibutuhkan
     *
     * @param VerificationService $verificationService Service untuk verifikasi
     * @param TeamActivityService $teamActivityService Service untuk aktivitas tim
     */
    public function __construct(
        VerificationService $verificationService,
        TeamActivityService $teamActivityService
    ) {
        $this->verificationService = $verificationService;
        $this->teamActivityService = $teamActivityService;
    }

    /**
     * Mendapatkan grade harian untuk pegawai
     *
     * @param Request $request Request dari client yang berisi tanggal dan tim
     * @return JsonResponse Response berupa data verifikasi dalam format JSON
     */
    public function gradeHarian(Request $request): JsonResponse
    {
        $date = $request->date ? Carbon::parse($request->date)->startOfDay() : today();

        $verificationData = $this->verificationService->getDailyVerification($date, $request->team);

        return response()->json($verificationData);
    }

    /**
     * Mendapatkan daftar tim yang aktif pada tanggal tertentu
     *
     * @param Request $request Request dari client yang berisi tanggal
     * @return JsonResponse Response berupa daftar tim aktif dalam format JSON
     */
    public function getActiveTeams(Request $request): JsonResponse
    {
        $date = $request->date ? Carbon::parse($request->date)->startOfDay() : today();

        $activeTeams = $this->teamActivityService->getActiveTeams($date);

        return response()->json($activeTeams);
    }

    /**
     * Mendapatkan workstation yang memiliki data pada tanggal tertentu
     *
     * @param Request $request Request dari client yang berisi tanggal
     * @return JsonResponse Response berupa daftar workstation dalam format JSON
     */
    public function verifWorkstationNotEmpty(Request $request): JsonResponse
    {
        $date = $request->date ? Carbon::parse($request->date)->startOfDay() : today();

        $activeTeams = $this->teamActivityService->getActiveTeams($date);

        return response()->json($activeTeams);
    }
}
