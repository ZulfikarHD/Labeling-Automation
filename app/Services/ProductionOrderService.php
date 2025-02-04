<?php

namespace App\Services;

use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * Service untuk mengelola Production Order (PO)
 *
 * Service ini bertanggung jawab untuk:
 * - Registrasi PO baru
 * - Pengecekan status PO
 * - Manajemen label PCHT
 * - Perhitungan rim
 */
class ProductionOrderService
{
    private const SHEETS_PER_RIM = 500; // Jumlah lembar kertas per rim
    private const MIN_RIM = 1; // Jumlah minimum rim yang diperbolehkan
    private const PRODUCT_TYPE = 'PCHT'; // Tipe produk (Pita Cukai Hasil Tembakau)
    private const INITIAL_STATUS = 0; // Status awal untuk PO baru

    /**
     * Mendaftarkan Production Order baru ke dalam sistem
     *
     * Method ini melakukan:
     * 1. Validasi duplikasi nomor PO
     * 2. Perhitungan total rim berdasarkan jumlah lembar
     * 3. Penyimpanan data PO dalam transaksi database
     *
     * @param array $productionOrder Data PO dengan format:
     *                              [
     *                                'po' => int,         // Nomor PO
     *                                'obc' => string,     // Nomor OBC
     *                                'jml_lembar' => int, // Total lembar
     *                                'start_rim' => int,  // Nomor rim awal
     *                                'end_rim' => int,    // Nomor rim akhir
     *                                'team' => string     // ID tim yang ditugaskan
     *                              ]
     * @throws \Exception Ketika nomor PO sudah terdaftar dalam sistem
     */
    public function registerProductionOrder(array $productionOrder): void
    {
        // Validasi duplikasi nomor PO
        if ($this->registeredProductionOrder($productionOrder['po'])->exists()) {
            throw new \Exception('Nomor PO sudah terdaftar dalam sistem');
        }

        // Eksekusi dalam database transaction untuk menjamin atomicity
        DB::transaction(function () use ($productionOrder) {
            $sumRim = $this->calculateTotalRims($productionOrder['jml_lembar']);

            // Insert data PO baru
            GeneratedProducts::create([
                'no_po' => $productionOrder['po'],
                'no_obc' => $productionOrder['obc'],
                'type' => self::PRODUCT_TYPE,
                'status' => self::INITIAL_STATUS,
                'sum_rim' => $sumRim,
                'start_rim' => $productionOrder['start_rim'],
                'end_rim' => $productionOrder['end_rim'],
                'assigned_team' => $productionOrder['team'],
            ]);
        });
    }

    /**
     * Menghitung total rim berdasarkan jumlah lembar kertas
     *
     * Rumus: total_rim = max(floor(total_lembar / lembar_per_rim), minimum_rim)
     *
     * @param int $totalSheets Total lembar kertas
     * @return int Jumlah rim yang dihitung
     */
    private function calculateTotalRims(int $totalSheets): int
    {
        return max(floor($totalSheets / self::SHEETS_PER_RIM), self::MIN_RIM);
    }

    /**
     * Mengambil query builder untuk mencari PO berdasarkan nomor
     *
     * @param int $no_po Nomor PO yang dicari
     * @return Builder Query builder untuk tabel generated_products
     */
    public function registeredProductionOrder(int $no_po): Builder
    {
        return GeneratedProducts::where('no_po', $no_po);
    }

    /**
     * Mengambil query builder untuk label PCHT berdasarkan nomor PO
     *
     * @param int $no_po Nomor PO yang dicari
     * @return Builder Query builder untuk tabel generated_labels
     */
    public function cekLabelPchtTerdaftar(int $no_po): Builder
    {
        return GeneratedLabels::where('no_po_generated_products', $no_po);
    }

    /**
     * Mengambil daftar nomor rim dan potongan untuk PCHT
     *
     * Data diurutkan berdasarkan nomor rim secara ascending
     *
     * @param int $no_po Nomor PO yang dicari
     * @return Builder Query builder dengan select no_rim dan potongan
     */
    public function getListNomorRimPcht(int $no_po): Builder
    {
        return $this->cekLabelPchtTerdaftar($no_po)
            ->select('no_rim', 'potongan')
            ->orderBy('no_rim');
    }

    /**
     * Memeriksa status penyelesaian PO
     *
     * PO dianggap selesai jika semua label telah diproses
     * (ditandai dengan terisinya kolom np_users)
     *
     * @param int $no_po Nomor PO yang diperiksa
     * @return bool True jika semua label telah diproses, false jika belum
     */
    public function isPoFinished(int $no_po): bool
    {
        return $this->cekLabelPchtTerdaftar($no_po)
            ->where(function ($query) {
                $query->whereNull('np_users')
                    ->orWhere('np_users', '');
            })
            ->count() === 0;
    }
}
