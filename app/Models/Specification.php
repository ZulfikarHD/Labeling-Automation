<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specification extends Model
{
    // Specify the database connection if not default
    protected $connection = 'mysql';
    protected $table = 'specifications';
    use HasFactory;
    protected $fillable = ['no_po', 'no_obc', 'seri', 'type', 'rencet', 'mesin'];
}
