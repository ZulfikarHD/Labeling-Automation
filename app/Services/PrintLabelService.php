<?php

namespace App\Services;

use App\Models\DataInschiet;
use App\Models\GeneratedLabels;
use Illuminate\Support\Facades\DB;

/**
 * Service class untuk menangani operasi pencetakan label dan manajemen data produksi
 *
 * Class ini bertanggung jawab untuk:
 * - Membuat dan mengelola label produksi (create & manage production labels)
 * - Menghitung dan memproses data inschiet (calculate & process inschiet data)
 * - Mengelola status pemeriksaan label (manage label inspection status)
 * - Mengoptimalkan performa database dengan batch processing
 *
 * Flow utama:
 * 1. Menerima data PO dari controller
 * 2. Menghitung jumlah rim berdasarkan lembar
 * 3. Generate label untuk setiap rim dan potongan
 * 4. Menangani data inschiet jika ada sisa lembar
 * 5. Menyimpan semua data ke database dalam satu transaksi
 *
 * Fitur keamanan:
 * - Menggunakan transaksi database untuk menjaga konsistensi data
 * - Validasi input sebelum penyimpanan
 * - Sanitasi nama pemeriksa dengan format standar
 *
 * Performance optimization:
 * - Batch processing untuk insert/update data
 * - Minimalisasi query dengan pengumpulan data
 * - Index pada kolom-kolom yang sering diquery
 *
 * @package App\Services
 */
class PrintLabelService
{
    /**
     * Konstanta untuk tipe potongan label
     * Digunakan untuk memastikan konsistensi value di seluruh aplikasi
     *
     * @var array
     */
    private const POTONGAN_TYPES = ['Kiri', 'Kanan'];

    /**
     * Nomor rim khusus untuk inschiet
     * Rim 999 digunakan sebagai penanda untuk lembar inschiet
     *
     * @var int
     */
    private const INSCHIET_RIM_NUMBER = 999;

    /**
     * Jumlah lembar standar per rim
     * Digunakan untuk perhitungan jumlah rim dan inschiet
     *
     * @var int
     */
    private const SHEETS_PER_RIM = 1000;

    /**
     * Method utama untuk mengisi dan membuat label PO yang terdaftar
     *
     * Alur proses:
     * 1. Hitung jumlah rim dari total lembar
     * 2. Generate label jika memenuhi syarat
     * 3. Proses data inschiet jika ada sisa lembar
     *
     * @param array $dataPo Array berisi data PO dengan struktur:
     *                      - po: (int) nomor PO
     *                      - jml_lembar: (int) jumlah lembar total
     *                      - periksa1: (string|null) NIP pemeriksa pertama
     *                      - periksa2: (string|null) NIP pemeriksa kedua
     *                      - team: (int) ID tim produksi
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
     * Cek apakah perlu generate label berdasarkan total lembar
     *
     * Rules:
     * - Generate jika hasil bagi lembar dengan SHEETS_PER_RIM > 1
     * - Gunakan divnum untuk handle pembagian dengan 0
     *
     * @param int $totalSheets Total jumlah lembar
     * @return bool True jika perlu generate label
     */
    private function shouldGenerateLabels(int $totalSheets): bool
    {
        return divnum($totalSheets, self::SHEETS_PER_RIM) > 1;
    }

