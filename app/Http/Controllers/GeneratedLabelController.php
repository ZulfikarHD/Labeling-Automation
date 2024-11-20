<?php

namespace App\Http\Controllers;

use App\Models\GeneratedLabels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GeneratedLabelController extends Controller
{
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            GeneratedLabels::where('id', $request->id)
                ->update([
                    'np_users'   => $request->np_users,
                    'np_user_p2' => $request->np_user_p2 ?? null,
                    'start'  => $request->start ?? null,
                    'finish' => $request->finish ?? null,
                    'workstation' => $request->team
                ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Label berhasil diperbarui',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getLabels(string $po)
    {
        try {
            $labels = GeneratedLabels::where('no_po_generated_products', $po)
                ->select('id', 'no_po_generated_products', 'no_rim', 'np_users', 'np_user_p2', 'potongan', 'start', 'finish')
                ->orderBy('no_rim')
                ->get();

            return response()->json($labels);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data label',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
