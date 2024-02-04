<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataInschiet extends Model
{
    use HasFactory;
    protected $table = 'data_inschiet';
    protected $primaryKey = 'no_po';
    protected $fillable = ['no_po','inschiet','np_kiri','np_kanan'];
}
