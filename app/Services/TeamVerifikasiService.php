<?php
namespace App\Services;

use App\Models\Workstations;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;

class TeamVerifikasiService
{
    public function getProductAndLabelsByTeam(string $teamId)
    {
        $product = GeneratedProducts::where('assigned_team', $teamId)
                                    ->where('status', 1)
                                    ->first();

        if (!$product) {
            return null;
        }

        $labels = GeneratedLabels::where('no_po_generated_products', $product->no_po)->get();

        return [
            'product' => $product,
            'labels' => $labels,
            'team' => Workstations::where('id', $product->assigned_team)->firstOrFail(),
        ];
    }

    public function getAllWorkstations()
    {
        return Workstations::all();
    }
}
