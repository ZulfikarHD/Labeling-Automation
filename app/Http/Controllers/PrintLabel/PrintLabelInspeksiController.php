<?php

namespace App\Http\Controllers\PrintLabel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Specification;
use App\Models\Workstations;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;

use App\Services\ProductionOrderService;
use App\Services\PrintLabelService;

class PrintLabelInspeksiController extends Controller
{
    // Status constants for better readability
    private const STATUS_IN_PROGRESS = 1;
    private const STATUS_COMPLETED = 2;

    public function __construct(
        protected ProductionOrderService $productionOrderService,
        protected PrintLabelService $printLabelService
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

    public function getSpecification(int $no_po)
    {
        $specification = Specification::where('no_po', $no_po)->first();
        return response()->json($specification);
    }

    public function getRemainingLabelCount(int $no_po)
    {
        $isPoRegistered = GeneratedProducts::where('no_po', $no_po)->first();

        $countLabel = GeneratedLabels::where('no_po_generated_products', $no_po)
                             ->whereNull('np_users')
                             ->count();

        if ($isPoRegistered) {
            return $countLabel;
        } else {
            $specification = Specification::where('no_po', $no_po)->first();

            if (!$specification) {
                return 0;
            }

            return max(floor($specification->rencet / 500), 0);
        }
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

            // Only log completion with essential data
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
            'np1' => 'required|string|max:4',
            'np2' => 'nullable|string|max:4',
        ]);
    }

    private function ensureProductionOrderExists(array $validatedData): void
    {
        $existingLabelsCount = GeneratedLabels::where('no_po_generated_products', $validatedData['no_po'])->count();

        if ($existingLabelsCount === 0) {
            // Get specification data to create production order
            $specification = Specification::where('no_po', $validatedData['no_po'])->first();

            if (!$specification) {
                throw new \Exception('Spesifikasi untuk nomor PO tidak ditemukan');
            }

                        // Calculate rim data from specification (using 500 sheets per rim for inspection)
            $totalRims = max(floor($specification->rencet / 500), 1);

            // Transform data for ProductionOrderService
            $productionOrderData = [
                'po' => $validatedData['no_po'],
                'obc' => $specification->no_obc,
                'jml_lembar' => $specification->rencet,
                'start_rim' => 1,
                'end_rim' => $totalRims,
                'team' => $validatedData['team'],
            ];

            // Transform data for PrintLabelService
            $printLabelData = [
                'po' => $validatedData['no_po'],
                'jml_lembar' => $specification->rencet,
                'team' => $validatedData['team'],
                'start_rim' => 1,
                'end_rim' => $totalRims,
            ];

            Log::info('Creating new PO for inspection', ['no_po' => $validatedData['no_po']]);

            $this->productionOrderService->registerProductionOrder($productionOrderData);
            $this->printLabelService->populateLabelForRegisteredPo($printLabelData);
        }
    }

    private function processLabels(array $validatedData): array
    {
        $processedLabels = 0;
        $failedLabels = 0;

        for ($i = 0; $i < $validatedData['jumlah_label']; $i++) {
            $label = $this->getNextAvailableLabel($validatedData['no_po']);

            if (!$label) {
                // Only log this if we couldn't process any labels - indicates a real issue
                if ($processedLabels === 0) {
                    Log::warning('No available labels found', ['no_po' => $validatedData['no_po']]);
                }
                break;
            }

            if ($this->updateLabel($label, $validatedData)) {
                $processedLabels++;
            } else {
                $failedLabels++;
            }
        }

        $remainingLabels = $this->getRemainingLabelCount($validatedData['no_po']);

        return [
            'processed_labels' => $processedLabels,
            'failed_labels' => $failedLabels,
            'remaining_labels' => $remainingLabels
        ];
    }

    private function getNextAvailableLabel(int $no_po)
    {
        return GeneratedLabels::where('no_po_generated_products', $no_po)
                             ->whereNull('np_users')
                             ->orderBy('no_rim', 'desc')
                             ->first();
    }

    private function updateLabel($label, array $validatedData): bool
    {
        try {
            $label->update([
                'np_users' => strtoupper($validatedData['np1']),
                'np_user_p2' => strtoupper($validatedData['np2']),
                'workstation' => $validatedData['team'],
                'start' => now(),
                'finish' => now(),
            ]);

            return true;

        } catch (\Exception $e) {
            // Only log errors - successful updates don't need logging
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
        // Remove detailed logging for validation errors - they're expected user errors
        return response()->json([
            'success' => false,
            'message' => 'Data yang dikirim tidak valid',
            'errors' => $e->errors()
        ], 422);
    }

    private function systemErrorResponse(\Exception $e, Request $request): \Illuminate\Http\JsonResponse
    {
        // Keep detailed logging only for system errors - these are unexpected
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
