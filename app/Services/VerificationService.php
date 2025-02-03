<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VerificationService
{
    /**
     * Mendapatkan data verifikasi harian untuk sebuah tim
     *
     * @param Carbon $date Tanggal yang ingin dicek
     * @param string $team ID tim yang ingin dicek (0 untuk semua tim)
     * @return Collection Koleksi data verifikasi yang berisi:
     *                   - pegawai: Nomor pegawai
     *                   - verifikasi: Total verifikasi (base + inschiet)
     *                   - jumlah_po: Jumlah PO yang dikerjakan
     */
    public function getDailyVerification(Carbon $date, string $team): Collection
    {
        // Mendapatkan data verifikasi dengan single query
        $verificationData = DB::table('generated_labels as gl')
            ->select([
                'gl.np_users as pegawai',
                DB::raw('COUNT(DISTINCT gl.no_po_generated_products) as jumlah_po'),
                DB::raw('COUNT(CASE WHEN gl.no_rim != 999 THEN 1 END) * 500 as verifikasi_base')
            ])
            ->whereDate('gl.start', $date)
            ->where('gl.np_users', 'not like', '%mesin%')
            ->when($team !== '0', function($query) use ($team) {
                return $query->where('gl.workstation', $team);
            })
            ->groupBy('gl.np_users')
            ->get();

        if ($verificationData->isEmpty()) {
            return collect();
        }

        // Mendapatkan data inschiet dengan single query
        $inschietData = DB::table('data_inschiet')
            ->select([
                DB::raw('np_kiri as pegawai'),
                DB::raw('SUM(inschiet) as kiri_total')
            ])
            ->whereIn('np_kiri', $verificationData->pluck('pegawai'))
            ->whereBetween('updated_at', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay()
            ])
            ->groupBy('np_kiri')
            ->unionAll(
                DB::table('data_inschiet')
                    ->select([
                        DB::raw('np_kanan as pegawai'),
                        DB::raw('SUM(inschiet) as kanan_total')
                    ])
                    ->whereIn('np_kanan', $verificationData->pluck('pegawai'))
                    ->whereBetween('updated_at', [
                        $date->copy()->startOfDay(),
                        $date->copy()->endOfDay()
                    ])
                    ->groupBy('np_kanan')
            )
            ->get()
            ->groupBy('pegawai');

        // Menggabungkan dan menghitung hasil akhir
        return $verificationData->map(function($item) use ($inschietData) {
            $inschiet = $inschietData->get($item->pegawai, collect());
            $totalInschiet = $inschiet->sum('kiri_total') + $inschiet->sum('kanan_total');

            return [
                'pegawai' => $item->pegawai,
                'verifikasi' => $item->verifikasi_base + round($totalInschiet / 2),
                'jumlah_po' => $item->jumlah_po
            ];
        })->sortByDesc('verifikasi')->values();
    }
}
