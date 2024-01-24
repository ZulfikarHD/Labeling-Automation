<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GeneratedProducts extends Model
{
    use HasFactory;
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the workstation associated with the GeneratedProducts
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function workstation(): HasOne
    {
        return $this->hasOne(Workstations::class, 'id', 'assigned_team');
    }
}
