<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedLabels extends Model
{
    use HasFactory;
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public static function isFinishedAll(String $npPegawai)
    {
        $countUnfinishedState = self::where('np_user',$npPegawai)
                                    ->whereNull('finish')
                                    ->count();

        $isFinishedAll = $countUnfinishedState > 0 ? false : true;

        return $isFinishedAll;
    }
}
