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

        $q = GeneratedLabels::query()
                        ->whereBetween('start',$this->dateBetween($date))
                        ->get()
                        ->groupBy('np_users')
                        ->map(function($q, $key){
                            return count($q) * 500;
                        });

        return $q->sortDesc();
    }

    private function dateBetween($date)
    {
        return [Carbon::parse($date)->startOfDay(),Carbon::parse($date)->endOfDay()];
    }
}
