<?php

namespace App\Http\Controllers\OrderKecil;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGeneratedProductsRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\GeneratedLabels;
use App\Models\Workstations;
use App\Traits\UpdateStatusProgress;
use App\Services\PrintLabelService;
use App\Services\ProductionOrderService;
use App\Services\SpecificationService;
use Inertia\Inertia;

class CetakLabelController extends Controller
{
    use UpdateStatusProgress;

    protected $productionOrderService;
    protected $printLabelService;

    public function __construct(
        ProductionOrderService $productionOrderService,
        PrintLabelService $printLabelService
    ) {
        $this->productionOrderService = $productionOrderService;
        $this->printLabelService = $printLabelService;
    }

    public function index(Workstations $workstations)
    {
        $listTeam = $workstations->listWorkstation();

        return Inertia::render('OrderKecil/CetakLabel', [
            'listTeam'    => $listTeam,
            'currentTeam' => Auth::user()->workstation_id,
        ]);
    }

    public function show(int $no_po, SpecificationService $specificationService)
    {
        return $specificationService->getSpecByNomorPo($no_po);
    }

    public function cetakLabel(StoreGeneratedProductsRequest $request)
    {
        $validatedRequest = $request->validated();

        try {
            \DB::transaction(function() use ($validatedRequest) {
                $this->productionOrderService->registerProductionOrder($validatedRequest);
                $this->printLabelService->populateLabelForRegisteredPo($validatedRequest);
                $this->printLabelService->finishPreviousUserSession($validatedRequest['periksa1']);
                $this->updateProgress($validatedRequest['po'], 2);
            });

            return response()->json(['message' => 'Label berhasil dibuat'], 200);
        } catch (\Exception $exception) {
            \Log::error('Transaction failed: ' . $exception->getMessage());

            return response()->json([
                'error' => 'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.'
            ], 422);
        }
    }
}
