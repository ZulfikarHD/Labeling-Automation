<?php

namespace App\Services;

use App\Models\DataInschiet;
use App\Models\GeneratedLabels;
use Illuminate\Support\Facades\DB;

/**
 * Service class untuk menangani operasi pencetakan label dan manajemen data produksi
 *
 * Class ini bertanggung jawab untuk:
 * - Membuat dan mengelola label produksi
 * - Menghitung dan memproses data inschiet
 * - Mengelola status pemeriksaan label
 * - Mengoptimalkan performa database dengan batch processing
 *
 * @package App\Services
 */
class PrintLabelService
{
    /**
     * Konstanta untuk tipe potongan label
     * @var array
     */
    private const POTONGAN_TYPES = ['Kiri', 'Kanan'];

    /**
     * Nomor rim khusus untuk inschiet
     * @var int
     */
    private const INSCHIET_RIM_NUMBER = 999;

    /**
     * Jumlah lembar per rim
     * @var int
     */
    private const SHEETS_PER_RIM = 1000;

    /**
     * Mengisi dan membuat label untuk order produksi yang terdaftar
     *
     * Method ini menangani:
     * - Perhitungan jumlah rim berdasarkan lembar
     * - Pembuatan label untuk setiap rim
     * - Penanganan data inschiet
     *
     * @param array $dataPo Data order produksi yang berisi:
     *                      - po: nomor PO
     *                      - jml_lembar: jumlah lembar
     *                      - periksa1: pemeriksa pertama
     *                      - periksa2: pemeriksa kedua
     *                      - team: ID tim
     * @return void
     */
    public function populateLabelForRegisteredPo(array $dataPo): void
    {
        $sumRim = max(floor($dataPo['jml_lembar'] / self::SHEETS_PER_RIM), 1);

        if ($this->shouldGenerateLabels($dataPo['jml_lembar'])) {
            $this->generateLabels($dataPo, $sumRim);
        }

        $this->insertInschiet(
            $dataPo['po'],
            $dataPo['jml_lembar'],
            $dataPo['periksa1'] ?? null,
            $dataPo['periksa2'] ?? null,
            $dataPo['team']
        );
    }

    /**
     * Menentukan apakah perlu membuat label berdasarkan total lembar
     *
     * @param int $totalSheets Total jumlah lembar
     * @return bool True jika perlu membuat label
     */
    private function shouldGenerateLabels(int $totalSheets): bool
    {
        return divnum($totalSheets, self::SHEETS_PER_RIM) > 1;
    }

