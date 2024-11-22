<?php

namespace App\Services;

use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class ProductionOrderService
{
    private const SHEETS_PER_RIM = 500;
    private const MIN_RIM = 1;
    private const PRODUCT_TYPE = 'PCHT';
    private const INITIAL_STATUS = 1;

    /**
     * Register a new production order
     *
     * @param array $productionOrder Production order data
     * @throws \Exception If PO number already exists
     */
    public function registerProductionOrder(array $productionOrder): void
    {
        if ($this->registeredProductionOrder($productionOrder['po'])->exists()) {
            throw new \Exception('Nomor PO sudah terdaftar dalam sistem');
        }

        DB::transaction(function () use ($productionOrder) {
            $sumRim = $this->calculateTotalRims($productionOrder['jml_lembar']);

            GeneratedProducts::create([
                'no_po' => $productionOrder['po'],
                'no_obc' => $productionOrder['obc'],
                'type' => self::PRODUCT_TYPE,
                'status' => self::INITIAL_STATUS,
                'sum_rim' => $sumRim,
                'start_rim' => $productionOrder['start_rim'],
                'end_rim' => $productionOrder['end_rim'],
                'assigned_team' => $productionOrder['team'],
            ]);
        });
    }

    /**
     * Calculate total rims based on number of sheets
     */
    private function calculateTotalRims(int $totalSheets): int
    {
        return max(floor($totalSheets / self::SHEETS_PER_RIM), self::MIN_RIM);
    }

    /**
     * Get registered production order query
     */
    public function registeredProductionOrder(int $no_po): Builder
    {
        return GeneratedProducts::where('no_po', $no_po);
    }

    /**
     * Get registered PCHT labels
     */
    public function cekLabelPchtTerdaftar(int $no_po): Builder
    {
        return GeneratedLabels::where('no_po_generated_products', $no_po);
    }

    /**
     * Get list of rim numbers for PCHT
     */
    public function getListNomorRimPcht(int $no_po): Builder
    {
        return $this->cekLabelPchtTerdaftar($no_po)
            ->select('no_rim', 'potongan')
            ->orderBy('no_rim');
    }

    /**
     * Check if production order is finished
     */
    public function isPoFinished(int $no_po): bool
    {
        return $this->cekLabelPchtTerdaftar($no_po)
            ->where(function ($query) {
                $query->whereNull('np_users')
                    ->orWhere('np_users', '');
            })
            ->count() === 0;
    }
}
