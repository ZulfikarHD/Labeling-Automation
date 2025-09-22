<?php

namespace App\Http\Controllers\PrintLabel;

use App\Http\Controllers\Controller;
use App\Models\GeneratedLabelsMmea;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrintLabelMmeaController extends Controller
{
    public function index()
    {
        return Inertia::render('PrintLabel/PrintLabelMmea/Index');
    }

    public function store(Request $request)
    {
        foreach($request->no_rim as $nomor_rim) {
            $key_kemas = "no_".$nomor_rim;
            $key_pemeriksa = "np_".$nomor_rim;
            GeneratedLabelsMmea::updateOrCreate(
                [
                    'nomor_po'  => $request->no_po,
                    'nomor_rim' => $nomor_rim,
                ],
                [
                    'periksa1'  => $request->periksa1[$key_pemeriksa],
                    'periksa2'  => $request->periksa2[$key_pemeriksa],
                    'lbr_kemas' => $request->jml_kemas[$key_kemas],
                ]
            );
        }
    }
    
}
