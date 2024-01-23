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
            'products' => GeneratedProducts::all(),
        ]);
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
