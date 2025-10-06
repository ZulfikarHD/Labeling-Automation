<?php

namespace App\Http\Controllers;

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
}
