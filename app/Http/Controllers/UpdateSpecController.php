<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Specification;
use Illuminate\Http\JsonResponse;

/**
 * Controller untuk mengupdate spesifikasi produk
 *
 * Controller ini bertanggung jawab untuk:
 * - Memperbarui atau membuat data spesifikasi produk baru
 * - Memproses data dalam format batch/multiple records
 * - Menangani error dan memberikan response yang sesuai
 *
 * @package App\Http\Controllers
 */
class UpdateSpecController extends Controller
{
    /**
     * Memperbarui atau membuat data spesifikasi produk
     *
     * Method ini akan:
     * 1. Memproses setiap item dari request array
     * 2. Melakukan validasi data yang diperlukan
     * 3. Mengupdate atau membuat record baru di database
     * 4. Mengembalikan response dalam format JSON
     *
     * @param Request $request Request berisi array data spesifikasi
     * @return JsonResponse Response status operasi dan data
     */
    public function updateSpec(Request $request): JsonResponse
    {
        try {
            // Memproses setiap item dalam request array
            foreach ($request->all() as $noPo => $spesifikasi) {
                // Skip jika data tidak lengkap
                if (!isset($noPo) || !isset($spesifikasi['no_obc'])) {
                    continue;
                }

                // Update atau buat spesifikasi baru
                Specification::updateOrCreate(
                    [
                        'no_po'  => $noPo,
                        'no_obc' => $spesifikasi['no_obc']
                    ],
                    [
                        'seri'   => $spesifikasi['seri'],
                        'type'   => $spesifikasi['jenis'],
                        'rencet' => $spesifikasi['rencet'],
                        'mesin'  => $spesifikasi['mesin']
                    ]
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil memperbarui spesifikasi',
                'data' => $request->all()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui spesifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
