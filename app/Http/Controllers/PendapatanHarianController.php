<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\GeneratedLabels;

class PendapatanHarianController extends Controller
{
    public function gradeHarian(Request $request)
    {
        $date = $request->date !== null ? $request->date : now();

        $verifPegawai = $this->verifHarian($date,$request->team);

        return $verifPegawai;
    }

    private function dateBetween($date)
    {
        return [Carbon::parse($date)->startOfDay(),Carbon::parse($date)->endOfDay()];
    }

    /**
     * Kalkulasi hasil verifikasi harian pegawai
     */
    public function verifHarian(String $date, String $team)
    {
        $teamFilter = $team == 0 ? '!=' : '=' ;
        // $teamFilter = '!=';

        return GeneratedLabels::query()
                        ->whereBetween('start',$this->dateBetween($date))
                        ->where('workstation',$teamFilter,$team)
                        ->get()
                        ->groupBy('np_users')
                        ->map(function($q, $key){
                            return [
                                'pegawai'    => $key,
                                'verifikasi' => count($q) * 500,
                            ];
                        })->sortByDesc('verifikasi')->values();
    }
}
