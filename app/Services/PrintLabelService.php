<?php

namespace App\Services;

use App\Models\DataInschiet;
use App\Models\GeneratedLabels;
use Illuminate\Support\Facades\DB;

/**
 * Service class untuk menangani operasi pencetakan label
 *
 * Service ini mengelola pembuatan dan penanganan label untuk order produksi,
 * termasuk perhitungan inschiet dan populasi label untuk PO yang terdaftar.
 */
class PrintLabelService
{
    private const POTONGAN_TYPES = ['Kiri', 'Kanan'];
    private const INSCHIET_RIM_NUMBER = 999;
    private const SHEETS_PER_RIM = 1000;

    /**
     * Mengisi label untuk order produksi yang terdaftar
     *
     * @param object $dataPo Objek data order produksi
     * @return void
     */
    public function populateLabelForRegisteredPo($dataPo): void
    {
        $sumRim = max(floor($dataPo['jml_lembar'] / self::SHEETS_PER_RIM), 1);

        if ($this->shouldGenerateLabels($dataPo['jml_lembar'])) {
            $this->generateLabels($dataPo, $sumRim);
        }

        $this->insertInschiet(
            $dataPo['po'],
            $dataPo['jml_lembar'],
            $dataPo['periksa1'],
            $dataPo['periksa2'],
            $dataPo['team']
        );
    }

    /**
     * Menentukan apakah label harus dibuat berdasarkan total lembar
     *
     * @param int $totalSheets Total jumlah lembar
     * @return bool
     */
    private function shouldGenerateLabels(int $totalSheets): bool
    {
        return divnum($totalSheets, self::SHEETS_PER_RIM) > 1;
    }

    /**
     * Membuat label untuk order produksi
     *
     * @param object $dataPo Data order produksi
     * @param int $sumRim Total jumlah rim
     * @return void
     * @throws \Exception
     */
    private function generateLabels($dataPo, int $sumRim): void
    {
        try {
            DB::transaction(function () use ($dataPo, $sumRim) {
                $periksa1 = $this->formatPeriksaName($dataPo->periksa1);
                $periksa2 = $this->formatPeriksaName($dataPo->periksa2);

                for ($i = 1; $i <= $sumRim; $i++) {
                    foreach (self::POTONGAN_TYPES as $potongan) {
                        $this->createLabel($dataPo->no_po, $i, $potongan, $periksa1, $periksa2, $dataPo->team);
                    }
                }
            });
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Membuat satu record label
     *
     * @param int $noPo Nomor order produksi
     * @param int $rimNumber Nomor rim
     * @param string $potongan Tipe potongan (Kiri/Kanan)
     * @param string|null $periksa1 Pemeriksa pertama
     * @param string|null $periksa2 Pemeriksa kedua
     * @param int $team ID Tim
     * @return void
     */
    public function createLabel(int $noPo, int $rimNumber, string $potongan, ?string $periksa1, ?string $periksa2, int $team): void
    {
        if($rimNumber == 999) {
            $this->updateInschietData($noPo,null,$periksa1,$potongan);
        }

        GeneratedLabels::updateOrcreate(
            [
                'no_po_generated_products' => $noPo,
                'no_rim' => $rimNumber,
                'potongan' => $potongan,
            ],
            [
                'np_users' => $this->formatPeriksaName($periksa1),
                'periksa2' => $this->formatPeriksaName($periksa2),
                'start' => now(),
                'finish' => $periksa2 ? now() : null,
                'workstation' => $team,
        ]);

    }

    /**
     * Memasukkan data inschiet untuk order produksi
     *
     * @param int $noPo Nomor order produksi
     * @param int $lembar Jumlah lembar
     * @param string|null $periksa1 Pemeriksa pertama
     * @param string|null $periksa2 Pemeriksa kedua
     * @param int|null $workstation ID Workstation
     * @return void
     * @throws \Exception
     */
    public function insertInschiet(int $noPo, int $lembar, ?string $periksa1 = null, ?string $periksa2 = null, ?int $workstation = null): void
    {
        $calcInschiet = $lembar % self::SHEETS_PER_RIM;

        if ($calcInschiet <= 0) {
            return;
        }

        try {
            DB::transaction(function () use ($noPo, $calcInschiet, $periksa1, $periksa2, $workstation) {
                $formattedPeriksa1 = $this->formatPeriksaName($periksa1);
                $formattedPeriksa2 = $this->formatPeriksaName($periksa2);

                $this->updateInschietData($noPo, $calcInschiet, $formattedPeriksa1, null);
                $this->createInschietLabels($noPo, $formattedPeriksa1, $formattedPeriksa2, $workstation);
            });
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Memperbarui data inschiet untuk order produksi
     *
     * @param int $noPo Nomor order produksi
     * @param int $calcInschiet Nilai inschiet yang dihitung
     * @param string|null $periksa1 Pemeriksa pertama
     * @return void
     */
    private function updateInschietData(int $noPo, ?int $calcInschiet = null , ?string $periksa1, ?string $potongan = null): void
    {
        $periksa1 = $this->formatPeriksaName($periksa1);

        if($potongan == "Kiri") {
            DataInschiet::where('no_po',$noPo)
                    ->update(['np_kiri' => $periksa1]);
        } elseif($potongan == "Kanan") {
            DataInschiet::where('no_po',$noPo)
                    ->update(['np_kanan' => $periksa1]);
        } else {
            DataInschiet::updateOrCreate(
                ['no_po' => $noPo],
                [
                    'inschiet' => $calcInschiet,
                    'np_kiri' => $periksa1,
                    'np_kanan' => $periksa1,
                ]
            );
        }
    }

    /**
     * Membuat label inschiet untuk kedua sisi
     *
     * @param int $noPo Nomor order produksi
     * @param string|null $periksa1 Pemeriksa pertama
     * @param string|null $periksa2 Pemeriksa kedua
     * @param int|null $workstation ID Workstation
     * @return void
     */
    private function createInschietLabels(int $noPo, ?string $periksa1, ?string $periksa2, ?int $workstation): void
    {
        $periksa1 = $this->formatPeriksaName($periksa1);
        $periksa2 = $this->formatPeriksaName($periksa2);

        foreach (self::POTONGAN_TYPES as $potongan) {
            GeneratedLabels::updateOrCreate(
                [
                    'no_po_generated_products' => $noPo,
                    'no_rim' => self::INSCHIET_RIM_NUMBER,
                    'potongan' => $potongan,
                ],
                [
                    'np_users' => $periksa1,
                    'periksa2' => $periksa2,
                    'start' => $periksa1 ? now() : null,
                    'workstation' => $workstation,
                ]
            );
        }
    }

    /**
     * Format nama pemeriksa menjadi huruf besar
     *
     * @param string|null $name Nama pemeriksa
     * @return string|null
     */
    private function formatPeriksaName(?string $name): ?string
    {
        return $name ? strtoupper($name) : null;
    }

    /**
     * Menyelesaikan sesi pengguna sebelumnya dengan memperbarui timestamp finish
     *
     * @param string $npPegawai Nomor pegawai
     * @return void
     */
    public function finishPreviousUserSession(string $npPegawai): void
    {
        GeneratedLabels::where('np_users', $npPegawai)
            ->whereNull('finish')
            ->update(['finish' => now()]);
    }
}
