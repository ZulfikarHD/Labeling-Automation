<?php

namespace App\Http\Controllers\PrintLabel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Specification;

class PrintLabelInspeksiController extends Controller
{
    public function index()
    {
        return Inertia::render('PrintLabel/PrintLabelInspeksi/Index');
    }

    public function fetchDataSpec(Int $no_po)
    {
        $specPo = Specification::where('no_po', $no_po)->first();

        return response()->json($specPo);
    }
}
