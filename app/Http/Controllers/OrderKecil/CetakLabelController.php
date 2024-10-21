<?php

namespace App\Http\Controllers\OrderKecil;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProductionOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use App\Models\GeneratedLabels;
use App\Models\Workstations;

use App\Traits\UpdateStatusProgress;

use App\Services\PrintLabelService;
use App\Services\SpecificationService;
use Inertia\Inertia;

class CetakLabelController extends Controller
{
    use UpdateStatusProgress;

    public function index(Workstations $workstations)
    {
        $listTeam = $workstations->listWorkstation();

        return Inertia::render('OrderKecil/CetakLabel',[
            'listTeam'    => $listTeam,
            'currentTeam' => Auth::user()->workstation_id,
        ]);
    }

    public function show(Int $no_po, SpecificationService $specificationService)
    {
        return $specificationService->getSpecByNomorPo($no_po);
    }

    public function store(Request $request, PrintLabelService $printLabelService)
    {
        try {
            $registerProductionOrder = app(ProductionOrderController::class)->store($request);
            $printLabelService->finishPreviousUserSession($request->periksa1);
            $this->updateProgress($request->no_po, 2);

            return redirect()->back();

        } catch (\Exception $exception) {
            return response()->json(['error' =>  $exception->getMessage()] , 422);
        }
    }

    private function countNullNp(string $noPo): int
    {
        return GeneratedLabels::where('no_po_generated_products', $noPo)
            ->whereNull('np_users')
            ->count();
    }
}
