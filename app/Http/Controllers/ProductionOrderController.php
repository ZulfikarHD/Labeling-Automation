<?php

namespace App\Http\Controllers;

use App\Models\DataInschiet;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Workstations;
use App\Services\PrintLabelService;
use App\Services\ProductionOrderService;
use App\Traits\UpdateStatusProgress;
use Illuminate\Support\Facades\DB;

class ProductionOrderController extends Controller
{
    use UpdateStatusProgress;

    protected $productionOrderService;
    protected $printLabelService;

    public function __construct(ProductionOrderService $productionOrderService, PrintLabelService $printLabelService)
    {
        $this->productionOrderService = $productionOrderService;
        $this->printLabelService = $printLabelService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(String $team)
    {
        return Inertia::render('ProductionOrderList', [
            'products' => $this->data_products($team, request()->merge(['search' => ''])),
            'listTeam' => Workstations::select('id', 'workstation')->get(),
            'crntTeam' => $team,
        ]);
    }

    public function data_products(String $team, Request $request)
    {
        $search = $request->search;
        $team_filter = $team == 0 ? "!=" : "=";
        $data_product = GeneratedProducts::query()
            ->with('workstation')
            ->where('assigned_team', $team_filter, $team)
            ->where(function ($query) use ($search) {
                $query->where('no_po', 'LIKE', "%{$search}%")
                    ->orWhere('no_obc', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($q) {
                return [
                    'id'    => $q->id,
                    'no_po' => $q->no_po,
                    'no_obc' => $q->no_obc,
                    'workstation' => $q->workstation->workstation,
                    'created_at'  => $q->created_at,
                    'updated_at'  => $q->updated_at,
                    'status'    => $q->status,
                    'assigned_team' => $q->assigned_team,
                ];
            });

        return $data_product == null ? '' : $data_product;
    }

    public function edit(Int $po)
    {
        $dataPo = GeneratedProducts::where('no_po', $po)->firstOrFail();
        $dataLabel  = GeneratedLabels::where('no_po_generated_products', $po)
            ->select('id', 'no_po_generated_products', 'no_rim', 'np_users', 'np_user_p2', 'potongan', 'start', 'finish')
            ->get();
        $inschiet   = DataInschiet::where('no_po', $po)->first()->inschiet ?? 0;

        return Inertia::render('EditProductionOrder', [
            'dataPo'    => $dataPo,
            'dataLabel' => $dataLabel,
            'teamList'  => Workstations::listWorkstation(),
            'inschiet'  => $inschiet,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(String $team, string $po)
    {
        $assigned_team = GeneratedProducts::where('no_po', $po)->firstOrFail()->assigned_team;
        return Inertia::render('MonitoringProduksi/StatusVerifikasiTeam', [
            'dataRim'   => GeneratedLabels::where('no_po_generated_products', $po)->get(),
            'spec'  => GeneratedProducts::where('no_po', $po)->firstOrFail(),
            'team'  => Workstations::where('id', $assigned_team)->firstOrFail(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $this->productionOrderService->registerProductionOrder($request);
            $this->printLabelService->populateLabelForRegisteredPo($request);
        } catch (\Exception $exeption) {
            return response()->json(['error' =>  $exeption->getMessage()], 422);
        }

        return redirect()->back();
    }

    public function update(Request $request)
    {
        $labels = GeneratedLabels::where('no_po_generated_products', $request->no_po)
                        ->orderBy('no_rim')
                        ->get();

        $newRimNumber = $request->start_rim;
        $previousRim = null;

        DB::beginTransaction();
        try {
            foreach ($labels as $label) {
                // If current rim number is different from previous, increment newRimNumber
                if ($previousRim !== null && $label->no_rim != $previousRim) {
                    $newRimNumber++;
                }

                // Update the record
                GeneratedLabels::where('id', $label->id)
                    ->update([
                        'no_rim' => $newRimNumber
                    ]);

                $previousRim = $label->no_rim;
            }
        /**
         * add some return message here
         */

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        GeneratedProducts::where('no_po', $request->no_po)->update([
            'type'  => $request->type,
            'sum_rim'   => $request->sum_rim,
            'start_rim' => $request->start_rim,
            'end_rim'   => $request->end_rim,
            'assigned_team' => $request->assigned_team,
            'status'    => $request->status,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $po)
    {
        GeneratedLabels::where('no_po_generated_products', $po)->delete();
        GeneratedProducts::where('no_po', $po)->delete();
        DataInschiet::where('no_po', $po)->delete();
    }

    public function updateStatusFinish(String $po)
    {
        $this->updateProgress($po, 2);
    }
}
