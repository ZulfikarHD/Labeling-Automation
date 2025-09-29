<?php
namespace App\Http\Jobs;

Trait Divnum
{
    public function divnum($numerator, $denominator)
    {
        return $denominator == 0 ? 0 : ($numerator / $denominator);
    }
}