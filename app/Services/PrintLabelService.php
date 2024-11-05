<?php

namespace App\Services;

use App\Models\DataInschiet;
use App\Models\GeneratedLabels;
use Illuminate\Support\Facades\DB;

class PrintLabelService
{
    public function populateLabelForRegisteredPo($dataPo) : void
    {
        $this->insertInschiet($dataPo->no_po,$dataPo->jml_lembar,$dataPo->periksa1,$dataPo->periksa2,$dataPo->team);
        $sumRim = max(floor($dataPo->jml_lembar / 1000), 1);

        if(divnum($dataPo->jml_lembar,1000) > 1)
        {
            try {
                DB::transaction(function () use ($dataPo, $sumRim) {
                    $periksa1 = strtoupper($dataPo->periksa1) ?? null;
                    $periksa2 = strtoupper($dataPo->periksa2) ?? null;

                        for ($i = 1; $i <= $sumRim; $i++) {
                            foreach (['Kiri', 'Kanan'] as $potongan) {
                                GeneratedLabels::create(
                                    [
                                        'no_po_generated_products'  => $dataPo->no_po,
                                        'no_rim'    => $i,
                                        'potongan'  => $potongan,
                                        'np_users'  => $periksa1,
                                        'periksa2'  => $periksa2,
                                        'start'     => now(),
                                        'finish'    => $periksa2 == null ? null : now(),
                                        'workstation' => $dataPo->team,
                                    ]
                                );
                            }
                        }
                });
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        }
    }

    public function insertInschiet(Int $noPo, Int $lembar, String $periksa1 = null, String $periksa2 = null, Int $workstation = null): void
    {
        $calcInschiet = $lembar % 1000;
        $periksa1 = strtoupper($periksa1) ?? null;
        $periksa2 = strtoupper($periksa2) ?? null;

        if($calcInschiet > 0)
        {
            try {
                DB::transaction(function () use ($periksa1, $periksa2, $workstation, $noPo, $calcInschiet) {
                    DataInschiet::updateOrCreate(
                        ['no_po' => $noPo],
                        [
                            'inschiet' => $calcInschiet,
                            'np_kiri'  => $periksa1,
                            'np_kanan' => $periksa1,
                        ]
                    );

                    foreach (['Kiri', 'Kanan'] as $potongan) {
                        GeneratedLabels::updateOrCreate(
                            [
                                'no_po_generated_products' => $noPo,
                                'no_rim' => 999,
                                'potongan' => $potongan,
                            ],
                            [
                                'np_users' => $periksa1,
                                'periksa2' => $periksa2,
                                'start'    => $periksa1 == null ? null : now(),
                                'workstation' => $workstation == null ? null : $workstation,
                            ]
                        );
                    }
                });
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        }

    }

    public function  finishPreviousUserSession(string $npPegawai): void
    {
        GeneratedLabels::where('np_users', $npPegawai)
            ->whereNull('finish')
            ->update(['finish'   => now()]);
    }
}
