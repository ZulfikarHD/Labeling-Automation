<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Workstations;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Traits\UpdateStatusProgress;
use App\Models\DataInschiet;

class PrintLabelController extends Controller
{
    use UpdateStatusProgress;

    /**
     * Index
     */
    public function index(String $workstation, String $id)
    {
        $product = GeneratedProducts::where('id',$id)->first();

        return Inertia::render('NonPerekat/NonPersonal/Verifikator/GenerateLabel',[
            'product'   => GeneratedProducts::where('id',$id)->first(),
            'listTeam'  => Workstations::select('id','workstation')->get(),
            'crntTeam'  => Workstations::where('id',$workstation)->value('id'),
            'noRim'     => $this->fetcNoRim($product->no_po)['noRim'],
            'potongan'  => $this->fetcNoRim($product->no_po)['potongan'],
            'date'      => now(),
        ]);
    }

    private function countNullNp(String $po)
    {
        return count(GeneratedLabels::where('no_po_generated_products',$po)->where('np_users',null)->get());
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
                'np_users'    => strtoupper($request->rfid),
                'start'       => now(),
                'finish'      => null,
                'workstation' => $request->team
            ]);

        if($this->countNullNp($request->po) > 0 ){
            $this->updateProgress($request->po,1);
        }
        else{
            $this->updateProgress($request->po,2);
        }
        if($request->no_rim === 999 && $request->lbr_ptg === "Kiri"){
            DataInschiet::where('no_po',$request->po)
                    ->update([
                            'np_kiri' => strtoupper($request->rfid)
                    ]);
        }
        elseif($request->no_rim === 999 && $request->lbr_ptg === "Kanan"){
            DataInschiet::where('no_po',$request->po)
                    ->update([
                            'np_kanan' => strtoupper($request->rfid)
                    ]);
        }
        else{

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
                    'np_users'  => strtoupper($request->npPetugas),
                    'workstation' => $request->team,
                    'start'     => now()
                ]);

            if($request->noRim === 999 && $request->dataRim === "Kiri"){
                DataInschiet::where('no_po',$request->po)
                        ->update([
                                'np_kiri' => strtoupper($request->npPetugas)
                        ]);

            }
            elseif($request->noRim === 999 && $request->dataRim === "Kanan"){
                DataInschiet::where('no_po',$request->po)
                        ->update([
                                'np_kanan' => strtoupper($request->npPetugas)
                        ]);
            }
            else{
            }

        return redirect()->back();
    }

    public function delete(String $id)
    {

    }


    /**
     * Get Nomor Rim dan Potongan
     */
    private function fetcNoRim(String $po)
    {
        $nullKiri   = GeneratedLabels::where('no_po_generated_products',$po)
                                ->where('potongan','Kiri')
                                ->where('np_users',null)
                                ->get();

        $nullKanan  = GeneratedLabels::where('no_po_generated_products',$po)
                                ->where('potongan','Kanan')
                                ->where('np_users',null)
                                ->get();

        $lastKiri   = GeneratedLabels::where('no_po_generated_products',$po)
                            ->where('potongan','Kiri')
                            ->where('np_users',null)
                            ->first();

        $lastKanan   = GeneratedLabels::where('no_po_generated_products',$po)
                            ->where('potongan','Kanan')
                            ->where('np_users',null)
                            ->first();

        $lastRimKiri  = $lastKiri  !== null ? $lastKiri->no_rim : GeneratedLabels::where('no_po_generated_products',$po)->where('potongan','kiri')->latest('no_rim')->first()->no_rim;
        $lastRimKanan = $lastKanan !== null ? $lastKanan->no_rim : GeneratedLabels::where('no_po_generated_products',$po)->where('potongan','kanan')->latest('no_rim')->first()->no_rim;

        // Cek jika label bagian kiri dan kana sudah isi semua
        if(count($nullKiri) < 1 && count($nullKanan) < 1){
            $noRim = 0;
            $potongan = 'Finished';
        }
        else{
            if(count($nullKiri) > 0){
                if($lastRimKiri == $lastRimKanan){
                    $noRim  = $lastKiri->no_rim;
                    $potongan = "Kiri";
                }
                elseif($lastRimKiri > $lastRimKanan){
                    $noRim  = $lastKanan->no_rim;
                    $potongan = "Kanan";
                }
                elseif($lastRimKiri < $lastRimKanan){
                    $noRim  = $lastKiri->no_rim;
                    $potongan = "Kiri";
                }
            }
            else{
                if($lastRimKanan == $lastRimKiri){
                    $noRim  = $lastKanan->no_rim;
                    $potongan  = "Kanan";
                }
                elseif($lastRimKanan > $lastRimKiri){
                    $noRim  = $lastKiri->no_rim;
                    $potongan = "Kiri";
                }
                elseif($lastRimKanan < $lastRimKiri){
                    $noRim  = $lastKanan->no_rim;
                    $potongan  = "Kanan";
                }
            }
        }

        return [
            'noRim' => $noRim ,
            'potongan' => $potongan
        ];
    }
}
