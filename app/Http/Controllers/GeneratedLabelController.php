<?php

namespace App\Http\Controllers;

use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

/**
 * Controller untuk mengelola label yang digenerate
 *
 * Controller ini bertanggung jawab untuk:
 * - Update data label
 * - Mengambil data label berdasarkan nomor PO
 * - Menambah rim baru ke PO yang sudah ada
 * - Batch delete/reset label
 *
 * Tech Stack:
 * - Laravel 10 untuk backend
 * - Database transaction untuk data consistency
 * - Logging untuk error tracking
 * - Request validation
 *
 * Flow Aplikasi:
 * 1. User dapat update status label (np_users, start/finish time)
 * 2. User dapat menambah rim baru ke PO
 * 3. User dapat mereset status label secara batch
 * 4. System menjaga konsistensi data dengan transaction
 */
class GeneratedLabelController extends Controller
{
    /**
     * Update data label yang sudah ada
     *
     * Flow:
     * 1. Validasi request data
     * 2. Jalankan dalam transaction
     * 3. Reset status PO jika diperlukan
     * 4. Update data label
     *
     * @param Request $request Data update label
     * @return \Illuminate\Http\JsonResponse Response status operasi
     */
    public function update(Request $request)
    {
        $validatedData = $this->validateUpdateRequest($request);
        if ($validatedData->fails()) {
            return $this->validationErrorResponse($validatedData->errors());
        }

        return DB::transaction(function () use ($request) {
            try {
                $label = GeneratedLabels::findOrFail($request->id);

                if($request->np_users === null && $request->np_user_p2 === null) {
                    $this->resetProductionOrderStatus($label->no_po_generated_products);
                }

                $label->update($this->prepareUpdateData($request));

                return $this->successResponse('Label berhasil diperbarui');
            } catch (\Exception $e) {
                Log::error('Error updating label: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Mengambil data label untuk suatu PO dengan query yang dioptimasi
     *
     * Optimasi:
     * - Select kolom spesifik yang dibutuhkan
     * - Ordering by no_rim untuk display
     * - Array response untuk performa
     *
     * @param string $po Nomor PO yang dicari
     * @return \Illuminate\Http\JsonResponse Data label dalam format JSON
     */
    public function getLabels(string $po)
    {
        try {
            $labels = GeneratedLabels::query()
                ->where('no_po_generated_products', $po)
                ->select([
                    'id',
                    'no_po_generated_products',
                    'no_rim',
                    'np_users',
                    'np_user_p2',
                    'potongan',
                    'start',
                    'finish'
                ])
                ->orderBy('no_rim')
                ->get()
                ->toArray();

            return response()->json($labels);
        } catch (\Exception $e) {
            Log::error('Error fetching labels: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil data label');
        }
    }

    /**
     * Menambahkan rim baru ke PO yang sudah ada
     *
     * Flow:
     * 1. Validasi request data
     * 2. Reset status PO
     * 3. Cek duplikasi rim
     * 4. Create label baru dalam transaction
     *
     * @param Request $request Data rim baru
     * @return \Illuminate\Http\JsonResponse Response status operasi
     */
    public function addRim(Request $request)
    {
        $validatedData = $this->validateAddRimRequest($request);

        if ($validatedData->fails()) {
            return $this->validationErrorResponse($validatedData->errors());
        }

        $this->resetProductionOrderStatus($request->no_po);

        return DB::transaction(function () use ($request) {
            try {
                $productionOrder = $this->getProductionOrder($request->no_po);

                if ($this->rimExists($request)) {
                    return $this->errorResponse('Rim sudah ada dalam database', 422);
                }

                $this->createRimLabels($request, $productionOrder);

                return $this->successResponse('Label berhasil ditambahkan');
            } catch (\Exception $e) {
                Log::error('Error adding rim: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Reset status PO ke status awal
     *
     * @param string $noPo Nomor PO yang akan direset
     */
    private function resetProductionOrderStatus(string $noPo)
    {
        $productionOrder = GeneratedProducts::where('no_po', $noPo)->firstOrFail();
        if($productionOrder->status === 2) {
            $productionOrder->update([
                'status' => 1
            ]);
        }
    }

    /**
     * Validasi data untuk update label
     */
    private function validateUpdateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'id' => 'required|exists:generated_labels,id',
            'np_users' => 'nullable|string|max:4',
            'np_user_p2' => 'nullable|string|max:4',
            'start' => 'nullable|date',
            'finish' => 'nullable|date',
            'team' => 'required|integer'
        ]);
    }

    /**
     * Validasi data untuk penambahan rim
     */
    private function validateAddRimRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'no_po' => 'required|integer',
            'no_rim' => 'required|integer|min:1',
            'potongan' => 'required|in:both,Kiri,Kanan',
            'team' => 'required|integer'
        ]);
    }

    /**
     * Menyiapkan data untuk update
     */
    private function prepareUpdateData(Request $request): array
    {
        return [
            'np_users' => $request->np_users,
            'np_user_p2' => $request->np_user_p2,
            'start' => $request->start,
            'finish' => $request->finish,
            'workstation' => $request->team
        ];
    }

    /**
     * Mengambil data PO berdasarkan nomor
     */
    private function getProductionOrder(int $noPo)
    {
        return GeneratedProducts::where('no_po', $noPo)->firstOrFail();
    }

    /**
     * Cek apakah rim sudah ada di database
     */
    private function rimExists(Request $request): bool
    {
        $query = GeneratedLabels::where('no_po_generated_products', $request->no_po)
            ->where('no_rim', $request->no_rim);

        if ($request->potongan === 'both') {
            return $query->whereIn('potongan', ['Kiri', 'Kanan'])->exists();
        }

        return $query->where('potongan', $request->potongan)->exists();
    }

    /**
     * Membuat label untuk rim
     */
    private function createRimLabels(Request $request, GeneratedProducts $productionOrder)
    {
        if ($request->potongan === 'both') {
            $this->createBothSideLabels($request);
            $this->updateProductionOrder($productionOrder, 2);
        } else {
            $this->createSingleLabel($request);
            $this->updateProductionOrder($productionOrder, 1);
        }
    }

    /**
     * Membuat label untuk kedua sisi
     */
    private function createBothSideLabels(Request $request)
    {
        $now = now();
        GeneratedLabels::insert([
            [
                'no_po_generated_products' => $request->no_po,
                'no_rim' => $request->no_rim,
                'potongan' => 'Kiri',
                'workstation' => $request->team,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'no_po_generated_products' => $request->no_po,
                'no_rim' => $request->no_rim,
                'potongan' => 'Kanan',
                'workstation' => $request->team,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);
    }

    /**
     * Membuat label untuk satu sisi
     */
    private function createSingleLabel(Request $request)
    {
        GeneratedLabels::create([
            'no_po_generated_products' => $request->no_po,
            'no_rim' => $request->no_rim,
            'potongan' => $request->potongan,
            'workstation' => $request->team
        ]);
    }

    /**
     * Update jumlah rim pada PO
     */
    private function updateProductionOrder(GeneratedProducts $productionOrder, int $increment)
    {
        $productionOrder->update([
            'sum_rim' => $productionOrder->sum_rim + $increment,
            'end_rim' => $productionOrder->end_rim + $increment,
        ]);
    }

    /**
     * Response untuk operasi sukses
     */
    private function successResponse(string $message)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }

    /**
     * Response untuk operasi error
     */
    private function errorResponse(string $message, int $code = 500)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $code);
    }

    /**
     * Response untuk error validasi
     */
    private function validationErrorResponse($errors)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Data validasi tidak valid',
            'errors' => $errors
        ], 422);
    }

    /**
     * Batch delete/reset status label
     *
     * Flow:
     * 1. Reset data label dalam transaction
     * 2. Reset status PO
     * 3. Return response sukses/error
     *
     * @param Request $request Data label yang akan direset
     * @return \Illuminate\Http\JsonResponse Response status operasi
     */
    public function batchDelete(Request $request)
    {
        try {
            DB::beginTransaction();

            GeneratedLabels::whereIn('id', $request->ids)
                ->update([
                    'np_users' => null,
                    'np_user_p2' => null,
                    'start' => null,
                    'finish' => null,
                ]);

            DB::commit();

            $this->resetProductionOrderStatus($request->no_po);

            return response()->json([
                'status' => 'success',
                'message' => 'Label berhasil direset'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mereset label',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
