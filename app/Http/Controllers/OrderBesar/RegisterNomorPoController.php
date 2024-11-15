<?php

namespace App\Http\Controllers\OrderBesar;

use App\Services\SpecificationService;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGeneratedProductsRequest;
use Inertia\Inertia;
use App\Models\Workstations;
use App\Services\PrintLabelService;
use App\Services\ProductionOrderService;
use DB;
use Illuminate\Support\Facades\Auth;

class RegisterNomorPoController extends Controller
{
    protected $productionOrderService;
    protected $printLabelService;

    public function __construct(ProductionOrderService $productionOrderService, PrintLabelService $printLabelService)
    {
        $this->productionOrderService = $productionOrderService;
        $this->printLabelService = $printLabelService;
    }

    /**
     * Display the main Entry PO page.
     *
     * @return \Inertia\Response
     */
    public function index(Workstations $workstations)
    {
        return Inertia::render('OrderBesar/RegisterNomorPo', [
            'workstation' => $workstations->listWorkstation(),
            'currentTeam' => Auth::user()->workstation_id,
        ]);
    }

    /**
     * Store or create labels based on PO and the number of rims.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreGeneratedProductsRequest $request)
    {
        $validatedData = $request->validated();

        try {
            \DB::transaction(function() use ($validatedData) {
                $this->productionOrderService->registerProductionOrder($validatedData);
                $this->printLabelService->populateLabelForRegisteredPo($validatedData);
            });
            return redirect()->back();
        } catch (\Exception $exception) {
            \Log::error('Transaction failed: ' . $exception->getMessage());

            return response()->json([
                'error' => 'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.'
            ], 422);
        }
    }


    /**
     * Display the specified resource based on ID.
     *
     * @param string $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function show($no_po, SpecificationService $specificationService)
    {
        return $specificationService->getSpecByNomorPo($no_po);
    }
}
