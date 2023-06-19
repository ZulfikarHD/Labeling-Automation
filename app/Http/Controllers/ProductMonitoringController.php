<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\Workstations;

class ProductMonitoringController extends Controller
{
    public function index()
    {
        return Inertia::render('NonPerekat/KepalaMeja/TeamLists',[
            'workstations' => Workstations::all()
        ]);
    }

    public function show(string $id)
    {
        return Inertia::render('NonPerekat/KepalaMeja/Monitor');
    }
}
