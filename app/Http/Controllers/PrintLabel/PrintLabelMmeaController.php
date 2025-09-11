<?php

namespace App\Http\Controllers\PrintLabel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrintLabelMmeaController extends Controller
{
    public function index()
    {
        return Inertia::render('PrintLabel/PrintLabelMmea/Index');
    }

    
}
