<?php

namespace App\Http\Controllers\PrintLabel;

use App\Http\Controllers\Controller;
use App\Models\GeneratedLabelsMmea;
use App\Models\GeneratedProducts;
use App\Services\PrintLabelService;
use App\Services\ProductionOrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrintLabelMmeaController extends Controller
{
    public function __construct(
        protected PrintLabelService $printLabelService,
        protected ProductionOrderService $productionOrderService
    ) {}

    public function index($no_po = null)
    {
        return Inertia::render('PrintLabel/PrintLabelMmea/Index', [
            'no_po' => $no_po,
        ]);
    }

    public function store(Request $request)
    {
        foreach($request->no_rim as $nomor_rim) {
            // Check Existing Data
            $current_data = GeneratedLabelsMmea::where('nomor_po', $request->no_po)
                                ->where('nomor_rim',$nomor_rim)
                                ->first();

            $key_kemas = "no_".$nomor_rim;
            $key_pemeriksa = "np_".$nomor_rim;

            $periksa1 = $this->printLabelService->convertNpFormat(
                $request->periksa1[$key_pemeriksa] ?? $request->periksa1['np_1']
            );
            $periksa2 = $this->printLabelService->convertNpFormat(
                $request->periksa2[$key_pemeriksa] ?? $request->periksa2['np_1']
            );

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
        ]);
    }

    public function storeProduct(Request $request)
    {
        $this->productionOrderService->registerOrUpdateMmeaProduct([
            'no_po' => $request->no_po,
            'no_obc' => $request->no_obc,
            'produk' => $request->produk,
            'jml_lbr' => $request->jml_lbr
        ]);
    }

    public function qcData(int $nomor_po)
    {
        return GeneratedLabelsMmea::where('nomor_po',$nomor_po)
                    ->orderBy('nomor_rim')
                    ->get();
    }
}
