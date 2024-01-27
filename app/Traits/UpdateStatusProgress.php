<?php

namespace app\Traits;

use App\Models\GeneratedProducts;

trait UpdateStatusProgress {

    // Update Status Orderan
    public function updateProgress(String $po, String $status)
    {
        GeneratedProducts::where('no_po',$po)->update(['status' => $status]);
    }
}
