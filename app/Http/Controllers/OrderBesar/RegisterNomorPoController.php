<?php

namespace App\Http\Controllers\OrderBesar;

use App\Services\SpecificationService;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGeneratedProductsRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Workstations;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use App\Models\DataInschiet;
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
        // dd($validatedData);
        // $this->printLabelService->populateLabelForRegisteredPo($validatedData);
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
        // try {

        //     DB::beginTransaction();

        //     $validatedData = $request->validated();
        //     $sumRim = max(floor($validatedData['jml_lembar'] / 1000), 1);
        //     $cnt_gen_po = GeneratedLabels::where('no_po_generated_products', $validatedData['po'])->count();

        //     if ($cnt_gen_po === 0) {
        //         $this->createOrUpdateGeneratedProducts($validatedData, $sumRim);
        //         $this->createOrUpdateGeneratedLabels($validatedData, $sumRim);
        //     } else {
        //         $this->updateGeneratedProductsAndLabels($validatedData, $sumRim);
        //     }

        //     if ($validatedData['inschiet'] > 0) {
        //         $this->createInschietLabels($validatedData);
        //     }

        //     DB::commit();
        //     return response()->json(['message' => 'Data berhasil disimpan']);

        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return response()->json(
        //         ['message' => 'Terjadi kesalahan: ' . $e->getMessage()],
        //         500
        //     );
        // }
    }

    // /**
    //  * Create or update generated products based on PO.
    //  *
    //  * @param \Illuminate\Http\Request $request
    //  * @param int $sumRim
    //  * @return void
    //  */
    // private function createOrUpdateGeneratedProducts($request, $sumRim)
    // {
    //     GeneratedProducts::updateOrCreate(
    //         ['no_po' => $request['po']],
    //         [
    //             'no_obc' => $request['obc'],
    //             'type' => $request['produk'],
    //             'sum_rim' => $sumRim,
    //             'start_rim' => $request['start_rim'],
    //             'end_rim' => $request['end_rim'],
    //             'status' => 0,
    //             'assigned_team' => $request['team'],
    //         ]
    //     );
    // }

    // /**
    //  * Create or update generated labels based on PO.
    //  *
    //  * @param \Illuminate\Http\Request $request
    //  * @param int $sumRim
    //  * @return void
    //  */
    // private function createOrUpdateGeneratedLabels($request, $sumRim)
    // {
    //     for ($i = 0; $i < $sumRim; $i++) {
    //         foreach (['Kiri', 'Kanan'] as $potongan) {
    //             GeneratedLabels::updateOrCreate(
    //                 [
    //                     'no_po_generated_products' => $request['po'],
    //                     'no_rim' => $request['start_rim'] + $i,
    //                     'potongan' => $potongan
    //                 ]
    //             );
    //         }
    //     }
    // }

    // /**
    //  * Update generated products and labels based on PO.
    //  *
    //  * @param \Illuminate\Http\Request $request
    //  * @param int $sumRim
    //  * @return void
    //  */
    // private function updateGeneratedProductsAndLabels($request, $sumRim)
    // {
    //     $current_start_rim = GeneratedProducts::where('no_po', $request['po'])->value('start_rim');

    //     if ($current_start_rim !== $request['start_rim']) {
    //         $labels = GeneratedLabels::where('no_po_generated_products', $request['po'])->get();
    //         foreach ($labels as $index => $label) {
    //             $label->update(['no_rim' => $request['start_rim'] + floor($index / 2)]);
    //         }

    //         $this->createOrUpdateGeneratedProducts($request, $sumRim);
    //     }
    // }

    // /**
    //  * Create inschiet labels based on PO.
    //  *
    //  * @param \Illuminate\Http\Request $request
    //  * @return void
    //  */
    // private function createInschietLabels($request)
    // {
    //     foreach (['Kiri', 'Kanan'] as $potongan) {
    //         GeneratedLabels::updateOrCreate(
    //             [
    //                 'no_po_generated_products' => $request['po'],
    //                 'no_rim' => 999,
    //                 'potongan' => $potongan
    //             ]
    //         );
    //     }

    //     DataInschiet::updateOrCreate(
    //         ['no_po' => $request['po']],
    //         ['inschiet' => $request['inschiet']]
    //     );
    // }

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
