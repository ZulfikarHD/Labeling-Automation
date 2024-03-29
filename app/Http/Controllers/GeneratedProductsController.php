<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Specification;
use App\Models\Workstations;
use App\Traits\UpdateStatusProgress;

class GeneratedProductsController extends Controller
{
    use UpdateStatusProgress;

    /**
     * Display a listing of the resource.
     */
    public function index(String $team)
    {
        return Inertia::render('NonPerekat/NonPersonal/Pic/ListPo',[
            'products' => $this->data_products($team,request()->merge(['search'=>''])),
            'listTeam' => Workstations::select('id','workstation')->get(),
            'crntTeam' => $team,
        ]);
    }

    public function data_products(String $team, Request $request)
    {
        $search = $request->search;
        $team_filter = $team == 0 ? "!=" : "=";
        $data_product = GeneratedProducts::query()
                                ->with('workstation')
                                ->where('assigned_team',$team_filter,$team)
                                ->where(function($query) use($search){
                                    $query->where('no_po','LIKE',"%{$search}%")
                                          ->orWhere('no_obc','LIKE',"%{$search}%");
                                })
                                ->orderBy('created_at','desc')
                                ->paginate(10)
                                ->through(function ($q){
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
    public function show(String $team,string $po)
    {
        $assigned_team = GeneratedProducts::where('no_po',$po)->firstOrFail()->assigned_team;
        return Inertia::render('NonPerekat/NonPersonal/Pic/Monitor',[
            'dataRim'   => GeneratedLabels::where('no_po_generated_products',$po)->get(),
            'spec'  => GeneratedProducts::where('no_po',$po)->firstOrFail(),
            'team'  => Workstations::where('id',$assigned_team)->firstOrFail(),
        ]);
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

    public function updateStatusFinish(String $po)
    {
        $this->updateProgress($po,2);
    }
}
