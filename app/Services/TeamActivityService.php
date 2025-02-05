<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TeamActivityService
{
    /**
     * Mendapatkan daftar tim yang aktif pada tanggal tertentu
     *
     * @param Carbon $date Tanggal yang ingin dicek
     * @return array Array berisi ID workstation yang aktif
     */
    public function getActiveTeams(Carbon $date): array
    {
        return DB::table('generated_labels')
            ->select('workstation')
            ->whereDate('start', $date)
            ->whereNotNull('workstation')
            ->where('np_users', 'not like', '%mesin%')
            ->distinct()
            ->pluck('workstation')
            ->toArray();
    }

    /**
     * Memeriksa apakah sebuah tim memiliki aktivitas pada tanggal tertentu
     *
     * @param int $teamId ID tim yang ingin dicek
     * @param Carbon $date Tanggal yang ingin dicek
     * @return bool True jika tim memiliki aktivitas, false jika tidak
     */
    public function hasActivity(int $teamId, Carbon $date): bool
    {
        return DB::table('generated_labels')
            ->whereDate('start', $date)
            ->where('workstation', $teamId)
            ->exists();
    }
}
