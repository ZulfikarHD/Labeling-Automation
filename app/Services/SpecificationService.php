<?php
namespace App\Services;

use App\Models\Specification;

class SpecificationService
{
    public function getSpecByNomorPo(Int $no_po)
    {
        return Specification::where('no_po', $no_po)
                    ->select('no_po', 'no_obc', 'seri', 'type', 'rencet')
                    ->firstOrFail();
    }
}
