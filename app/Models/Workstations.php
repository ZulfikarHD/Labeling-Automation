<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workstations extends Model
{
    use HasFactory;
    protected $table = 'workstation';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public static function listWorkstation()
    {
        return self::select('id','workstation')->orderBy('workstation')->get();
    }

    public static function getTeamName($id)
    {
        return self::select('workstation')->where('id', $id)->first();
    }
}
