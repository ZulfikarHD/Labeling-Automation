<?php

namespace App\Http\Controllers;

use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class GeneratedLabelController extends Controller
{
    /**
     * Update a generated label record
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * Get labels for a production order with optimized query
     *
     * @param string $po
     * @return \Illuminate\Http\JsonResponse
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
     * Add new rim(s) to a production order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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

    private function resetProductionOrderStatus(string $noPo)
    {
        $productionOrder = GeneratedProducts::where('no_po', $noPo)->firstOrFail();
        if($productionOrder->status === 2) {
            $productionOrder->update([
                'status' => 1
            ]);
        } else {
        // do nothing
        }
    }

    /**
     * Validate update request data
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
     * Validate add rim request data
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
     * Prepare data for update
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
     * Get production order by PO number
     */
    private function getProductionOrder(int $noPo)
    {
        return GeneratedProducts::where('no_po', $noPo)->firstOrFail();
    }

    /**
     * Check if rim already exists
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
     * Create rim labels
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
     * Create labels for both sides
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
     * Create single side label
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
     * Update production order rim counts
     */
    private function updateProductionOrder(GeneratedProducts $productionOrder, int $increment)
    {
        $productionOrder->update([
            'sum_rim' => $productionOrder->sum_rim + $increment,
            'end_rim' => $productionOrder->end_rim + $increment,
        ]);
    }

    /**
     * Return success response
     */
    private function successResponse(string $message)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }

    /**
     * Return error response
     */
    private function errorResponse(string $message, int $code = 500)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $code);
    }

    /**
     * Return validation error response
     */
    private function validationErrorResponse($errors)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Data validasi tidak valid',
            'errors' => $errors
        ], 422);
    }
}
