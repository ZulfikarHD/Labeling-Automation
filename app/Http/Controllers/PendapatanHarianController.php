<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\GeneratedLabels;
use App\Models\DataInschiet;

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
                            $ins_date = [
                                carbon::parse($q->value('start'))->startOfDay(),
                                carbon::parse($q->value('start'))->endOfDay()];

                            $sum_ins_kiri   = DataInschiet::where('np_kiri',$key)
                                                    ->whereBetween('updated_at',$ins_date)
                                                    ->sum('inschiet');

                            $sum_ins_kanan  = DataInschiet::where('np_kanan',$key)
                                                    ->whereBetween('updated_at',$ins_date)
                                                    ->sum('inschiet');

                            $calculate_verif = count($q->whereNotIn('no_rim',999)) * 500;
                            $sum_inschiet    = round(divnum($sum_ins_kiri,2)) + round(divnum($sum_ins_kanan,2));
                            return [
                                'pegawai'    => $key,
                                'verifikasi' => $calculate_verif + $sum_inschiet,
                            ];
                        })->sortByDesc('verifikasi')->values();
    }
}
