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
        return Inertia::render('NonPerekat/NonPersonal/Verifikator/ChosePo',[
            'products' => GeneratedProducts::where('status','<',2)->get()
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

    public function showMmea(string $id)
    {
        return Inertia::render('Perekat/Verifikator/GenerateLabel');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function callSpec(Request $request)
    {
        $getData = OrderMmea::where('order',$request->po)
                            ->first();

        return $getData;
    }
}
