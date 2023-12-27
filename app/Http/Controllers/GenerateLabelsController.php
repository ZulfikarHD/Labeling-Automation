<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Workstations;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use App\Models\Specification;

class GenerateLabelsController extends Controller
{
    public function index()
    {
        return Inertia::render('NonPerekat/NonPersonal/Pic/GenerateLabel',[
            'workstation' => Workstations::orderBy('workstation')->select('id','workstation')->get(),
       ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $sumRim = $request->jml_rim > 0 ? ceil($request->jml_rim / 2) : 0;
        GeneratedProducts::updateOrCreate(
            [
                'no_po' => $request->po,
            ],
            [
                'no_obc'    => $request->obc,
                'type'      => $request->produk,
                'sum_rim'   => $sumRim,
                'start_rim' => $request->start_rim,
                'end_rim'   => $request->end_rim,
                'status'    => 0,
                'assigned_team' => $request->team,
            ]
        );

        for($i = 0 ; $i < $sumRim ; $i++)
        {
            GeneratedLabels::updateOrCreate(
                [
                    'no_po_generated_products' => $request->po,
                    'no_rim'    => $request->start_rim + $i,
                    'potongan'  => "Kiri"
                ]
                );

            GeneratedLabels::updateOrCreate(
                [
                    'no_po_generated_products' => $request->po,
                    'no_rim'    => $request->start_rim + $i,
                    'potongan'  => "Kanan"
                ]
                );
        }

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Specification::where('no_po',$id)
                    ->select('no_po','no_obc','seri','type','rencet')
                    ->firstOrFail();
    }
}
