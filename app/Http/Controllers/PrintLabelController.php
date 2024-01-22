<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Workstations;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;

class PrintLabelController extends Controller
{
    /**
     * Index
     */
    public function index(String $id)
    {
        $product = GeneratedProducts::where('id',$id)->first();

        return Inertia::render('NonPerekat/NonPersonal/Verifikator/GenerateLabel',[
            'product'   => GeneratedProducts::where('id',$id)->first(),
            'listTeam'  => Workstations::select('id','workstation')->get(),
            'noRim'     => $this->fetcNoRim($product->no_po)['noRim'],
            'potongan'  => $this->fetcNoRim($product->no_po)['potongan'],
        ]);
    }

    /**
     * Print Label dan Simpan data user ke Database
     */
    public function store(Request $request)
    {
        // Jangan rubah Urutannya
        $cnt_prog = count(GeneratedLabels::where('np_users',$request->rfid)->pluck('finish'));
        if($cnt_prog > 0){
            GeneratedLabels::where('np_users',$request->rfid)
                    ->where('finish',null)
                    ->update([
                        'np_users'  => strtoupper($request->rfid),
                        'finish'     => now()
                    ]);
        }else{
            // do nothing
        }

        GeneratedLabels::where('no_po_generated_products',$request->po)
            ->where('potongan',$request->lbr_ptg)
            ->where('no_rim',$request->no_rim)
            ->update([
                'np_users'  => strtoupper($request->rfid),
                'start'     => now(),
                'finish'    => null,
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

        return redirect()->back();
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

        return redirect()->back();
    }

    /**
     * Get Nomor Rim dan Potongan
     */
    private function fetcNoRim(String $po)
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
