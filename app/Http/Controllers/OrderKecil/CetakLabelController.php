<?php

namespace App\Http\Controllers\OrderKecil;

use App\Http\Controllers\Controller;
use App\Models\GeneratedLabelsPersonal;
use App\Traits\UpdateStatusProgress;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Specification;
use App\Models\Workstations;
use App\Services\ProductionOrderService;
use App\Services\SpecificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CetakLabelController extends Controller
{
    use UpdateStatusProgress;

    public function index()
    {
        $listTeam = Workstations::select('id','workstation')->get();

        return Inertia::render('OrderKecil/CetakLabel',[
            'listTeam'  => $listTeam,
        ]);
    }

    public function show(Int $no_po, SpecificationService $specificationService)
    {
        return $specificationService->getSpecByNomorPo($no_po);
    }

    /**
     * Menyimpan data label yang dihasilkan berdasarkan permintaan.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $npPegawai = strtoupper($request->rfid);
        $npPeriksa2 = strtoupper($request->periksa2);

        $registeredProduct = $this->registerNoPo($request->po);
        $this->generateLabels($npPegawai, $registeredProduct);
        $this->finishPreviousSession($npPegawai);
        $this->updateProgress($request->po, $this->countNullNp($request->po) > 0 ? 1 : 2);

        return redirect()->back();
    }

    /**
     * Mendaftarkan nomor PO dan mengembalikan produk yang terdaftar.
     *
     * @param string $noPo
     * @return \App\Models\GeneratedProducts
     */
    public function registerNoPo(string $noPo, ProductionOrderService $productionOrder)
    {
        $detailProduct = Specification::where('no_po', $noPo)->first();

        $sumRim = max(floor($detailProduct->rencet / 1000), 1);
        $endRim = 0 + $sumRim;

        if($productionOrder->cekPoTerdaftar($noPo) === false)
        {
            $registeredProduct = GeneratedProducts::updateOrCreate(
                [
                    'no_po' => $noPo,
                ],
                [
                    'no_obc'  => $detailProduct->no_obc,
                    'type'    => "PCHT",
                    'status'  => 1,
                    'sum_rim' => $sumRim,
                    'start_rim' => 1,
                    'end_rim'   => $endRim,
                    'assigned_team' => 3,
                ]
            );

            return $registeredProduct;
        } else {
            return redirect()->back()->with('po_exist','PO Sudah Terdaftar');
        }

    }



    /**
     * Menghasilkan label berdasarkan produk yang terdaftar.
     *
     * @param string $npPegawai
     * @param \App\Models\GeneratedProducts $registeredProduct
     * @return void
     */
    public function generateLabels(string $npPegawai, GeneratedProducts $registeredProduct): void
    {
        for ($i = 1; $i <= $registeredProduct->sum_rim; $i++) {
            foreach (['Kiri', 'Kanan'] as $potongan) {
                GeneratedLabelsPersonal::updateOrCreate(
                    [
                        'no_po'  => $registeredProduct->no_po,
                        'no_rim'    => $i,
                        'potongan'  => $potongan,
                    ],
                    [
                        'np_users'  => $npPegawai,
                        'start'     => now(),
                        'finish'    => null,
                        'workstation' => 3,
                        'jml_lembar' => $i == $registeredProduct->sum_rim ? $registeredProduct->sum_rim % 500 : 500,
                    ]
                );
            }
        }
    }

    /**
     * Menyelesaikan sesi sebelumnya berdasarkan NP Pegawai.
     *
     * @param string $npPegawai
     * @return void
     */
    public function finishPreviousSession(string $npPegawai): void
    {
        GeneratedLabels::where('np_users', $npPegawai)
            ->whereNull('finish')
            ->update([
                'np_users' => $npPegawai,
                'finish'   => now()
            ]);

        GeneratedLabelsPersonal::where('np_users', $npPegawai)
            ->whereNull('finish')
            ->update([
                'np_users' => $npPegawai,
                'finish'   => now()
            ]);
    }

    /**
     * Menghitung jumlah label yang tidak memiliki NP Pegawai.
     *
     * @param string $noPo
     * @return int
     */
    private function countNullNp(string $noPo): int
    {
        return GeneratedLabels::where('no_po_generated_products', $noPo)
            ->whereNull('np_users')
            ->count();
    }
}