    /**
     * Generate label untuk PO dengan batch processing
     *
     * Alur proses:
     * 1. Buka transaksi database
     * 2. Format nama pemeriksa
     * 3. Kumpulkan semua data label
     * 4. Insert/update secara batch
     * 5. Commit atau rollback transaksi
     *
     * @param array $dataPo Data order produksi
     * @param int $sumRim Total jumlah rim
     * @throws \Exception Jika terjadi error dalam transaksi
     */
    private function generateLabels(array $dataPo, int $sumRim): void
    {
        try {
            DB::transaction(function () use ($dataPo, $sumRim) {
                $periksa1 = $this->formatPeriksaName($dataPo['periksa1'] ?? null);
                $periksa2 = $this->formatPeriksaName($dataPo['periksa2'] ?? null);

                $labels = [];
                // Kumpulkan semua data label
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

                // Batch upsert untuk optimasi
                GeneratedLabels::upsert(
                    $labels,
                    ['no_po_generated_products', 'no_rim', 'potongan'],
                    ['np_users', 'np_user_p2', 'start', 'finish', 'workstation']
                );
            });
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mencari label berikutnya yang tersedia untuk diproses
     *
     * Alur pencarian:
     * 1. Cek potongan saat ini
     * 2. Jika potongan Kiri, cek Kanan
     * 3. Jika keduanya terisi, pindah ke rim berikutnya
     * 4. Ulangi sampai menemukan slot kosong
     *
     * @param int $noPo Nomor PO
     * @param int $rimNumber Nomor rim awal pencarian
     * @param string $potongan Tipe potongan awal (Kiri/Kanan)
     * @return array|null Array berisi rim dan potongan atau null jika tidak ditemukan
     */
    private function findNextAvailableLabel(int $noPo, int $rimNumber, string $potongan): ?array
    {
        $currentRim = $rimNumber;

        while (true) {
            // Cek potongan saat ini
            $label = GeneratedLabels::where('no_po_generated_products', $noPo)
                ->where('no_rim', $currentRim)
                ->where('potongan', $potongan)
                ->first();

            if (!$label || is_null($label->np_users)) {
                return ['rim' => $currentRim, 'potongan' => $potongan];
            }

            // Jika potongan Kiri, cek Kanan
            if ($potongan === 'Kiri') {
                $labelKanan = GeneratedLabels::where('no_po_generated_products', $noPo)
                    ->where('no_rim', $currentRim)
                    ->where('potongan', 'Kanan')
                    ->first();

                if (!$labelKanan || is_null($labelKanan->np_users)) {
                    return ['rim' => $currentRim, 'potongan' => 'Kanan'];
                }
            }

            // Pindah ke rim berikutnya
            $currentRim++;

            // Cek keberadaan rim berikutnya
            $nextRimExists = GeneratedLabels::where('no_po_generated_products', $noPo)
                ->where('no_rim', $currentRim)
                ->exists();

            if (!$nextRimExists) {
                return null;
            }

            // Reset potongan ke 'Kiri' untuk rim baru
            $potongan = 'Kiri';
        }
    }

    /**
     * Membuat atau memperbarui label tunggal
     *
     * Alur proses:
     * 1. Handle khusus untuk rim inschiet
     * 2. Cari label berikutnya yang tersedia
     * 3. Update/create label
     * 4. Update assigned team di generated_products
     *
     * @param int $noPo Nomor PO
     * @param int $rimNumber Nomor rim
     * @param string $potongan Tipe potongan
     * @param string|null $periksa1 NIP pemeriksa 1
     * @param string|null $periksa2 NIP pemeriksa 2
     * @param int $team ID tim
     * @return array Response status dan data
     */
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
     * Handle pembuatan dan update data inschiet
     *
     * Alur proses:
     * 1. Hitung sisa lembar untuk inschiet
     * 2. Jika ada sisa, buat record dalam transaksi
     * 3. Update data inschiet dan label terkait
     *
     * @param int $noPo Nomor PO
     * @param int $lembar Total lembar
     * @param string|null $periksa1 NIP pemeriksa 1
     * @param string|null $periksa2 NIP pemeriksa 2
     * @param int|null $workstation ID workstation
     * @throws \Exception Jika terjadi error dalam transaksi
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
     * Update data inschiet untuk PO tertentu
     *
     * Update dilakukan berdasarkan:
     * - Potongan spesifik jika diberikan
     * - Kedua potongan jika tidak ada potongan spesifik
     *
     * @param int $noPo Nomor PO
     * @param int|null $calcInschiet Jumlah inschiet
     * @param string|null $periksa1 NIP pemeriksa
     * @param string|null $potongan Tipe potongan
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
     * Buat label inschiet untuk kedua potongan
     *
     * Label inschiet dibuat dengan:
     * - Nomor rim khusus (999)
     * - Potongan Kiri dan Kanan
     * - Data pemeriksa dan workstation
     *
     * @param int $noPo Nomor PO
     * @param string|null $periksa1 NIP pemeriksa 1
     * @param string|null $periksa2 NIP pemeriksa 2
     * @param int|null $workstation ID workstation
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
     * Format nama pemeriksa menjadi uppercase
     *
     * Digunakan untuk standarisasi format nama di database
     *
     * @param string|null $name Nama pemeriksa
     * @return string|null Nama terformat atau null
     */
    private function formatPeriksaName(?string $name): ?string
    {
        return $name ? strtoupper($name) : null;
    }

    /**
     * Selesaikan sesi user sebelumnya
     *
     * Update timestamp finish untuk semua label:
     * - Yang belum selesai (finish = null)
     * - Milik pegawai tertentu
     *
     * @param string $npPegawai NIP pegawai
     */
    public function finishPreviousUserSession(string $npPegawai): void
    {
        GeneratedLabels::where('np_users', $npPegawai)
            ->whereNull('finish')
            ->update(['finish' => now()]);
    }
}
