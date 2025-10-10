<?php

namespace App\Http\Controllers;

use App\Models\GeneratedLabelsMmea;
use App\Models\GeneratedProducts;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegisteredPoMmeaController extends Controller
{
    public function index()
    {
        return Inertia::render('RegisteredPo/RegisteredPoMmea/Index', [
            'registeredPo'  => $this->registeredPoData(request()->merge(['search' => ''])),
        ]);
    }

    public function registeredPoData(Request $request)
    {
        $search = $request->search;

        $data_product = GeneratedProducts::whereIn('type', ['MMEA', 'HPTL'])
                            ->where(function ($query) use ($search) {
                                $query->where('no_po', 'LIKE', "%{$search}%")
                                    ->orWhere('no_obc', 'LIKE', "%{$search}%");
                            })
                            ->paginate(10)
                            ->through(function ($q) {
                                return [
                                    'id'     => $q->id,
                                    'no_po'  => $q->no_po,
                                    'no_obc' => $q->no_obc,
                                    'produk' => $q->type,
                                    'status' => $q->status,
                                    'created_at' => $q->created_at,
                                    'updated_at' => $q->updated_at,
                                ];
                            });

        return $data_product == null ? '' : $data_product;
    }

    public function show(Int $no_po)
    {
        $data_product = GeneratedProducts::where('no_po',$no_po)
                            ->select('no_po','no_obc','type')
                            ->first();

        $data_periksa = GeneratedLabelsMmea::where('nomor_po',$no_po)
                            ->orderBy('nomor_rim')
                            ->get();

        return Inertia::render('RegisteredPo/RegisteredPoMmea/Show',[
            'data_product'  => $data_product,
            'data_periksa'  => $data_periksa,
        ]);
    }

    public function destroy($no_po)
    {
        // Delete Generated Labels MMEA
        GeneratedLabelsMmea::where('nomor_po',$no_po)->delete();

        // Delete Registered PO MMEA
        GeneratedProducts::where('no_po',$no_po)->delete();
    }
}