    /**
     * Membuat label untuk order produksi dengan batch processing
     * untuk optimasi performa
     *
     * @param array $dataPo Data order produksi
     * @param int $sumRim Total jumlah rim
     * @throws \Exception Jika terjadi kesalahan dalam transaksi
     */
    private function generateLabels(array $dataPo, int $sumRim): void
    {
        try {
            DB::transaction(function () use ($dataPo, $sumRim) {
                $periksa1 = $this->formatPeriksaName($dataPo['periksa1'] ?? null);
                $periksa2 = $this->formatPeriksaName($dataPo['periksa2'] ?? null);

                $labels = [];
                // Kumpulkan semua label terlebih dahulu
                for ($i = $dataPo['start_rim'] ?? 1; $i <= $dataPo['end_rim'] ?? $sumRim; $i++) {
                    foreach (self::POTONGAN_TYPES as $potongan) {
                        $labels[] = [
                            'no_po_generated_products' => $dataPo['po'],
                            'no_rim' => $i,
                            'potongan' => $potongan,
                            'np_users' => $periksa1,
                            'np_user_p2' => $periksa2,
                            'start' => $periksa1 ? now() : null,
                            'finish' => $periksa2 ? now() : null,
                            'workstation' => $dataPo['team']
                        ];
                    }
                }

                // Pastikan semua data terkumpul sebelum melakukan single batch operation
                // if (count($labels) === ($sumRim * count(self::POTONGAN_TYPES))) {
                    GeneratedLabels::upsert(
                        $labels,
                        ['no_po_generated_products', 'no_rim', 'potongan'],
                        ['np_users', 'np_user_p2', 'start', 'finish', 'workstation']
                    );
                // } else {
                //     throw new \Exception('Jumlah label tidak sesuai dengan yang diharapkan');
                // }
            });
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Membuat atau memperbarui satu record label
     *
     * @param int $noPo Nomor order produksi
     * @param int $rimNumber Nomor rim
     * @param string $potongan Tipe potongan (Kiri/Kanan)
     * @param string|null $periksa1 Pemeriksa pertama
     * @param string|null $periksa2 Pemeriksa kedua
     * @param int $team ID Tim
     */
    private function findNextAvailableLabel(int $noPo, int $rimNumber, string $potongan): ?array
    {
        $currentRim = $rimNumber;

        while (true) {
            // Check current potongan
            $label = GeneratedLabels::where('no_po_generated_products', $noPo)
                ->where('no_rim', $currentRim)
                ->where('potongan', $potongan)
                ->first();

            if (!$label || is_null($label->np_users)) {
                return ['rim' => $currentRim, 'potongan' => $potongan];
            }

            // If current potongan is 'Kiri', check 'Kanan'
            if ($potongan === 'Kiri') {
                $labelKanan = GeneratedLabels::where('no_po_generated_products', $noPo)
                    ->where('no_rim', $currentRim)
                    ->where('potongan', 'Kanan')
                    ->first();

                if (!$labelKanan || is_null($labelKanan->np_users)) {
                    return ['rim' => $currentRim, 'potongan' => 'Kanan'];
                }
            }

            // Move to next rim
            $currentRim++;

            // Check if next rim exists
            $nextRimExists = GeneratedLabels::where('no_po_generated_products', $noPo)
                ->where('no_rim', $currentRim)
                ->exists();

            if (!$nextRimExists) {
                return null;
            }

            // Reset potongan to 'Kiri' for next rim
            $potongan = 'Kiri';
        }
    }

    public function createLabel(int $noPo, int $rimNumber, string $potongan, ?string $periksa1, ?string $periksa2, int $team): array
    {
        if ($rimNumber === self::INSCHIET_RIM_NUMBER) {
            $this->updateInschietData($noPo, null, $periksa1, $potongan);
            $nextLabel = ['rim' => $rimNumber, 'potongan' => $potongan];
        } else {
            $nextLabel = $this->findNextAvailableLabel($noPo, $rimNumber, $potongan);
        }

        if (!$nextLabel) {
            return [
                'status' => 'error',
                'message' => 'Order sudah selesai'
            ];
        }

        GeneratedLabels::updateOrCreate(
            [
                'no_po_generated_products' => $noPo,
                'no_rim' => $nextLabel['rim'],
                'potongan' => $nextLabel['potongan'],
            ],
            [
                'np_users' => $this->formatPeriksaName($periksa1),
                'np_user_p2' => $this->formatPeriksaName($periksa2),
                'start' => $periksa1 ? now() : null,
                'finish' => $periksa2 ? now() : null,
                'workstation' => $team,
            ]
        );

        DB::table('generated_products')
                ->where('no_po', $noPo)
                ->update(['assigned_team' => $team]);

        return [
            'status' => 'success',
            'message' => 'Label berhasil dibuat',
            'data' => [
                'no_rim' => $nextLabel['rim'],
                'potongan' => $nextLabel['potongan']
            ]
        ];
    }

    /**
     * Menangani pembuatan dan pembaruan data inschiet
     *
     * @param int $noPo Nomor order produksi
     * @param int $lembar Jumlah lembar
     * @param string|null $periksa1 Pemeriksa pertama
     * @param string|null $periksa2 Pemeriksa kedua
     * @param int|null $workstation ID Workstation
     * @throws \Exception Jika terjadi kesalahan dalam transaksi
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
     * @param int|null $calcInschiet Nilai inschiet yang dihitung
     * @param string|null $periksa1 Pemeriksa pertama
     * @param string|null $potongan Tipe potongan (Kiri/Kanan)
     */
    private function updateInschietData(int $noPo, ?int $calcInschiet = null, ?string $periksa1, ?string $potongan = null): void
    {
        $periksa1 = $this->formatPeriksaName($periksa1);

        if ($potongan === "Kiri") {
            DataInschiet::where('no_po', $noPo)->update(['np_kiri' => $periksa1]);
        } elseif ($potongan === "Kanan") {
            DataInschiet::where('no_po', $noPo)->update(['np_kanan' => $periksa1]);
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
     * Membuat label inschiet untuk kedua sisi potongan
     *
     * @param int $noPo Nomor order produksi
     * @param string|null $periksa1 Pemeriksa pertama
     * @param string|null $periksa2 Pemeriksa kedua
     * @param int|null $workstation ID Workstation
     */
    private function createInschietLabels(int $noPo, ?string $periksa1, ?string $periksa2, ?int $workstation): void
    {
        $periksa1 = $this->formatPeriksaName($periksa1);
        $periksa2 = $this->formatPeriksaName($periksa2);

        $labels = [];
        foreach (self::POTONGAN_TYPES as $potongan) {
            $labels[] = [
                'no_po_generated_products' => $noPo,
                'no_rim' => self::INSCHIET_RIM_NUMBER,
                'potongan' => $potongan,
                'np_users' => $periksa1,
                'np_user_p2' => $periksa2,
                'start' => $periksa1 ? now() : null,
                'workstation' => $workstation
            ];
        }

        GeneratedLabels::upsert(
            $labels,
            ['no_po_generated_products', 'no_rim', 'potongan'],
            ['np_users', 'np_user_p2', 'start', 'workstation']
        );
    }

    /**
     * Format nama pemeriksa menjadi huruf kapital
     *
     * @param string|null $name Nama pemeriksa
     * @return string|null Nama yang sudah diformat atau null
     */
    private function formatPeriksaName(?string $name): ?string
    {
        return $name ? strtoupper($name) : null;
    }

    /**
     * Menyelesaikan sesi pengguna sebelumnya dengan memperbarui timestamp finish
     *
     * @param string $npPegawai Nomor pegawai
     */
    public function finishPreviousUserSession(string $npPegawai): void
    {
        GeneratedLabels::where('np_users', $npPegawai)
            ->whereNull('finish')
            ->update(['finish' => now()]);
    }
}
