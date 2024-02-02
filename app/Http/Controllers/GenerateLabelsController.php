<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Workstations;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use App\Models\Specification;
use App\Models\DataInschiet;

class GenerateLabelsController extends Controller
{
    public function index()
    {
        return Inertia::render('NonPerekat/NonPersonal/Pic/GenerateLabel',[
            'workstation' => Workstations::orderBy('workstation')->select('id','workstation')->get(),
       ]);
    }

    /**
     * Save / Buat Label berdasarkan PO dan Jumlah Rimnnya
     */
    public function store(Request $request)
    {

        $sumRim = $request->jml_rim > 0 ? ceil($request->jml_rim / 2) : 0;
        $cnt_gen_po = count(GeneratedLabels::where('no_po_generated_products',$request->po)->pluck('no_po_generated_products'));
        if($cnt_gen_po === 0){
            // Save PO ke table generated_products
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

            // Save label ke table generated_labels
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
        }
        else{
            $current_start_rim = GeneratedProducts::where('no_po',$request->po)->value('start_rim');
            // dd($current_start_rim);
            if($current_start_rim !== $request->start_rim){
                // Save label ke table generated_labels
                for($i = 0 ; $i < ($sumRim*2) ; $i++)
                {
                    $current_id = GeneratedLabels::where('no_po_generated_products',$request->po)->value('id');
                        GeneratedLabels::where('id',$current_id+$i)
                                ->update(['no_rim' => $i === 0 ? $request->start_rim : $request->start_rim+floor($i/2)]);

                }
                // Save PO ke table generated_products
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
            }
        }

        if($request->inschiet > 0){
            GeneratedLabels::updateOrCreate(
                [
                    'no_po_generated_products' => $request->po,
                    'no_rim'    => 999,
                    'potongan'  => "Kiri"
                ]);

            GeneratedLabels::updateOrCreate(
                [
                    'no_po_generated_products' => $request->po,
                    'no_rim'    => 999,
                    'potongan'  => "Kanan"
                ]);

            DataInschiet::updateOrCreate([
                'no_po'  => $request->po,
                'inschiet' => $request->inschiet,
            ]);
        }
        else{
            //
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
