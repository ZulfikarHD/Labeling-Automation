<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedLabelsPersonal extends Model
{
    use HasFactory;
    protected $table = "generated_labels_personal";
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
