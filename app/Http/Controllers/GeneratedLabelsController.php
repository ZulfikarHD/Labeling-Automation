<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;

class GeneratedLabelsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('NonPerekat/Verifikator/ChosePo',[
            'products' => GeneratedProducts::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return $request->all();
        foreach($request->no_rim as $rim)
        {
            GeneratedLabels::updateOrCreate(
                [
                    'no_po_generated_products' => $request->po,
                    'potongan'  => $request->lbr_ptg,
                    'no_rim'    => $rim
                ],
                [
                    'np_users'   => $request->rfid,
                    'start'     => now(),
                ]
                );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Inertia::render('NonPerekat/Verifikator/GenerateLabel',[
            'product'   => GeneratedProducts::where('id',$id)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $noPo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getRim(Request $request)
    {
        $getRim = GeneratedLabels::where('no_po_generated_products',$request->po)
                                  ->where('potongan',$request->lbr_ptg)
                                  ->where('np_users',null)
                                  ->take($request->jml_rim)
                                  ->select('no_rim')->get();
        return $getRim;
    }
}
