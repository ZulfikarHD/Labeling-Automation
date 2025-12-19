<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Service untuk integrasi dengan Sirine API
 *
 * Service ini bertanggung jawab untuk:
 * - Mengambil data spesifikasi PO dari Sirine
 * - Caching data untuk performa
 * - Error handling untuk API eksternal
 *
 * @package App\Services
 */
class SirineApiService
{
    /**
     * Base URL Sirine API
     */
    private const SIRINE_API_BASE_URL = 'https://sirine.peruri.co.id/sirine/api';

    /**
     * Cache duration dalam menit
     */
    private const CACHE_DURATION = 30;

    /**
     * Mengambil detail spesifikasi order berdasarkan nomor PO
     *
     * Data yang dikembalikan:
     * - no_po: Nomor Production Order
     * - no_obc: Nomor Order Bea Cukai
     * - jenis: Jenis produk (P/D)
     * - rencet: Jumlah lembar yang akan dicetak
     * - mesin: Nomor mesin cetak
     * - desain: Tahun desain
     * - Dan field lainnya dari Sirine
     *
     * @param int $no_po Nomor Production Order
     * @return array Data spesifikasi order
     * @throws \Exception Jika data tidak ditemukan atau API error
     */
    public function getOrderSpecification(int $no_po): array
    {
        try {
            // Cek cache terlebih dahulu
            $cacheKey = "sirine_order_spec_{$no_po}";

            return Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($no_po) {
                $response = Http::timeout(10)
                    ->get(self::SIRINE_API_BASE_URL . "/detail-order-pcht/{$no_po}");

                if ($response->failed()) {
                    Log::error('Sirine API error', [
                        'no_po' => $no_po,
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);

                    throw new \Exception("Gagal mengambil data dari Sirine untuk PO: {$no_po}");
                }

                $data = $response->json();

                // Validasi data yang dikembalikan
                if (!isset($data['no_po']) || !isset($data['no_obc'])) {
                    throw new \Exception("Data tidak lengkap dari Sirine untuk PO: {$no_po}");
                }

                return $data;
            });

        } catch (\Exception $e) {
            Log::error('Error fetching from Sirine API', [
                'no_po' => $no_po,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Mengambil data spesifikasi dalam format yang compatible dengan local Specification model
     *
     * @param int $no_po Nomor Production Order
     * @return array Data dalam format Specification model
     */
    public function getSpecificationFormatted(int $no_po): array
    {
        $data = $this->getOrderSpecification($no_po);

        // Ekstrak seri dari no_obc (karakter ke-5)
        $seri = isset($data['no_obc'][4]) && $data['no_obc'][4] <= '3'
            ? $data['no_obc'][4]
            : '1';

        return [
            'no_po' => $data['no_po'],
            'no_obc' => $data['no_obc'],
            'seri' => $seri,
            'type' => $data['jenis'] ?? 'P',
            'rencet' => $data['rencet'],
            'mesin' => $data['mesin'] ?? '-',
        ];
    }

    /**
     * Clear cache untuk nomor PO tertentu
     * Berguna ketika ada update data
     *
     * @param int $no_po Nomor Production Order
     * @return void
     */
    public function clearCache(int $no_po): void
    {
        Cache::forget("sirine_order_spec_{$no_po}");
    }

    /**
     * Validasi apakah nomor PO ada di Sirine
     *
     * @param int $no_po Nomor Production Order
     * @return bool True jika PO ditemukan
     */
    public function validatePoExists(int $no_po): bool
    {
        try {
            $this->getOrderSpecification($no_po);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

