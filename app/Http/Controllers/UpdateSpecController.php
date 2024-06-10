<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Specification;

class UpdateSpecController extends Controller
{
    public function updateSpec(Request $request)
    {
        $validatedData = $request->validate([
            '*.no_obc'  => 'required|string',
            '*.seri'    => 'required|integer|max:5',
            '*.jenis'   => 'required|string|max:1',
            '*.rencet'  => 'required|integer',
            '*.mesin'   => 'string|nullable',
        ]);

        try {
            // Process each item in the validated data array
            foreach ($validatedData as $key => $value) {
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

            return response()->json(['status' => 'success', 'data' => $validatedData]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to update spec.'], 500);
        }
    }
}
