<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Specification;
use App\Models\Workstations;

class GeneratedProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('NonPerekat/NonPersonal/Pic/ListPo',[
            'products' => $this->data_products(0,''),
            'listTeam' => Workstations::select('id','workstation')->get(),
        ]);
    }

    public function data_products(String $team,String $search = '')
    {
        $team_filter = $team == 0 ? "!=" : "=";
        $data_product = GeneratedProducts::query()
                                ->with('workstation')
                                ->where('no_po','LIKE',"%{$search}%")
                                ->orWhere('no_obc','LIKE',"%{$search}%")
                                ->orderBy('created_at','desc')
                                ->get()
                                ->where('assigned_team',$team_filter,$team)
                                ->transform(function ($q){
                                    return [
                                        'id'    => $q->id,
                                        'no_po' => $q->no_po,
                                        'no_obc'=> $q->no_obc,
                                        'workstation' => $q->workstation->workstation,
                                        'created_at'  => $q->created_at->format('d-m-y h:m:i'),
                                        'updated_at'  => $q->updated_at->format('d-m-y h:m:i'),
                                        'status'    => $q->status,
                                        'assigned_team' => $q->assigned_team,
                                    ];
                                });
        return $data_product == null ? '' : $data_product;
    }


    /**
     * Display the specified resource.
     */
    public function show(string $po)
    {
        return Inertia::render('NonPerekat/NonPersonal/Pic/Monitor',[
            'dataRim'   => GeneratedLabels::where('no_po_generated_products',$po)->get(),
            'spec'  => GeneratedProducts::where('no_po',$po)->firstOrFail(),
        ]);
        // return Specification::where('no_po',$id)
        //             ->select('no_po','no_obc','seri','type','rencet')
        //             ->firstOrFail();
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
