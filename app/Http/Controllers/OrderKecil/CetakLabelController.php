<?php

namespace App\Http\Controllers\OrderKecil;

use App\Http\Controllers\Controller;
use App\Models\GeneratedLabelsPersonal;
use App\Traits\UpdateStatusProgress;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Specification;
use App\Models\Workstations;
use App\Services\PrintLabelService;
use App\Services\ProductionOrderService;
use App\Services\SpecificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CetakLabelController extends Controller
{
    use UpdateStatusProgress;

    public function index()
    {
        $listTeam = Workstations::select('id','workstation')->get();

        return Inertia::render('OrderKecil/CetakLabel',[
            'listTeam'  => $listTeam,
        ]);
    }

    public function show(Int $no_po, SpecificationService $specificationService)
    {
        return $specificationService->getSpecByNomorPo($no_po);
    }

    public function store(Request $request, PrintLabelService $printLabelService)
    {
        try {
            $registerProductionOrder = url("/api/register-production-order",$request);
            $printLabelService->finishPreviousUserSession($request->periksa1);
            $this->updateProgress($request->no_po, $this->countNullNp($request->no_po) > 0 ? 1 : 2);
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
