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
    public function __construct(
        protected ProductionOrderService $productionOrderService,
        protected PrintLabelService $printLabelService
    ) {}

    public function index()
    {
        $listTeam = Workstations::listWorkstation()->toArray();
        $currentTeam = Auth::user()->workstation_id;

        return Inertia::render('PrintLabel/PrintLabelInspeksi/Index', [
            'listTeam' => $listTeam,
            'currentTeam' => $currentTeam
        ]);
    }

    public function fetchDataSpec(Int $no_po)
    {
        $specPo = Specification::where('no_po', $no_po)->first();

        return response()->json($specPo);
    }

    public function calcRemainingLabel(Int $no_po)
    {
        return GeneratedLabels::where('no_po_generated_products', $no_po)
                             ->where('np_users','=',null)
                             ->count();
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validatedRequest = $request->validate([
                'no_po' => 'required',
                'team' => 'required|exists:workstation,id',
                'jumlah_label' => 'required|integer|min:1',
                'np1' => 'required|string|max:4',
                'np2' => 'nullable|string|max:4',
            ]);

            $fetchLabel = GeneratedLabels::where('no_po_generated_products', $request->no_po)->count();

            if ($fetchLabel == 0) {
                Log::info('Creating new PO and labels', ['no_po' => $request->no_po]);

                $this->productionOrderService->registerProductionOrder($validatedRequest);
                $this->printLabelService->populateLabelForRegisteredPo($validatedRequest);

                Log::info('Successfully created PO and labels', ['no_po' => $request->no_po]);
            }

            $this->printLabelService->finishPreviousUserSession($request->np1);

            $processedLabels = 0;
            $failedLabels = 0;

            Log::info('Starting label processing', [
                'no_po' => $request->no_po,
                'requested_labels' => $request->jumlah_label,
                'inspector_1' => $request->np1,
                'inspector_2' => $request->np2
            ]);

            for ($i = 0; $i < $request->jumlah_label; $i++) {
                try {
                    $storeLabel = GeneratedLabels::where('no_po_generated_products', $request->no_po)
                                                ->where('np_users', null)
                                                ->orderBy('no_rim', 'asc')
                                                ->first();

                    if ($storeLabel) {
                        $storeLabel->update([
                            'np_users' => strtoupper($request->np1),
                            'np_user_p2' => strtoupper($request->np2),
                            'workstation' => $request->team,
                            'start' => now(),
                            'finish' => now(),
                        ]);

                        $processedLabels++;

                        Log::debug('Label processed successfully', [
                            'label_id' => $storeLabel->id,
                            'no_rim' => $storeLabel->no_rim,
                            'potongan' => $storeLabel->potongan
                        ]);
                    } else {
                        Log::warning('No available label found for processing', [
                            'no_po' => $request->no_po,
                            'iteration' => $i + 1
                        ]);
                        break;
                    }
                } catch (\Exception $labelError) {
                    $failedLabels++;
                    Log::error('Failed to process individual label', [
                        'no_po' => $request->no_po,
                        'iteration' => $i + 1,
                        'error' => $labelError->getMessage()
                    ]);

                    continue;
                }
            }

            $remainingLabels = $this->calcRemainingLabel($request->no_po);
            $status = $remainingLabels > 0 ? 1 : 2;

            $this->updateProgress($request->no_po, $status);

            DB::commit();

            Log::info('Label processing completed successfully', [
                'no_po' => $request->no_po,
                'processed_labels' => $processedLabels,
                'failed_labels' => $failedLabels,
                'remaining_labels' => $remainingLabels,
                'final_status' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Label berhasil diproses',
                'data' => [
                    'processed_labels' => $processedLabels,
                    'failed_labels' => $failedLabels,
                    'remaining_labels' => $remainingLabels,
                    'status' => $remainingLabels > 0 ? 'in_progress' : 'completed'
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();

            Log::warning('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Data yang dikirim tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Critical error in label processing', [
                'no_po' => $request->no_po ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.',
                'error_code' => 'SYSTEM_ERROR'
            ], 500);
        }
    }

    private function updateProgress(Int $no_po, Int $status)
    {
        GeneratedProducts::where('no_po', $no_po)->update([
            'status' => $status
        ]);
    }
}
