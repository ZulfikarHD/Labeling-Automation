<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk mengelola verifikasi produksi
 *
 * Service ini bertanggung jawab untuk:
 * - Mengambil data verifikasi harian per tim/pegawai
 * - Menghitung total verifikasi (base + inschiet)
 * - Menghitung jumlah PO yang dikerjakan
 */
class VerificationService
{
    /**
     * Mengambil data verifikasi harian untuk sebuah tim
     */
    public function getDailyVerification(Carbon $date, string $team): Collection
    {
        // Query data verifikasi dasar
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

        // Check if no_rim=999 exists in verification data
        $hasNoRim999 = DB::table('generated_labels as gl')
            ->whereDate('gl.start', $date)
            ->where('gl.no_rim', 999)
            ->when($team !== '0', function($query) use ($team) {
                return $query->where('gl.workstation', $team);
            })
            ->exists();

        $inschietData = collect();

        // Only execute inschiet query if no_rim=999 exists
        if ($hasNoRim999) {
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
        }

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
