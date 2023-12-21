<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Specification;

class GeneratedProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('NonPerekat/KepalaMeja/GeneratedOrders',[
            'products' => GeneratedProducts::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('NonPerekat/KepalaMeja/NewGenerate',[
            'showModal' => false,
       ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        GeneratedProducts::updateOrCreate(
            [
                'no_po' => $request->po,
            ],
            [
                'no_obc'    => $request->obc,
                'type'      => $request->produk,
                'sum_rim'   => $request->jml_rim,
                'start_rim' => $request->start_rim,
                'end_rim'   => $request->end_rim,
                'status'    => 0,
            ]
        );

        for($i = 0 ; $i < $request->jml_rim ; $i++)
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

        return Inertia::render('NonPerekat/KepalaMeja/NewGenerate',[
            'showModal' => true,
       ]);
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $po)
    {
        GeneratedLabels::where('no_po_generated_products',$po)->delete();
        GeneratedProducts::where('no_po',$po)->delete();
    }

    public function updateStatus($po)
    {
        if(count(GeneratedLabels::where('no_po_generated_products',$po)->where('np_users',null)->get()) > 0 )
        {
            GeneratedProducts::where('no_po',$po)->update([
                'status'    => 1
            ]);
        }
        else
        {
            GeneratedProducts::where('no_po',$po)->update([
                'status'    => 2
            ]);
        }
    }
}
