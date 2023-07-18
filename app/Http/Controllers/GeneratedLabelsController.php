<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\GeneratedLabelsMmea;
use App\Models\OrderMmea;

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

    public function indexMmea()
    {
        return Inertia::render('Perekat/Verifikator/GenerateLabel');
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
                    'no_rim'    => $rim,
                ],
                [
                    'np_users'   => $request->rfid,
                    'start'     => now(),
                ]
                );
        }

    }
    /**
     * Store a newly created resource in storage.
     * For Perekat
     */
    public function storeMmea(Request $request)
    {
        GeneratedLabelsMmea::create([
                'nomor_po'  => $request->po,
                'nomor_obc' => $request->obc,
                'produk'    => $request->produk,
                'periksa1'  => $request->periksa1,
                'periksa2'  => $request->periksa2,
                'kemasan'   => $request->kemasan,
                'lbr_kemas' => $request->lbr_kemas,
                'gol'   => $request->gol,
            ]);
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
    public function showMmea(string $id)
    {
        return Inertia::render('Perekat/Verifikator/GenerateLabel');
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

    public function callSpec(Request $request)
    {
        $getData = OrderMmea::where('order',$request->po)
                            ->first();

        return $getData;
    }
}
