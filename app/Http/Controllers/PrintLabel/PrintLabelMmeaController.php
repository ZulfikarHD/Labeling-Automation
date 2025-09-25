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
                    'periksa1'  => $request->periksa1[$key_pemeriksa] ?? $request->periksa1['np_1'],
                    'periksa2'  => $request->periksa2[$key_pemeriksa] ?? $request->periksa2['np_1'],
                    'lbr_kemas' => $request->jml_kemas[$key_kemas] ?? $request->jml_kemas['no_1'],
                ]
            );
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Label berhasil diproses',
            // 'data' => [
            //     'processed_labels' => $result['processed_labels'],
            //     'failed_labels' => $result['failed_labels'],
            //     'remaining_labels' => $result['remaining_labels'],
            //     'status' => $result['remaining_labels'] > 0 ? 'in_progress' : 'completed'
            // ]
        ]);
    }

    public function qcData(int $nomor_po)
    {
        return GeneratedLabelsMmea::where('nomor_po',$nomor_po)
                    ->orderBy('nomor_rim')
                    ->get();
    }
    
}
