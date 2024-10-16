<?php

namespace App\Services;

use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use Illuminate\Support\Facades\DB;

class ProductionOrderService
{
    public function registerProductionOrder($productionOrder)
    {
        if ($this->registeredProductionOrder($productionOrder->no_po)->exists()) {
            throw new \Exception('Nomor Po Sudah Terdaftar');
        }

        return DB::transaction(function () use ($productionOrder) {
            $sumRim = max(floor($productionOrder->jml_lembar / 1000), 1);

            GeneratedProducts::create([
                'no_po'   => $productionOrder->no_po,
                'no_obc'  => $productionOrder->obc,
                'type'    => "PCHT",
                'status'  => 1,
                'sum_rim' => $sumRim,
                'start_rim' => 1,
                'end_rim'   => $sumRim,
                'assigned_team' => $productionOrder->team,
            ]);
        });
    }

    public function registeredProductionOrder(Int $no_po)
    {
        return GeneratedProducts::where('no_po', $no_po);
    }

    public function cekLabelPchtTerdaftar(Int $no_po)
    {
        return GeneratedLabels::where('no_po_generated_products', $no_po)->get();
    }

    public function getListNomorRimPcht(Int $no_po)
    {
        return $this->cekLabelPchtTerdaftar($no_po)
            ->orderBy('no_rim')
            ->select('no_rim', 'potongan')
            ->get();
    }
}
