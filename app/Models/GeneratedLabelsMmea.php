<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedLabelsMmea extends Model
{
    use HasFactory;
    protected $table = 'generated_labels_mmea';
    protected $fillable = [
        'nomor_po',
        'nomor_rim',
        'periksa1',
        'periksa2',
        'lbr_kemas',
    ];
}
