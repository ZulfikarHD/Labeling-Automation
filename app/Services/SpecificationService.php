<?php
namespace App\Services;

use App\Models\Specification;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengelola spesifikasi produk
 *
 * Service ini sekarang menggunakan integrasi dengan Sirine API
 * sebagai sumber data utama, dengan fallback ke database lokal
 */
class SpecificationService
{
    protected $sirineApiService;

    public function __construct(SirineApiService $sirineApiService)
    {
        $this->sirineApiService = $sirineApiService;
    }

    /**
     * Mendapatkan spesifikasi berdasarkan nomor PO
     *
     * Flow:
     * 1. Coba ambil dari Sirine API (sumber utama)
     * 2. Jika gagal, fallback ke database lokal
     * 3. Jika berhasil dari Sirine, sync ke database lokal
     *
     * @param int $no_po Nomor Production Order
     * @return array|object Data spesifikasi
     * @throws \Exception Jika data tidak ditemukan di kedua sumber
     */
    public function getSpecByNomorPo(int $no_po)
    {
        try {
            // Prioritas pertama: ambil dari Sirine API
            $spec = $this->sirineApiService->getSpecificationFormatted($no_po);

            // Sync ke database lokal untuk backup
            $this->syncToLocalDatabase($spec);

            return (object) $spec;

        } catch (\Exception $e) {
            Log::warning('Gagal mengambil dari Sirine API, menggunakan database lokal', [
                'no_po' => $no_po,
                'error' => $e->getMessage()
            ]);

            // Fallback ke database lokal
            $localSpec = Specification::where('no_po', $no_po)
                ->select('no_po', 'no_obc', 'seri', 'type', 'rencet')
                ->first();

            if (!$localSpec) {
                throw new \Exception("Spesifikasi untuk PO {$no_po} tidak ditemukan di Sirine maupun database lokal");
            }

            return $localSpec;
        }
    }

    /**
     * Sync data dari Sirine ke database lokal
     *
     * @param array $spec Data spesifikasi dari Sirine
     * @return void
     */
    private function syncToLocalDatabase(array $spec): void
    {
        try {
            Specification::updateOrCreate(
                [
                    'no_po' => $spec['no_po']
                ],
                [
                    'no_obc' => $spec['no_obc'],
                    'seri' => $spec['seri'],
                    'type' => $spec['type'],
                    'rencet' => $spec['rencet'],
                    'mesin' => $spec['mesin'] ?? '-',
                ]
            );
        } catch (\Exception $e) {
            // Log error tapi jangan throw, karena data dari Sirine sudah berhasil
            Log::error('Gagal sync spesifikasi ke database lokal', [
                'no_po' => $spec['no_po'],
                'error' => $e->getMessage()
            ]);
        }
    }
}
