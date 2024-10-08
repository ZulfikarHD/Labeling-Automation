<?php

namespace App\Http\Controllers;

use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use App\Models\Specification;
use App\Traits\UpdateStatusProgress;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PrintLabelPersonalController extends Controller
{
    use UpdateStatusProgress;

    /**
     * Menyimpan data label yang dihasilkan berdasarkan permintaan.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $npPegawai = strtoupper($request->rfid);

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
    public function registerNoPo(string $noPo): GeneratedProducts
    {
        $detailProduct = Specification::where('no_po', $noPo)->first();

        $sumRim = max(floor($detailProduct->rencet / 1000), 1);
        $endRim = 0 + $sumRim;

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
                GeneratedLabels::updateOrCreate(
                    [
                        'no_po_generated_products'  => $registeredProduct->no_po,
                        'no_rim'    => $i,
                        'potongan'  => $potongan,
                    ],
                    [
                        'np_users'  => $npPegawai,
                        'start'     => now(),
                        'finish'    => null,
                        'workstation' => 3,
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
