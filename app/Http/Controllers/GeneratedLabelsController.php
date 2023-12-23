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
     */
    public function store(Request $request)
    {
        GeneratedLabels::where('no_po_generated_products',$request->po)
                ->where('potongan',$request->lbr_ptg)
                ->where('no_rim',$request->no_rim)
                ->update([
                    'np_users'  => $request->rfid,
                    'start'     => now()
                ]);

        if(count(GeneratedLabels::where('no_po_generated_products',$request->po)->where('np_users',null)->get()) > 0 )
        {
            GeneratedProducts::where('no_po',$request->po)->update([
                'status'    => 1,
                'assigned_team' => $request->team,
            ]);
        }
        else
        {
            GeneratedProducts::where('no_po',$request->po)->update([
                'status'    => 2,
                'assigned_team' => $request->team,
            ]);
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
        $product = GeneratedProducts::where('id',$id)->first();
        return Inertia::render('NonPerekat/Verifikator/GenerateLabel',[
            'product'   => $product,
            'listTeam'  => \App\Models\Workstations::select('id','workstation')->get(),
            'noRim'     => $this->fetcNoRim($product->no_po)['noRim'],
            'potongan'  => $this->fetcNoRim($product->no_po)['potongan'],
        ]);

    }

    public function showMmea(string $id)
    {
        return Inertia::render('Perekat/Verifikator/GenerateLabel');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $getDataRim = GeneratedLabels::where('no_po_generated_products',$request->po)
                                     ->where('potongan',$request->dataRim)
                                     ->select('no_rim','np_users','potongan','start','finish')
                                     ->get();
        return  $getDataRim;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        GeneratedLabels::where('no_po_generated_products',$request->po)
                ->where('potongan',$request->dataRim)
                ->where('no_rim',$request->noRim)
                ->update([
                    'np_users'  => $request->npPetugas,
                ]);
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

    public function fetcNoRim(String $po)
    {
        $lastKiri   = GeneratedLabels::where('no_po_generated_products',$po)
                                ->where('potongan','Kiri')
                                ->where('np_users',null)
                                ->first();

        $lastKanan  = GeneratedLabels::where('no_po_generated_products',$po)
                                ->where('potongan','Kanan')
                                ->where('np_users',null)
                                ->first();

        if(is_null($lastKiri) && is_null($lastKanan)){
            $noRim = 0;
            $potongan = 'Finished';
        }
        elseif(is_null($lastKiri)){
            $noRim  = $lastKanan->no_rim;
            $potongan = 'Kanan';
        }
        else{
            if($lastKiri->no_rim === $lastKanan->no_rim){
                $noRim  = $lastKiri->no_rim;
                $potongan = 'Kiri';
            }
            elseif($lastKiri->no_rim > $lastKanan->no_rim){
                $noRim  = $lastKanan->no_rim;
                $potongan = 'Kanan';
            }
        }

        return [
            'noRim' => $noRim,
            'potongan' => $potongan
        ];
    }
}
