<?php
namespace App\Services;

use App\Models\GeneratedLabels;
use Illuminate\Support\Facades\DB;

class PrintLabelService
{
    public function populateLabelForRegisteredPo ($productionOrder)
    {
        DB::transaction(function () use ($productionOrder) {

            $sumRim = max(floor($productionOrder->jml_lembar / 1000), 1);
            $periksa1 = strtoupper($productionOrder->periksa1) ?? null;
            $periksa2 = strtoupper($productionOrder->periksa2) ?? null;

            for ($i = 1; $i <= $sumRim; $i++) {
                foreach (['Kiri', 'Kanan'] as $potongan) {
                    GeneratedLabels::create(
                        [
                            'no_po_generated_products'  => $productionOrder->no_po,
                            'no_rim'    => $i,
                            'potongan'  => $potongan,
                            'np_users'  => $periksa1,
                            'periksa2'  => $periksa2,
                            'start'     => now(),
                            'finish'    => $periksa2 == null ? null : now(),
                            'workstation' => $productionOrder->team,
                        ]
                    );
                }
            }
        });
    }

    public function finishPreviousUserSession(string $npPegawai) : void
    {
        GeneratedLabels::where('np_users', $npPegawai)
            ->whereNull('finish')
            ->update([
                'np_users' => $npPegawai,
                'finish'   => now()
            ]);
    }



}
