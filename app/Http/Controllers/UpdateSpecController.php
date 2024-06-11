<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Specification;

class UpdateSpecController extends Controller
{
    public function updateSpec(Request $request)
    {
        try {
            // Process each item in the request array
            foreach ($request->all() as $key => $value) {

                if(!isset($key) || !isset($value['no_obc'])) {
                    continue;
                }

                Specification::updateOrCreate(
                    [
                        'no_po'  => $key,
                        'no_obc' => $value['no_obc']
                    ],
                    [
                        'seri'   => $value['seri'],
                        'type'   => $value['jenis'],
                        'rencet' => $value['rencet'],
                        'mesin'  => $value['mesin']
                    ]
                );
            }

            return response()->json(['status' => 'success', 'data' => $request->all()]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to update spec.'], 500);
        }

    }
}
