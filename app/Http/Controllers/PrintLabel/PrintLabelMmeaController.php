<?php

namespace App\Http\Controllers\PrintLabel;

use App\Http\Controllers\Controller;
use App\Models\GeneratedLabelsMmea;
use App\Models\GeneratedProducts;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrintLabelMmeaController extends Controller
{
    public function index($no_po = null)
    {
        return Inertia::render('PrintLabel/PrintLabelMmea/Index', [
            'no_po' => $no_po,
        ]);
    }

    public function store(Request $request)
    {
        foreach($request->no_rim as $nomor_rim) {
            // Check Existiing Data
            $current_data = GeneratedLabelsMmea::where('nomor_po', $request->no_po)
                                ->where('nomor_rim',$nomor_rim)
                                ->first();

            $key_kemas = "no_".$nomor_rim;
            $key_pemeriksa = "np_".$nomor_rim;

            $periksa1   = $this->convNp($request->periksa1[$key_pemeriksa] ?? $request->periksa1['np_1']);
            $periksa2   = $this->convNp($request->periksa2[$key_pemeriksa] ?? $request->periksa2['np_1']);

            if($current_data !==  null) {
                $waktu_p1 = $current_data->periksa1 == strtoupper($periksa1) ? $current_data->waktu_p1 : now();
                $waktu_p2 = $current_data->periksa2 == strtoupper($periksa2) ? $current_data->waktu_p2 : now();
            } else {
                $waktu_p1 = now();
                $waktu_p2 = now();
            }


            GeneratedLabelsMmea::updateOrCreate(
                [
                    'nomor_po'  => $request->no_po,
                    'nomor_rim' => $nomor_rim,
                ],
                [
                    'periksa1'  => strtoupper($periksa1),
                    'periksa2'  => strtoupper($periksa2),
                    'lbr_kemas' => $request->jml_kemas[$key_kemas] ?? $request->jml_kemas['no_1'],
                    'waktu_p1'  => $waktu_p1,
                    'waktu_p2'  => $waktu_p2,
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

    public function storeProduct(Request $request)
    {
        $sum_rim = round($request->jml_lbr,0,PHP_ROUND_HALF_UP);

        GeneratedProducts::updateOrCreate(
            [
                'no_po' => $request->no_po,
            ],
            [
                'no_obc'  => $request->no_obc,
                'type'    => $request->produk,
                'sum_rim' => $sum_rim,
                'start_rim' => 1,
                'end_rim'   => $sum_rim,
                'assigned_team' => 6, //MMEA
                'status'    => 2
            ]
        );
    }

    public function qcData(int $nomor_po)
    {
        return GeneratedLabelsMmea::where('nomor_po',$nomor_po)
                    ->orderBy('nomor_rim')
                    ->get();
    }

    private function convNp($np)
    {
        if(strlen($np) > 4) {
            $firstLetter = substr($np, 0, 1);
            $lastFour = substr($np, (strlen($np) - 4), strlen($np));
            $np = $firstLetter . $lastFour;
        }

        return $np;
    }

}
