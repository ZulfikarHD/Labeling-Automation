<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\Specification;

class GenerateLabelsPersonalController extends Controller
{
    public function index()
    {
        return Inertia::render('NonPerekat/Personal/GenerateLabel');
    }

    public function show($noPo)
    {
        return Specification::where('no_po',$noPo)
                    ->select('no_po','no_obc','seri','type','rencet')
                    ->firstOrFail();
    }


}
