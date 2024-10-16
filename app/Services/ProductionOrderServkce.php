<?php
namespace App\Services;

use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;

class ProductionOrderService
{
    public function cekPoTerdaftar(Int $no_po)
    {
        $product = GeneratedProducts::where('no_po',$no_po)->first();
        $statusPo = $product !== null ? true : false;

        return $statusPo;
    }

    public function cekLabelPchtTerdaftar(Int $no_po)
    {
        return GeneratedLabels::where('no_po_generated_products',$no_po)->get();
    }

    public function getListNomorRimPcht(Int $no_po)
    {
        return $this->cekLabelPchtTerdaftar($no_po)
                    ->orderBy('no_rim')
                    ->select('no_rim','potongan')
                    ->get();
    }
}
