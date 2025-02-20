<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\VerificationService;
use App\Services\TeamActivityService;

/**
 * Controller untuk mengelola data pendapatan harian pegawai
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
     */
    public function gradeHarian(Request $request): JsonResponse
    {
        $date = $request->date ? Carbon::parse($request->date)->startOfDay() : today();
        return response()->json($this->verificationService->getDailyVerification($date, $request->team));
    }

    /**
     * Mengambil daftar tim yang memiliki aktivitas
     */
    public function getActiveTeams(Request $request): JsonResponse
    {
        $date = $request->date ? Carbon::parse($request->date)->startOfDay() : today();
        return response()->json($this->teamActivityService->getActiveTeams($date));
    }

    /**
     * Memverifikasi workstation yang memiliki aktivitas
     */
    public function verifWorkstationNotEmpty(Request $request): JsonResponse
    {
        $date = $request->date ? Carbon::parse($request->date)->startOfDay() : today();
        return response()->json($this->teamActivityService->getActiveTeams($date));
    }
}
