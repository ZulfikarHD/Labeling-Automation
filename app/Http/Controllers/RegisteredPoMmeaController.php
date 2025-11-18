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
        $search = $request->input('search', '');

        $data_product = GeneratedProducts::select('id', 'no_po', 'no_obc', 'type', 'status', 'created_at', 'updated_at')
                            ->whereIn('type', ['MMEA', 'HPTL'])
                            ->when($search, function ($query) use ($search) {
                                $query->where(function ($q) use ($search) {
                                    $q->where('no_po', 'LIKE', "%{$search}%")
                                      ->orWhere('no_obc', 'LIKE', "%{$search}%");
                                });
                            })
                            ->orderBy('created_at', 'desc')
                            ->paginate(10)
                            ->through(function ($q) {
                                return [
                                    'id'         => $q->id,
                                    'no_po'      => $q->no_po,
                                    'no_obc'     => $q->no_obc,
                                    'produk'     => $q->type,
                                    'status'     => $q->status,
                                    'created_at' => $q->created_at,
                                    'updated_at' => $q->updated_at,
                                ];
                            });

        return $data_product;
    }

    public function show(int $no_po)
    {
        $data_product = GeneratedProducts::where('no_po', $no_po)
                            ->whereIn('type', ['MMEA', 'HPTL'])
                            ->select('no_po', 'no_obc', 'type')
                            ->first();

        if (!$data_product) {
            return redirect()->route('dataPoMmea.index')
                ->with('error', 'Data PO tidak ditemukan');
        }

        $data_periksa = GeneratedLabelsMmea::where('nomor_po', $no_po)
                            ->select('nomor_po', 'nomor_rim', 'periksa1', 'periksa2', 'lbr_kemas', 'waktu_p1', 'waktu_p2', 'created_at', 'updated_at')
                            ->orderBy('nomor_rim')
                            ->get();

        return Inertia::render('RegisteredPo/RegisteredPoMmea/Show', [
            'data_product'  => $data_product,
            'data_periksa'  => $data_periksa,
        ]);
    }

    public function edit(int $no_po)
    {
        $data_product = GeneratedProducts::where('no_po', $no_po)
                            ->whereIn('type', ['MMEA', 'HPTL'])
                            ->select('no_po', 'no_obc', 'type', 'sum_rim')
                            ->first();

        if (!$data_product) {
            return redirect()->route('dataPoMmea.index')
                ->with('error', 'Data PO tidak ditemukan');
        }

        $data_periksa = GeneratedLabelsMmea::where('nomor_po', $no_po)
                            ->select('nomor_po', 'nomor_rim', 'periksa1', 'periksa2', 'lbr_kemas', 'waktu_p1', 'waktu_p2')
                            ->orderBy('nomor_rim')
                            ->get();

        return Inertia::render('RegisteredPo/RegisteredPoMmea/Edit', [
            'data_product'  => $data_product,
            'data_periksa'  => $data_periksa,
        ]);
    }

    public function update(Request $request, int $no_po)
    {
        try {
            $validated = $request->validate([
                'no_obc' => 'required|string',
                'type' => 'required|string|in:MMEA,HPTL',
                'labels' => 'required|array',
            ]);

            // Update product info
            GeneratedProducts::where('no_po', $no_po)->update([
                'no_obc' => $validated['no_obc'],
                'type' => $validated['type'],
            ]);

            // Update labels if provided
            if (isset($validated['labels'])) {
                foreach ($validated['labels'] as $label) {
                    GeneratedLabelsMmea::where('nomor_po', $no_po)
                        ->where('nomor_rim', $label['nomor_rim'])
                        ->update([
                            'periksa1' => $label['periksa1'] ?? null,
                            'periksa2' => $label['periksa2'] ?? null,
                            'lbr_kemas' => $label['lbr_kemas'] ?? null,
                        ]);
                }
            }

            return redirect()->route('dataPoMmea.index')
                ->with('success', 'Data PO berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($no_po)
    {
        try {
            // Check if PO exists
            $product = GeneratedProducts::where('no_po', $no_po)->first();
            
            if (!$product) {
                return redirect()->back()
                    ->with('error', 'Data PO tidak ditemukan');
            }

            // Delete Generated Labels MMEA
            GeneratedLabelsMmea::where('nomor_po', $no_po)->delete();

            // Delete Registered PO MMEA
            GeneratedProducts::where('no_po', $no_po)->delete();

            return redirect()->route('dataPoMmea.index')
                ->with('success', 'Data PO berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}
