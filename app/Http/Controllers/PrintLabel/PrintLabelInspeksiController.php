<?php

namespace App\Http\Controllers\PrintLabel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Workstations;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;

use App\Services\ProductionOrderService;
use App\Services\PrintLabelService;

class PrintLabelInspeksiController extends Controller
{
    private const STATUS_IN_PROGRESS = 1;
    private const STATUS_COMPLETED = 2;
    private const INSCHIET_RIM_NUMBER = 999;

    public function __construct(
        protected ProductionOrderService $productionOrderService,
        protected PrintLabelService $printLabelService,
    ) {}

    public function index()
    {
        $listTeam = Workstations::listWorkstation()->toArray();
        $currentTeam = Auth::user()->workstation_id;

        return Inertia::render('PrintLabel/PrintLabelInspeksi', [
            'listTeam' => $listTeam,
            'currentTeam' => $currentTeam
        ]);
    }

    public function getRemainingLabelCount(int $no_po)
    {
        return GeneratedLabels::where('no_po_generated_products', $no_po)
            ->where('no_rim', '!=', self::INSCHIET_RIM_NUMBER)
            ->whereNull('np_users')
            ->count();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateRequest($request);

        DB::beginTransaction();

        try {
            $this->ensureProductionOrderExists($validatedData);
            $this->printLabelService->finishPreviousUserSession($validatedData['np1']);

            $result = $this->processLabels($validatedData);
            $this->updateProductionOrderStatus($validatedData['no_po'], $result['remaining_labels']);

            DB::commit();

            Log::info('Label inspection completed', [
                'no_po' => $validatedData['no_po'],
                'processed' => $result['processed_labels'],
                'remaining' => $result['remaining_labels']
            ]);

            return $this->successResponse($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return $this->validationErrorResponse($e, $request);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->systemErrorResponse($e, $request);
        }
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'no_po' => 'required',
            'team' => 'required|exists:workstation,id',
            'jumlah_label' => 'required|integer|min:1',
            'np1' => 'required|string|max:5',
            'np2' => 'nullable|string|max:5',
            'no_obc' => 'required|string',
            'rencet' => 'required|integer|min:1',
        ]);
    }

    private function ensureProductionOrderExists(array $validatedData): void
    {
        $existingLabelsCount = GeneratedLabels::where('no_po_generated_products', $validatedData['no_po'])->count();

        if ($existingLabelsCount > 0) {
            return;
        }

        $rencet = $validatedData['rencet'];
        $totalRims = max(intval(floor($rencet / 1000)), 1);

        Log::info('Creating new PO for inspection', [
            'no_po' => $validatedData['no_po'],
            'rencet' => $rencet,
            'total_rims' => $totalRims,
            'expected_labels' => $totalRims * 2,
        ]);

        $productionOrderData = [
            'po' => $validatedData['no_po'],
            'obc' => $validatedData['no_obc'],
            'jml_lembar' => $rencet,
            'start_rim' => 1,
            'end_rim' => $totalRims,
            'team' => $validatedData['team'],
        ];

        $printLabelData = [
            'po' => $validatedData['no_po'],
            'jml_lembar' => $rencet,
            'team' => $validatedData['team'],
            'start_rim' => 1,
            'end_rim' => $totalRims,
        ];

        $this->productionOrderService->registerProductionOrder($productionOrderData);
        $this->printLabelService->populateLabelForRegisteredPo($printLabelData);
    }

    /**
     * Fill the last N unfilled labels (excluding inschiet) with the user's credentials.
     * Labels are taken from the highest rim number down, Kanan first then Kiri per rim.
     */
    private function processLabels(array $validatedData): array
    {
        $availableLabels = GeneratedLabels::where('no_po_generated_products', $validatedData['no_po'])
            ->where('no_rim', '!=', self::INSCHIET_RIM_NUMBER)
            ->whereNull('np_users')
            ->orderBy('no_rim', 'desc')
            ->orderBy('potongan', 'asc')
            ->limit($validatedData['jumlah_label'])
            ->get();

        $processedLabels = 0;
        $failedLabels = 0;

        foreach ($availableLabels as $label) {
            if ($this->updateLabel($label, $validatedData)) {
                $processedLabels++;
            } else {
                $failedLabels++;
            }
        }

        if ($processedLabels === 0 && $validatedData['jumlah_label'] > 0) {
            Log::warning('No available labels found', ['no_po' => $validatedData['no_po']]);
        }

        $remainingLabels = $this->getRemainingLabelCount($validatedData['no_po']);

        return [
            'processed_labels' => $processedLabels,
            'failed_labels' => $failedLabels,
            'remaining_labels' => $remainingLabels
        ];
    }

    private function updateLabel($label, array $validatedData): bool
    {
        try {
            $label->update([
                'np_users' => strtoupper($validatedData['np1']),
                'np_user_p2' => isset($validatedData['np2']) ? strtoupper($validatedData['np2']) : null,
                'workstation' => $validatedData['team'],
                'start' => now(),
                'finish' => now(),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to update label', [
                'label_id' => $label->id,
                'no_po' => $validatedData['no_po'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    private function updateProductionOrderStatus(int $no_po, int $remainingLabels): void
    {
        $status = $remainingLabels > 0 ? self::STATUS_IN_PROGRESS : self::STATUS_COMPLETED;

        GeneratedProducts::where('no_po', $no_po)->update([
            'status' => $status
        ]);
    }

    private function successResponse(array $result): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Label berhasil diproses',
            'data' => [
                'processed_labels' => $result['processed_labels'],
                'failed_labels' => $result['failed_labels'],
                'remaining_labels' => $result['remaining_labels'],
                'status' => $result['remaining_labels'] > 0 ? 'in_progress' : 'completed'
            ]
        ]);
    }

    private function validationErrorResponse(\Illuminate\Validation\ValidationException $e, Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Data yang dikirim tidak valid',
            'errors' => $e->errors()
        ], 422);
    }

    private function systemErrorResponse(\Exception $e, Request $request): \Illuminate\Http\JsonResponse
    {
        Log::error('System error in label inspection', [
            'no_po' => $request->no_po ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.',
            'error_code' => 'SYSTEM_ERROR'
        ], 500);
    }
}
