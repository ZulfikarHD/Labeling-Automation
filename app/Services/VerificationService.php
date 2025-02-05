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
     *
     * Method ini melakukan:
     * 1. Query untuk mengambil data verifikasi dasar (base verification)
     * 2. Query untuk mengambil data inschiet dari kiri dan kanan
     * 3. Menggabungkan hasil dan menghitung total verifikasi
     *
     * @param Carbon $date Tanggal yang ingin dicek
     * @param string $team ID tim yang ingin dicek (0 untuk semua tim)
     * @return Collection Koleksi data verifikasi dengan format:
     *                   [
     *                     'pegawai' => string,     // Nomor pegawai
     *                     'verifikasi' => int,     // Total verifikasi (base + inschiet)
     *                     'jumlah_po' => int       // Jumlah PO yang dikerjakan
     *                   ]
     */
    public function getDailyVerification(Carbon $date, string $team): Collection
    {
        // Query #1: Mengambil data verifikasi dasar
        // - Menghitung jumlah PO per pegawai
        // - Menghitung verifikasi base (500 per rim, exclude rim 999)
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

        // Early return jika tidak ada data verifikasi
        if ($verificationData->isEmpty()) {
            return collect();
        }

        // Query #2: Mengambil data inschiet
        // - Menggunakan UNION ALL untuk menggabungkan data kiri dan kanan
        // - Menghitung total inschiet per pegawai
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
        // - Menggabungkan verifikasi base dengan inschiet
        // - Mengurutkan berdasarkan total verifikasi (descending)
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
