<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\Workstations;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;

class ProductMonitoringController extends Controller
{
    public function index()
    {
        return Inertia::render('NonPerekat/NonPersonal/Pic/TeamList',[
            'workstations' => Workstations::all()
        ]);
    }

    public function show(string $id)
    {
        $getPo  = GeneratedProducts::where('assigned_team',$id)
                                   ->where('status',1)
                                   ->first();

        if($getPo !== null)
        {
            $listLabel  = GeneratedLabels::where('no_po_generated_products',$getPo->no_po)->get();
            return Inertia::render('NonPerekat/NonPersonal/Pic/Monitor',[
                'spec'    => $getPo,
                'dataRim' => $listLabel,
                'team'  => Workstations::where('id',$getPo->assigned_team)->firstOrFail(),
            ]);
        }
        else
        {
            return Inertia::render('NonPerekat/NonPersonal/Pic/TeamList',[
                'workstations' => Workstations::all()
            ]);
        }
    }
}
